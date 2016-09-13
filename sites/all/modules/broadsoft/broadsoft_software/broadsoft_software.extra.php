<?php

/**
 * NOTE:
 * there are many hardcoded IDs and field names
 * unknown option - download_history_per_user
 * unknown 'content_field_*', 'upload' tables
 */


/**
 * @@@ this is a clone, reunite
 */
function GetTermName($term_tid) {
  $termObj = taxonomy_term_load($term_tid);
  if ($termObj) {
    return $termObj->name;
  }
  return "";
}

/**
 * Implementation of hook_block_info().
 *
 * @param string  $op    one of "list", "view", "save" and "configure" (i.e. calling context)
 * @param integer $delta code to identify the block (a module can have multiple blocks)
 * @param array   $edit  only for "save" operation
 *
 * @return array
 *
 * @see http://drupal.org/node/206758
 */
function broadsoft_software_block_info() {
  $blocks['broadtouch_software_block_1']['info'] = t('Base Software Download');
  $blocks['broadtouch_software_block_1']['properties']['administrative'] = TRUE;

  return $blocks;
}

/**
 * Implements hook_block_view().
 */
function broadsoft_software_block_view($delta = '') {
  if ($delta == 'broadtouch_software_block_1') {

    $startTimeMS = broadsoft_profiler_startTimer(TRUE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);

    $view = views_get_current_view();
    if (isset($_GET['os'])) {
      $OS_tid = $_GET['os'];
    }
    else {
      if (isset($_SESSION['views']['software_download_center']['default']['os'])) {
        $OS_tid = $_SESSION['views']['software_download_center']['default']['os'];
      }
    }
    if (isset($_GET['server'])) {
      $ServerType_tid = $_GET['server'];
    }
    else {
      if (isset($_SESSION['views']['software_download_center']['default']['server'])) {
        $ServerType_tid = $_SESSION['views']['software_download_center']['default']['server'];
      }
    }
    if (isset($_GET['release'])) {
      $Release_tid = $_GET['release'];
    }
    else {
      if (isset($_SESSION['views']['software_download_center']['default']['release'])) {
        $Release_tid = $_SESSION['views']['software_download_center']['default']['release'];
      }
    }

    $block['subject'] = t('Base Software Download');

    if (empty($OS_tid) || empty($Release_tid) || empty($ServerType_tid)) {
      if (empty($OS_tid)) {
        $block['content'] .= t('Please select an Operating System in the above list\n');
      }
      elseif (empty($Release_tid)) {
        $block['content'] .= t('Please select a Release in the above list\n');
      }
      elseif (empty($ServerType_tid)) {
        $block['content'] .= t('Please select a Server Type in the above list\n');
      }
    }
    else {
      $header = array('Title', 'Description', 'md5sum', 'Already <br>Downloaded');

      $rows = array();

      // Get that node from DB
      $query = "SELECT node.nid, node.title FROM {node} 
                INNER JOIN {taxonomy_index} tn1 ON node.nid = tn1.nid
                INNER JOIN {taxonomy_index} tn2 ON node.nid = tn2.nid
                INNER JOIN {taxonomy_index} tn3 ON node.nid = tn3.nid
                WHERE node.status = '1' 
                    AND node.type = 'broadworks_base_software' 
                    AND tn1.tid = :otid 
                    AND tn2.tid = :rtid  
                    AND tn3.tid = :stid ";
      $dbrow_node = db_query($query, array(':otid' => $OS_tid, ':rtid' => $Release_tid, ':stid' => $ServerType_tid))->fetchAssoc();

      // TODO: hardcoded field names
      switch (GetTermName($OS_tid)) {
        case 'Linux/Red Hat':
          $basebin_fid_field_name = 'field_linux_rh_basebin_file_fid';
          $ipbin_fid_field_name = 'field_linux_rh_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_rh_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_rh_ip_bin_md5sum_value';
          break;
        case 'Solaris/x86':
          $basebin_fid_field_name = 'field_solaris_x86_basebin_file_fid';
          $ipbin_fid_field_name = 'field_solaris_x86_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_x86_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_x86_ip_bin_md5sum_value';
          break;
        default:
          $basebin_fid_field_name = 'field_solaris_sparc_basebin_file_fid';
          $ipbin_fid_field_name = 'field_solaris_sparc_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_sparc_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_sparc_ip_bin_md5sum_value';
          break;
      }

      $node = NULL;
      if (is_array($dbrow_node)) {
        $node = node_load($dbrow_node['nid']);
      }

      // ***** base software for this os-rel-component
      $base_found = FALSE;
      if ($node) {
        // TODO: hardcoded field names
        switch (GetTermName($OS_tid)) {
          case 'Linux/Red Hat':
            $basebin_fid_field_name = 'field_linux_rh_basebin_file_fid';
            $ipbin_fid_field_name = 'field_linux_rh_ip_bin_file_fid';
            $md5_base_val_field_name = 'field_rh_basebin_md5sum_value';
            $md5_ip_val_field_name = 'field_rh_ip_bin_md5sum_value';
            break;
          case 'Solaris/x86':
            $basebin_fid_field_name = 'field_solaris_x86_basebin_file_fid';
            $ipbin_fid_field_name = 'field_solaris_x86_ip_bin_file_fid';
            $md5_base_val_field_name = 'field_x86_basebin_md5sum_value';
            $md5_ip_val_field_name = 'field_x86_ip_bin_md5sum_value';
            break;
          default:
            $basebin_fid_field_name = 'field_solaris_sparc_basebin_file_fid';
            $ipbin_fid_field_name = 'field_solaris_sparc_ip_bin_file_fid';
            $md5_base_val_field_name = 'field_sparc_basebin_md5sum_value';
            $md5_ip_val_field_name = 'field_sparc_ip_bin_md5sum_value';
            break;
        }

        // TODO: D7 does not have 'content_type_broadworks_base_software' table
        $query = "SELECT f.fid, f.filename, f.filepath, ctbbs." . $md5_base_val_field_name . " AS md5value 
                  FROM {content_type_broadworks_base_software} ctbbs, {file_managed} f
				  WHERE ctbbs.nid = :nid 
				    AND ctbbs." . $basebin_fid_field_name . " = f.fid ";
        $dbrow_f = db_query($query, array(':nid' => $node->nid))->fetchObject();

        if (is_object($dbrow_f)) {
          $base_found = TRUE;
          $fname = preg_replace('/[.](sol|linux).*/i', '', basename($dbrow_f->filename));
          // WAS $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files".$dbrow_f->filepath);
          $fpath = preg_replace('/\/bw\/broadworks\/xchangeRepos/', '', "system/files" . $dbrow_f->filepath);

          // e.g.: http://xchangeserver0.broadsoft.com/php/xchange/system/files/%252Fbw/broadworks/xchangeRepos/Release_17/build_1.434/Linux_x86/IP.as.17.0.434.ip20100328.Linux-x86_64.tar.gz
          // TODO: hardcoded ID - 290
          $rows[] = array(
            l($fname, $fpath),
            'Base Installation ' . ($ServerType_tid == 290 ? 'Preview ' : '') . '(zipped binary file)',
            $dbrow_f->md5value,
            // TODO: unknown option - download_history_per_user
            l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user)
          );
        }
      }
      if (!$base_found) {
        $rows[] = array('(none)', 'Base Installation', '-', '-');
        //DebugPrint(__LINE__, "No Base Soft found? nid=".$node->nid.", os_tid=".$OS_tid.", rel_tid=".$Release_tid.", comp_tid=".$ServerType_tid." query is:");
        //DebugPrint(__LINE__, $query);
      }


      // ***** IP bin for this os-rel-component
      $IP_found = FALSE;
      if ($node) {
        // TODO: D7 does not have 'content_type_broadworks_base_software' table
        $query = "SELECT f.fid, f.filename, f.filepath, ctbbs." . $md5_ip_val_field_name . " AS md5value 
                  FROM {content_type_broadworks_base_software} ctbbs, {file_managed} f
				  WHERE ctbbs.nid = :nid  
				    AND ctbbs." . $ipbin_fid_field_name . " = f.fid ";
        $dbrow_f = db_query($query, array(':nid' => $node->nid))->fetchObject();
        if (is_object($dbrow_f)) {
          $IP_found = TRUE;
          $fname = preg_replace('/[.](sol|linux).*/i', '', basename($dbrow_f->filename));
          // WAS $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files".$dbrow_f->filepath);
          $fpath = preg_replace('/\/bw\/broadworks\/xchangeRepos/', '', "system/files" . $dbrow_f->filepath);

          $rows[] = array(
            l($fname, $fpath),
            'Installation Patch (zipped binary file)',
            $dbrow_f->md5value,
            // TODO: unknown option - download_history_per_user
            l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user)
          );
        }
      }
      if (!$IP_found) {
        $rows[] = array('(none)', 'Installation Patch', '-', '-');
      }


      // *****  preupgrade check script for this rel
      $PreUp_found = FALSE;
      // TODO: hardcoded ID - 125
      $PreUpScript_tid = 125;

      // TODO: unknown table - upload
      $query = "SELECT f.fid, f.filename, f.filepath
				FROM {file_managed} f, node n, upload 
				INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
				LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
				WHERE upload.fid = f.fid
                  AND n.nid = upload.nid
                  AND n.type = 'broadsoft_software'
                  AND n.status = '1'
                  AND tn1.tid = :ptid 
                  AND tn3.tid = :rtid
				ORDER BY f.fid DESC ";

      $dbrow_f = db_query($query, array(':ptid' => $PreUpScript_tid, ':rtid' => $Release_tid))->fetchObject();
      if (is_object($dbrow_f)) {
        $PreUp_found = TRUE;
        $md5val = '-';

        // TODO: unknown field - field_software_file_md5sum_value
        $query2 = "SELECT ctbs.field_software_file_md5sum_value AS md5value
				   FROM content_type_broadsoft_software ctbs
				   WHERE ctbs.field_software_file_fid = :fid ";
        $dbrow_f2 = db_query($query2, array(':fid' => $dbrow_f->fid))->fetchObject();
        if (is_object($dbrow_f2)) {
          $md5val = $dbrow_f2->md5value;
        }

        $fname = preg_replace('/-Rel.*/i', '', basename($dbrow_f->filename));
        $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);
        $rows[] = array(
          l($fname, $fpath),
          'PreUpgrade Check script (self-extracting)',
          $md5val,
          // TODO: unknown option - download_history_per_user
          l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user)
        );
      }
      if (!$PreUp_found) {
        $rows[] = array('(none)', 'PreUpgrade check script', '-', '-');
      }

      // ***** Release Notes for this rel (this is a BW Document)
      // TODO: hardcoded ID - 104
      $ReleaseNotes_tid = 104;

      // TODO: unknown table - upload
      $query = "SELECT f.filename, f.filepath
				FROM {file_managed} f, node n, upload 
				INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
				LEFT JOIN {taxonomy_index} tn2 ON upload.nid = tn2.nid 
				LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
				WHERE upload.fid = f.fid 
                  AND n.nid = upload.nid
                  AND n.status = '1'
                  AND tn1.tid = :rntid 
                  AND tn2.tid = :stid 
                  AND tn3.tid = :rtid ";
      $result = db_query($query, array(':rntid' => $ReleaseNotes_tid, ':stid' => $ServerType_tid, ':rtid' => $Release_tid));
      foreach ($result as $dbrow_f) {
        $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);
        $rows[] = array(l(basename($dbrow_f->filename), $fpath), 'Release Notes', '-', '-');
      }

      // ***** patchtool - swmanager
      // TODO: hardcoded ID - 137
      $SWManager_tid = 137;

      // TODO: unknown table - upload
      $query = "SELECT f.fid, f.filename, f.filepath
				FROM {file_managed} f, node n, upload
				INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
				LEFT JOIN {taxonomy_index} tn2 ON upload.nid = tn2.nid 
				LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
				WHERE upload.fid = f.fid
                  AND n.nid = upload.nid
                  AND n.type = 'broadsoft_software'
                  AND n.status = '1'
                  AND tn1.tid = :mtid 
                  AND tn2.tid = :otid 
                  AND tn3.tid = :rtid
				ORDER BY f.fid DESC "; // assuming biggest fid are the latest
      $result = db_query($query, array(':mtid' => $SWManager_tid, ':otid' => $OS_tid, ':rtid' => $Release_tid))->fetchObject();
      if (is_object($dbrow_f)) {
        $md5val = '-';
        // TODO: unknown field - field_software_file_md5sum_value
        $query2 = "SELECT ctbs.field_software_file_md5sum_value AS md5value
				   FROM content_type_broadsoft_software ctbs
				   WHERE ctbs.field_software_file_fid = :fid ";
        $dbrow_f2 = db_query($query2, array(':fid' => $dbrow_f->fid))->fetchObject();
        if (is_object($dbrow_f2)) {
          $md5val = $dbrow_f2->md5value;
        }

        $fname = preg_replace('/[.](sol|lin|bin).*/i', '', $dbrow_f->filename); // strip extension
        $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);
        $rows[] = array(
          l($fname, $fpath),
          'Software Manager/Patch Tool',
          $md5val,
          // TODO: unknown option - download_history_per_user
          l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user)
        );
      }
      else {
        $rows[] = array('(none)', 'Software Manager/Patch tool', '-', '-');
      }

      // ***** patchtool - swmanager RELEASE NOTES
      // TODO: hardcoded IDs - 104, 137
      $ReleaseNotes_tid = 104;
      $SWManager_tid = 137;

      // TODO: unknown table - upload
      $query = "SELECT f.filename, f.filepath
				FROM {file_managed} f, node n, upload 
				INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
				LEFT JOIN {taxonomy_index} tn2 ON upload.nid = tn2.nid 
				LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
				WHERE upload.fid = f.fid 
                  AND n.nid = upload.nid
                  AND n.status = '1'
                  AND tn1.tid = :rntid 
                  AND tn2.tid = :stid
                  AND tn3.tid = :rtid ";

      $result = db_query($query, array(':rntid' => $ReleaseNotes_tid, ':stid' => $SWManager_tid, ':rtid' => $Release_tid));
      foreach ($result as $dbrow_f) {
        $fname = preg_replace('/[.]txt/i', '', basename($dbrow_f->filename)); // strip extension
        $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);
        $rows[] = array(l($fname, $fpath), 'Software Manager/Patch Tool Release Notes', '-', '-');
      }

      // ***** OCI zips
      // TODO: hardcoded IDs - 192
      $OCISoft_tid = 192;

      // TODO: unknown table - upload
      $query = "SELECT f.fid, f.filename, f.filepath 
				FROM {file_managed} f, node n, upload 
				INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
				LEFT JOIN {taxonomy_index} tn2 ON upload.nid = tn2.nid 
				LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
				LEFT JOIN {taxonomy_index} tn4 ON upload.nid = tn4.nid 
				WHERE upload.fid = f.fid
                  AND n.nid = upload.nid
                  AND n.type = 'broadsoft_software'
                  AND n.status = '1'
                  AND tn1.tid = :octid 
                  AND tn2.tid = :ostid
                  AND tn3.tid = :rtid
                  AND tn4.tid = :stid
				ORDER BY f.fid DESC"; // assuming biggest fid are the latest

      $result = db_query($query, array(':octid' => $OCISoft_tid, ':ostid' => $OS_tid, ':rtid' => $Release_tid, ':stid' => $ServerType_tid,));
      $firstTime = TRUE;
      foreach ($result as $dbrow_f) {
        if ($firstTime) {
          $rows[] = array('', '', '', '');
        } // separator blank row
        $firstTime = FALSE;
        $fname = preg_replace('/[.]zip/i', '', basename($dbrow_f->filename)); // strip extension
        $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);
        // TODO: unknown option - download_history_per_user
        $rows[] = array(l($fname, $fpath), 'Provision Client', '-', l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user));
      }

      // *****  Security Tool Kit for this rel
      // TODO: hardcoded IDs - 67
      if ($ServerType_tid == 67) { // for AS only
        $found = FALSE;
        // TODO: hardcoded IDs - 246
        $tid = 246;
        // TODO: unknown table - upload
        $query = "	SELECT f.fid, f.filename, f.filepath
					FROM {file_managed} f, node n, upload
					INNER JOIN {taxonomy_index} tn1 ON upload.nid = tn1.nid 
					LEFT JOIN {taxonomy_index} tn3 ON upload.nid = tn3.nid 
					WHERE upload.fid = f.fid
					AND n.nid = upload.nid
					AND n.type = 'broadsoft_software'
					AND n.status = '1'
					AND tn1.tid = :tid  
					AND tn3.tid = :rtid 
					ORDER BY f.fid DESC"; // assuming biggest fid are the latest

        $dbrow_f = db_query($query, array(':tid' => $tid, ':rtid' => $Release_tid))->fetchObject();
        if (is_object($dbrow_f)) {
          $md5val = '-';

          // TODO: unknown field - field_software_file_md5sum_value
          $query2 = "SELECT ctbs.field_software_file_md5sum_value AS md5value
					 FROM content_type_broadsoft_software ctbs
					 WHERE ctbs.field_software_file_fid = :fid ";
          $dbrow_f2 = db_query($query2, array(':fid' => $dbrow_f->fid))->fetchObject();
          if (is_object($dbrow_f2)) {
            $md5val = $dbrow_f2->md5value;
          }

          $fname = preg_replace('/-Rel.*/i', '', basename($dbrow_f->filename));
          $fpath = preg_replace('/\/bw\//', '/%2Fbw/', "system/files" . $dbrow_f->filepath);

          // TODO: unknown option - download_history_per_user
          $rows[] = array(l($fname, $fpath), 'Security Tool Kit', $md5val, l(broadsoft_software_getDownloaded($dbrow_f->fid), download_history_per_user));
        }
      }
    }

    $attributes = array(
      'width' => "100%",
      'class' => 'views-table sticky-enabled cols-1 sticky-enabled',
    );
    if (count($rows)) {
      $variables = array(
        'header' => $header,
        'rows' => $rows,
        'attributes' => $attributes,
      );
      $block['content'] .= theme_table($variables);
    }
    else {
      $block['content'] .= "There is no base software corresponding to your selection";
    }

    broadsoft_profiler_logElapsedTime($startTimeMS, "function", $broadsoft_profiler_id);

    return $block;
  }
}


/** ===========================================================================
 * OBSOLETE???
 **/
function broadsoft_software_getAssociatedBundle($patchNid, &$retBundleNidArr, &$retBundleDateArr) {
  $startTimeMS = broadsoft_profiler_startTimer(FALSE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);

  $view = views_get_current_view();
  $OS_tid = 0;
  if (isset($view->exposed_input)) {
    $OS_tid = $view->exposed_input['os'];
  }

  $retBundleNidArr = array();
  $retBundleDateArr = array();

  $i = 0;

  // TODO: unknown 'content_field_patch_nids' table
  $query = "SELECT nid
			FROM {content_field_patch_nids}
			WHERE field_patch_nids_nid = :pnid ";
  $result = db_query($query, array(':pnid' => $patchNid));

  foreach ($result as $dbrow) {
    $query2 = "SELECT title	FROM {node}	WHERE nid = :nid ";
    $dbrow2 = db_query($query2, array(':nid' => $dbrow->nid))->fetchAssoc();
    if (is_array($dbrow2)) {
      $retBundleNidArr[$i] = $dbrow['nid'];
      $retBundleDateArr[$i] = $dbrow2['title'];
      $i++;
    }
    else {
      // ??? @@@ error
    }
  }
  broadsoft_profiler_logElapsedTime($startTimeMS);
  return count($retBundleNidArr);
}


function broadsoft_software_getUniqueAssociatedBundleNid($patchNid, &$retBundleDateStr) {
  $startTimeMS = broadsoft_profiler_startTimer(FALSE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);

  $retBundleNid = NULL;
  $retBundleDateStr = "";

  $server_tid = 0; // used to differentiate bundles in case of a platform patch, doesn't hurt for server-specific patches
  if (isset($_GET['server'])) {
    $server_tid = $_GET['server'];
  }
  else {
    $view = views_get_current_view();
    if (isset($view->exposed_input)) {
      $server_tid = $view->exposed_input['server'];
    }
  }

// TODO: unknown 'content_field_patch_nids' table
  $query = "SELECT content_field_patch_nids.nid, node.title
			FROM {content_field_patch_nids}, {taxonomy_index}, {node}
			WHERE content_field_patch_nids.field_patch_nids_nid = :pnid
			  AND content_field_patch_nids.nid = node.nid
			  AND term_node.nid = content_field_patch_nids.nid
			  AND term_node.tid = :stnid ";

  $dbrow = db_query($query, array(':pnid' => $patchNid, ':stnid' => $server_tid))->fetchAllAssoc();
  if (count($dbrow) == 1) {
    $retBundleNid = $dbrow['nid'];
    $retBundleDateStr = preg_replace('/.*[.]pb/', '', $dbrow['title']);
  }
  else {
    $retBundleNid = NULL;
  }

  broadsoft_profiler_logElapsedTime($startTimeMS);

  return $retBundleNid;
}


function broadsoft_software_getDownloadedPatch($nid) {
  $startTimeMS = broadsoft_profiler_startTimer(FALSE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);

  $view = views_get_current_view();
  if (isset($view->exposed_input)) {
    $OS_tid = $view->exposed_input['os'];

    // TODO: there are hardcoded fields
    switch ($OS_tid) {
      case 101://'Linux/Red Hat':
        $field = 'field_linux_rh_patch_file_fid';
        break;
      case 100://'Solaris/x86':
        $field = 'field_solaris_x86_patch_file_fid';
        break;
      case 99://'Solaris/sparc':
        $field = 'field_solaris_sparc_patch_file_fid';
        break;
    }

    if (!empty($field)) {
      $query = "SELECT ctbp." . $field . " AS fid
				FROM {content_type_broadworks_patch} ctbp
				WHERE ctbp.nid = :nid ";
      $dbrow = db_query($query, array(':nid' => $nid))->fetchAssoc();
      if (is_array($dbrow)) {
        broadsoft_profiler_logElapsedTime($startTimeMS);
        return broadsoft_software_getDownloaded($dbrow['fid']);
      }
    }
    broadsoft_profiler_logElapsedTime($startTimeMS);
    return 'No';
  }
  broadsoft_profiler_logElapsedTime($startTimeMS);
  return "-";
}


function broadsoft_software_getDownloadedfromNID($nid) {
  $startTimeMS = broadsoft_profiler_startTimer(FALSE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);
  $fid = 0;
  if (!empty($nid)) {
    // TODO: unknown table - upload
    $query = "SELECT fid FROM {upload}  WHERE nid = :nid ";
    $dbrow = db_query($query, array(':nid' => $nid))->fetchAssoc();
    if (is_array($dbrow)) {
      $fid = $dbrow['fid'];
    }
  }

  broadsoft_profiler_logElapsedTime($startTimeMS);
  return broadsoft_software_getDownloaded($fid);
}


function broadsoft_software_getDownloaded($fid) {
  $startTimeMS = broadsoft_profiler_startTimer(FALSE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);
  if (!empty($fid)) {
    global $user;

    $query = "SELECT dcid
			  FROM {download_count} dc
			  WHERE dc.fid = :fid AND dc.uid = :uid ";
    $dbrow = db_query($query, array(':fid' => $fid, ':uid' => $user->uid))->fetchAssoc();
    if (is_array($dbrow)) {
      broadsoft_profiler_logElapsedTime($startTimeMS);
      return 'Yes';
    }
    broadsoft_profiler_logElapsedTime($startTimeMS);
    return 'No';
  }
  broadsoft_profiler_logElapsedTime($startTimeMS);
  return "-";
}


/** ===========================================================================
 **/
function broadsoft_software_getBundle($dcid, &$bundleNameRet, &$bundleURLRet) {
  $bundleNameRet = "";

  if (!empty($dcid)) {
    $query = "SELECT bundle_name
			  FROM {download_count}
			  WHERE download_count.dcid = :dcid ";
    $dbrow = db_query($query, array(':dcid' => $dcid))->fetchAssoc();
    if (is_array($dbrow)) {
      $bundleNameRet = $dbrow['bundle_name'];
    }
  }
}


/**
 * Gets all already downloaded patch nids for a user
 */
function GetAllDownloadedPatches($uid) {
  $startTimeMS = broadsoft_profiler_startTimer(TRUE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);
  $ret = array();

  $view = views_get_current_view();
  if (isset($view->exposed_input)) {
    $OS_tid = $view->exposed_input['os'];

    // TODO: there are IDs and hardcoded field names
    switch ($OS_tid) {
      case 101://'Linux/Red Hat':
        $field = 'field_linux_rh_patch_file_fid';
        break;
      case 100://'Solaris/x86':
        $field = 'field_solaris_x86_patch_file_fid';
        break;
      case 99://'Solaris/sparc':
        $field = 'field_solaris_sparc_patch_file_fid';
        break;
    }
  }
  if ($uid != 0 && isset($field) && isset($_GET['hide_dwld']) && $_GET['hide_dwld'] == 'yes') {
    $query = "SELECT download_count.nid as nid, download_count.fid
			  FROM {download_count}
			  INNER JOIN {content_type_broadworks_patch} ON content_type_broadworks_patch." . $field . " = download_count.fid 
			  WHERE download_count.uid = :uid ";
    $result = db_query($query, array(':uid' => $uid));
    foreach ($result as $row) {
      if ($row->nid) {
        $ret[] = $row->nid;
      }
    }
  }
  broadsoft_profiler_logElapsedTime($startTimeMS, "function", $broadsoft_profiler_id);

  return $ret;
}


function GetAllCustomBundle($uid) {
  $ret = array();

  $query = "SELECT distinct(bundle_name), timestamp 
            FROM {download_count} 
            WHERE uid = :uid 
            ORDER BY timestamp DESC";
  $result = db_query($query, array(':uid' => $uid));
  foreach ($result as $row) {
    if ($row->bundle_name) {
      $ret[$row->bundle_name] = date('M d, Y H:i:s', $row->timestamp);
    }
  }

  return $ret;
}


function GetAllPatchesFromCustomBundle($bundleName, $uid) {
  // Note: and uid - not needed, but left anyway
  $ret = array();

  $query = "SELECT node.nid as nid, node.title as title
			FROM {node} 
			INNER JOIN {download_count} ON download_count.nid = node.nid
			WHERE download_count.bundle_name = :bundle_name AND download_count.uid = :uid ";
  $result = db_query($query, array(':bundle_name' => $bundleName, ':uid' => $uid));
  foreach ($result as $row) {
    if ($row->nid) {
      $ret[$row->nid] = $row->title;
    }
  }
  return $ret;
}


/**
 * Gets all already downloaded bundle nids for a user
 */
function GetAllDownloadedBundles($uid) {
  $ret = array();

  $view = views_get_current_view();
  // TODO: there are hardcoded IDs and field names
  if (isset($view->exposed_input)) {
    $OS_tid = $view->exposed_input['os'];

    switch ($OS_tid) {
      case 101://'Linux/Red Hat':
        $field = 'field_linux_rh_file_fid';
        break;
      case 100://'Solaris/x86':
        $field = 'field_solaris_x86_file_fid';
        break;
      case 99://'Solaris/sparc':
        $field = 'field_solaris_sparc_file_fid';
        break;
    }
  }
  if ($uid != 0 && isset($field) && isset($_GET['hide_dwld']) && $_GET['hide_dwld'] == 'yes') {
    $query = "SELECT download_count.nid as nid, download_count.fid
			  FROM {download_count}
			  INNER JOIN {content_type_broadworks_patch_bundle} ON content_type_broadworks_patch_bundle." . $field . " = download_count.fid 
			  WHERE download_count.uid = :uid ";

    $result = db_query($query, array(':uid' => $uid));
    foreach ($result as $row) {
      if ($row->nid) {
        $ret[] = $row->nid;
      }
    }
  }
  return $ret;
}



function GetCustomDownloadFooter() {
  $startTimeMS = broadsoft_profiler_startTimer(TRUE, PROFILING_STAT___FUNCTION__, $broadsoft_profiler_id);
  $retHTML = '';
  $OS_tid = 0;
  if (!isset($_GET['os'])) {
    $retHTML .= 'You must select an Operating System and a Server, then press Apply<br>';
  }
  else {
    $OS_tid = $_GET['os'];

    $ServerType_tid = 0;
    if (!isset($_GET['server'])) {
      $retHTML .= 'You must select an Operating System and a Server, then press Apply<br>';
    }
    else {
      $ServerType_tid = $_GET['server'];

      // TODO: there are hardcoded field names
      switch (GetTermName($OS_tid)) {
        case 'Linux/Red Hat':
          $basebin_fid_field_name = 'field_linux_rh_basebin_file_fid';
          $ipbin_fid_field_name = 'field_linux_rh_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_rh_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_rh_ip_bin_md5sum_value';
          break;
        case 'Solaris/x86':
          $basebin_fid_field_name = 'field_solaris_x86_basebin_file_fid';
          $ipbin_fid_field_name = 'field_solaris_x86_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_x86_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_x86_ip_bin_md5sum_value';
          break;
        default:
          $basebin_fid_field_name = 'field_solaris_sparc_basebin_file_fid';
          $ipbin_fid_field_name = 'field_solaris_sparc_ip_bin_file_fid';
          $md5_base_val_field_name = 'field_sparc_basebin_md5sum_value';
          $md5_ip_val_field_name = 'field_sparc_ip_bin_md5sum_value';
          break;
      }

      $retHTML .= '<table>';

      $retHTML .= '<tr>';
      $retHTML .= '<th>Type</th>';
      $retHTML .= '<th>File</th>';
      $retHTML .= '<th>md5sum</th>';
      $retHTML .= '</tr>';

      // for BASE BINARY
      $query = "SELECT f.filename, f.filepath, ctbbs." . $md5_base_val_field_name . " AS md5value 
                FROM {content_type_broadworks_base_software} ctbbs, {file_managed} f
				WHERE f.filename LIKE '" . GetTermName($ServerType_tid) . "_Rel_19.sp1_1.574%' 
				  AND ctbbs." . $basebin_fid_field_name . " = f.fid ";
      $dbrow_f = db_query($query)->fetchObject();
      $retHTML .= '<tr>';
      $retHTML .= '<td>Base Binary</td>';
      if (is_object($dbrow_f)) {
        $retHTML .= '<td>';
        $retHTML .= '<a href="/php/xchange/getFile?path=' . $dbrow_f->filepath . '">' . $dbrow_f->filename . '</a>';
        $retHTML .= '</td>';
        $retHTML .= '<td>';
        $retHTML .= $dbrow_f->md5value;
        $retHTML .= '</td>';
      }
      else {
        $retHTML .= '<td>not available</td><td>not available</td>';
      }
      $retHTML .= '</tr>';

      // for IP bin for this os-rel-component
      $query = "SELECT f.filename, f.filepath, ctbbs." . $md5_ip_val_field_name . " AS md5value 
                FROM {content_type_broadworks_base_software} ctbbs, {file_managed} f
				WHERE f.filename like 'IP." . GetTermName($ServerType_tid) . ".19.sp1.574%' 
				  AND ctbbs." . $ipbin_fid_field_name . " = f.fid";
      $dbrow_f = db_query($query)->fetchObject();;
      $retHTML .= '<tr>';
      $retHTML .= '<td>Installation Patch</td>';
      if (is_object($dbrow_f)) {
        $retHTML .= '<td>';
        // TODO: need to update path
        $retHTML .= '<a href="/php/xchange/getFile?path=' . $dbrow_f->filepath . '">' . $dbrow_f->filename . '</a>';
        $retHTML .= '</td>';
        $retHTML .= '<td>';
        $retHTML .= $dbrow_f->md5value;
        $retHTML .= '</td>';
      }
      else {
        $retHTML .= '<td>not available</td><td>not available</td>';
      }
      $retHTML .= '</tr>';

      $retHTML .= '</table>';
    }
  }
  broadsoft_profiler_logElapsedTime($startTimeMS, "function", $broadsoft_profiler_id);

  return $retHTML;
}

