<?php 

/**
 * 
 * @param type $value
 * @param type $base_pattern
 * @param type $fixed_pattern
 * @param type $matches
 * @return type
 */
function getPatternMatching( $value, $base_pattern, $fixed_pattern, &$matches )
{
  $pattern = preg_replace('/\//', '\/', $base_pattern);
  $pattern = preg_replace('/\?/', '\?', $pattern);
  $found_pre = preg_match_all('/'.$pattern.'/', $value, $matches, PREG_OFFSET_CAPTURE);

  $base_url = $fixed_pattern;
  if( $base_url[0] == '/' ) {
    $base_url = substr( $base_url, 1 );
  }
  $pattern = preg_replace('/\//', '\/', $base_url);
  $pattern = preg_replace('/\?/', '\?', $pattern);
  return $pattern;
}

/**
 * 
 * @param type $value
 * @param type $base_pattern
 * @param type $fixed_pattern
 * @return boolean
 */
function detectPatternMatching($value, $base_pattern, $fixed_pattern)
{
  $require_correction = false;
  $matches = array();
  $pattern = getPatternMatching( $value, $base_pattern, $fixed_pattern, $matches );
  
  // Is this only to trim a part or there is a replacement/insertion to occur ?
  $pos = strpos($base_pattern, $fixed_pattern);
  foreach( $matches[0] as $m ) {
    if( !empty($m) ) {
      $substring = substr($value, $m[1]);
      $found_post = preg_match('/'.$pattern.'/', $substring);  
      // Already done OR This is only removing information from the pattern.
      if( !$found_post || ($pos === 0)) {
        $require_correction = true;
        break;
      }
    }
  }
  return $require_correction;
}

/**
 * 
 * @param type $value
 * @param type $base_pattern
 * @param type $fixed_pattern
 * @return type
 */
function fixPatternMatching(&$value, $base_pattern, $fixed_pattern)
{
  $matches = array();
  $pattern = getPatternMatching( $value, $base_pattern, $fixed_pattern, $matches );

  // Is this only to trim a part or there is a replacement/insertion to occur ?
  $pos = strpos($base_pattern, $fixed_pattern);
  $new_value = $value;
  foreach( $matches[0] as $index => $m ) {
    $substring = substr($value, $m[1]);
    $found_post = preg_match('/'.$pattern.'/', $substring);  
    if( !$found_post || ($pos === 0)) {
      // Fix the data (only entry at a time, requires multiple runs)
      $new_value = substr_replace($value, $fixed_pattern, $m[1], strlen($m[0]));
      break;
    }
  }
  return $new_value;
}

function updateNodeValue($table_name, $new_value, $revision_id, $field_str)
{
  $num_updated = db_update($table_name) // Table name no longer needs {}
                  ->fields(array($field_str => $new_value))
                  ->condition('revision_id', $revision_id, '=')
                  ->execute();  
  return $num_updated;
}

/**
 * 
 * @param type $base_pattern
 * @param type $fixed_pattern
 * @param type $base_url
 * @param type $scanType
 * @return type
 */
function scanForContentCorrection($table_name, $base_pattern, $fixed_pattern, $base_url, $scanType = 'node', $fixData = false, $addToSummary = true)
{
  $query = db_select('node', 'n');
  $query->join($table_name, 'r', 'r.revision_id = n.vid');
  $nodes = $query
            ->fields('r', array('revision_id', 'entity_type', 'bundle', 'body_value', 'body_summary', 'body_format'))
            ->fields('n', array('nid', 'vid', 'type', 'status', 'uid', 'title'))
            ->condition('r.body_value', '%'.$base_pattern.'%', 'like')
            ->execute();
  $row_count = $nodes->rowCount();

  if($nodes) {
    $row_index = $row_count;
    $require_correction = array();
    do{
      $row = $nodes->fetchAssoc();

      // Make sure that the URL is not already corrected
      ///////////////////////////////////////////////////////////
      $detected = detectPatternMatching(  $row['body_value'], 
                                            $base_pattern, 
                                            $fixed_pattern);
      array_push( $require_correction, array( 'body' => $detected));
      if($fixData && $detected ) {
        $returned_value = $row['body_value'];
        do {
          $returned_value = fixPatternMatching(   $returned_value, 
                                                  $base_pattern, 
                                                  $fixed_pattern);
        } while( detectPatternMatching( $returned_value, 
                                        $base_pattern, 
                                        $fixed_pattern));
        
        updateNodeValue( $table_name, $returned_value, $row['revision_id'], 'body_value');
        //drupal_set_message("<a href=\"".$base_url."node/".$row['nid']."\">".
        //        $row['nid']."</a>: [".$row['type']."] ".$row['title']." (".$returned_value.")", 'warning');
        if( $row['body_format'] == "filtered_html" ) {
          updateNodeValue( $table_name, "full_html", $row['revision_id'], 'body_format');
        }
      }

      $detected_summary = detectPatternMatching(  $row['body_summary'], 
                                            $base_pattern, 
                                            $fixed_pattern);
      array_push( $require_correction, array( 'summary' => $detected_summary));
      if($fixData && $detected_summary ) {
        $returned_value = $row['body_summary'];
        do {
          $returned_value = fixPatternMatching(   $returned_value, 
                                                  $base_pattern, 
                                                  $fixed_pattern);
        } while( detectPatternMatching( $returned_value, 
                                        $base_pattern, 
                                        $fixed_pattern));
        
        updateNodeValue( $table_name, $returned_value, $row['revision_id'], 'body_summary');
      }
      
      if($addToSummary) {
        if( $detected || $detected_summary ) {
          drupal_set_message("<a href=\"".$base_url."node/".$row['nid']."\">".
                  $row['nid']."</a>: [".$row['type']."] ".$row['title'], 'warning');
        } else {
          // Already done, remove it from the list.
          $row_count--;
        }       
      }
      $row_index--;
    }while( $row_index > 0);

    if($addToSummary) {
      if( $fixData ) {
        drupal_set_message("Data Correction (".$scanType.": ".$row_count.")");
      } else {
        drupal_set_message("Data Validation (".$scanType.": ".$row_count.")");
      }
    }
  }    
  return $row_count;
}

/**
 * 
 * @param type $form
 * @param type $form_state
 */
function broadsoft_content_correction_admin_config_form_submit($form, &$form_state) {
/* interesting to look at form arrays
  drupal_set_message("I am inside submit handler. Printed form values below");
  drupal_set_message("<pre>" . print_r($form_state['values'], 1) . "</pre>");
  drupal_set_message("Printing Form Structure Below !");
  drupal_set_message("<pre>" . print_r($form, 1) . "</pre>");  
  */
  $correction = !empty($form_state['values']['broadsoft_content_correction_enable_update']);
  
  //////////////////////////////////////
  // Nodes
  $detected_count = scanForContentCorrection( 'field_data_body', 
                                              $form_state['values']['broadsoft_content_correction_node_href'],
                                              $form_state['values']['broadsoft_content_correction_new_node_url'],
                                              $form_state['values']['broadsoft_content_correction_base_site_url'],
                                              'node', $correction);

  $detected_count = scanForContentCorrection( 'field_data_body', 
                                              $form_state['values']['broadsoft_content_correction_file_href'],
                                              $form_state['values']['broadsoft_content_correction_new_file_url'],
                                              $form_state['values']['broadsoft_content_correction_base_site_url'],
                                              'file', $correction);
  
  if($correction) {
    // Apply the correction
    $detected_count = scanForContentCorrection( 'field_revision_body', 
                                                $form_state['values']['broadsoft_content_correction_node_href'],
                                                $form_state['values']['broadsoft_content_correction_new_node_url'],
                                                $form_state['values']['broadsoft_content_correction_base_site_url'],
                                                'node', $correction, false);

    $detected_count = scanForContentCorrection( 'field_revision_body', 
                                                $form_state['values']['broadsoft_content_correction_file_href'],
                                                $form_state['values']['broadsoft_content_correction_new_file_url'],
                                                $form_state['values']['broadsoft_content_correction_base_site_url'],
                                                'file', $correction, false);
  }
}


/**
 * 
 * @param type $form
 * @param type $form_state
 * @return type
 */
function broadsoft_content_correction_admin_config_form($form, &$form_state) {
  $form = array();
  $form['broadsoft_content_correction_base_site_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Drupal base site URL'),
    '#default_value' => variable_get('broadsoft_content_correction_base_site_url', BROADSOFT_CONTENT_CORRECTION_BASE_SITE_URL),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  
  $form['broadsoft_content_correction_new_node_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Drupal base node URL'),
    '#default_value' => variable_get('broadsoft_content_correction_new_node_url', BROADSOFT_CONTENT_CORRECTION_NEW_NODE_URL),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  
  $form['broadsoft_content_correction_new_file_url'] = array(
    '#type' => 'textfield',
    '#title' => t('Drupal base file URL'),
    '#default_value' => variable_get('broadsoft_content_correction_new_file_url', BROADSOFT_CONTENT_CORRECTION_NEW_FILE_URL),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  
  $form['broadsoft_content_correction_node_href'] = array(
    '#type' => 'textfield',
    '#title' => t('Drupal node base href tag match pattern'),
    '#default_value' => variable_get('broadsoft_content_correction_node_href', BROADSOFT_CONTENT_CORRECTION_NODE_HREF),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => FALSE,
  );
  
  $form['broadsoft_content_correction_file_href'] = array(
    '#type' => 'textfield',
    '#title' => t('Drupal file base href tag match pattern'),
    '#default_value' => variable_get('broadsoft_content_correction_file_href', BROADSOFT_CONTENT_CORRECTION_FILE_HREF),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => FALSE,
  );
  
  $form['broadsoft_content_correction_enable_update'] = array(
    '#type' => 'checkbox',
    '#title' => t('Activate the Data correction'),
    '#description' => t('Submitted when disabled will only result in an analysis of the correction needed.'),
    '#default_value' => variable_get('broadsoft_content_correction_enable_update', FALSE),
    '#required' => FALSE,
  );  
  $form['#submit'][] = 'broadsoft_content_correction_admin_config_form_submit';  

  return system_settings_form($form);
}

?>