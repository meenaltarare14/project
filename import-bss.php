<?php

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

define("BROADWORKS", 6);
define("PBX", 5);
$db = 'xchange';
define("XC_TAXO_AOI_ACCESS", 8);
define("XC_TAXO_BROADCLOUD_COMPONENT", 11);
define("XC_TAXO_BROADTOUCH_COMPONENT", 15);
define("XC_TAXO_BROADTOUCH_RELEASE", 17);
define("XC_TAXO_BROADWORKS_COMPONENT", 4);
define("XC_TAXO_BROADWORKS_OS", 7);
define("XC_TAXO_BROADWORKS_RELEASE", 3);
define("XC_TAXO_CONNECT_RELEASE", 20);
define("XC_TAXO_LOKI_RELEASE", 19);
define("XC_TAXO_SYNERGY", 13);
//db_set_active('pbx');

db_set_active($db);

$query = db_select("node", "n");
//$query->distinct();
$query->fields('n', array('nid', 'uid', 'title', 'created', 'changed'));
$query->fields('nr', array('body', 'vid'));
$query->fields('a', array(
  'field_software_file_md5sum_value',
  'field_software_file_fid',
  'field_software_file_list',
  'field_software_file_data',
));
$query->fields('f', array(
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status'
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
$query->leftJoin("content_type_broadsoft_software", "a", "a.nid = n.nid AND a.vid = n.vid");
$query->leftJoin("files", "f", "a.field_software_file_fid = f.fid");
$query->condition("n.status", 1);
$query->condition("n.type", 'broadsoft_software');
//$query->condition("n.nid", 478221);
$query->orderBy("nr.nid", "ASC");
//$query->orderBy("nr.vid", "DESC");
//$query->range(0, 1);
//dpq($query);

//$result = $query->countQuery()->execute()->fetchField(); //->fetchAllAssoc('nid');
//var_dump($result);die;
$result = $query->execute()->fetchAllAssoc('nid');
foreach ($result as $nid => $old_node) {
  $query = db_select("term_node", "tn");
  $query->fields('tn', array('tid'));
  $query->fields('td', array('vid'));
  $query->innerJoin("term_data", "td", "td.tid = tn.tid");
  $query->condition("tn.nid", $old_node->nid);
  $query->condition("tn.vid", $old_node->vid);
  $taxo_result = $query->execute()->fetchAllKeyed(0,1);
//  var_dump($taxo_result);
  if(!empty($taxo_result)) {
   $result[$nid]->categories = $taxo_result;
  }
}
//var_dump($result);
db_set_active('default');
foreach ($result as $nid => $node_info) {
  // Check if we already imported old nid.
  $query = db_select("field_data_field_xchange_nid", "old_nid");
  $query->fields('old_nid', array('entity_id'));
  $query->condition("old_nid.field_xchange_nid_value", $node_info->nid);
  $query->condition("old_nid.bundle", 'broadsoft_software');
  $old_node = $query->execute()->fetchField();
  if ($old_node == 0 ) {
    if ($node_info->uid == 1) {
      $user_id = 1;
    }
    else {
      // Get the latest uid corresponding to the old node author uid
      $query = db_select("field_data_field_xchange_uid", "xchange_uid");
      $query->fields('xchange_uid', array('entity_id'));
      $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
      $query->condition("xchange_uid.field_xchange_uid_value", $node_info->uid);
      $query->condition("xchange_source.field_xchange_source_value", $db);
      //$user_id = $query->execute()->fetchField();
      // Special case for Broadsoft Software.
      if (empty($user_id)) {
        $user_id = 1;
      }
    }
    if (!empty($user_id)) {
      // Fetch new tid values for categories
      $additional_info = array();
      if(isset($node_info->categories)) {
        $new_aoi_access = array();
        foreach ($node_info->categories as $tid => $vid) {
          $query = db_select("field_data_field_xchange_old_tid", "b");
          $query->fields('b', array('entity_id', 'bundle'));
          $query->condition("b.field_xchange_old_tid_value", $tid);
          switch ($vid) {
            case XC_TAXO_AOI_ACCESS:
              $query->condition("b.bundle", 'aoi_access');
              break;
            case XC_TAXO_BROADCLOUD_COMPONENT:
              $query->condition("b.bundle", 'broadcloud_component');
              break;
            case XC_TAXO_BROADTOUCH_COMPONENT:
              $query->condition("b.bundle", 'broadtouch_component');
              break;
            case XC_TAXO_BROADTOUCH_RELEASE:
              $query->condition("b.bundle", 'broadtouch_release');
              break;
            case XC_TAXO_BROADWORKS_COMPONENT:
              $query->condition("b.bundle", 'broadworks_component');
              break;
            case XC_TAXO_BROADWORKS_OS:
              $query->condition("b.bundle", 'broadworks_os');
              break;
            case XC_TAXO_BROADWORKS_RELEASE:
              $query->condition("b.bundle", 'broadworks_release');
              break;
            case XC_TAXO_CONNECT_RELEASE:
              $query->condition("b.bundle", 'connect_release');
              break;
            case XC_TAXO_LOKI_RELEASE:
              $query->condition("b.bundle", 'loki_release');
              break;
            case XC_TAXO_SYNERGY:
              $query->condition("b.bundle", 'synergy');
              break;
          }
          $new_tid = $query->execute()->fetchAssoc();
//          var_dump($new_tid);
          if($new_tid) {
            $additional_info[$new_tid['bundle']][] = $new_tid['entity_id'];
          }
        }
      }
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array) $node_info, $additional_info);
//      var_dump($node_info);
      $new_node = broadworks_create_bss_node($node_info);
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
  else {
    var_dump($_GET);
    if(isset($_GET['update']) && $_GET['update'] == 1) {
      $additional_info['new_nid'] = $old_node;
//      // Fetch new tid values for aoi access
//      if(isset($node_info->content_field_indirect_customers)) {
//        $new_content_field_indirect_customers = $additional_info['new_content_field_indirect_customers'] = array();
//        foreach ($node_info->content_field_indirect_customers as $nid) {
//          $query = db_select("field_data_field_xchange_cs_nid", "old_nid");
//          $query->fields('old_nid', array('entity_id'));
//          $query->condition("old_nid.field_xchange_cs_nid_value", $nid);
//          $new_nid = $query->execute()->fetchField();
//          if($new_nid) {
//            $new_content_field_indirect_customers[] = array($new_nid);
//          }
//        }
//        if(!empty($new_content_field_indirect_customers)) {
//          $additional_info['new_content_field_indirect_customers'] = $new_content_field_indirect_customers;
//        }
//      }
//
      $node_info = (object) array_merge((array) $node_info, $additional_info);
////      var_dump($node_info);
//      $updated_node = broadworks_create_bss_node($node_info, TRUE);
      // for file attachments
      broadworks_create_bss_node($node_info, TRUE);
      echo "Node UPDATED with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
    else {
      echo "Node already created with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }

  }
}

function broadworks_create_bss_node($node_info = '', $update = FALSE) {

  if($update) {
    $entity = node_load($node_info->new_nid);
    $uri = str_replace('/bw/broadworks/xchangeRepos', 's3://file-storage-100/xchange', $node_info->filepath);
    // check if uri already exists
    $fid = db_select('file_managed', 'f')
      ->fields('f', array('fid'))
      ->condition('uri', $uri,'=')
      ->execute()
      ->fetchField();
    if($fid) {
      $file = file_load($fid);
    }
    else {
      $filename = $node_info->filename;
      $file = new stdClass;
      $file->uid = $node_info->uid;
      $file->filename = $filename;
      $file->uri = $uri;
      $file->filemime = $node_info->filemime;
      $file->filesize = $node_info->filesize;
      $file->status = $node_info->status;
      drupal_write_record('file_managed', $file);
    }
    $ewrapper = entity_metadata_wrapper('node', $entity);
    // File attachment field
    $ewrapper->field_software_file->set(array(
      'fid' => $file->fid,
      'display' => 1,
      'description' => '',
    ));
    $ewrapper->save();
    // add file usage
    $type = 'node';
    $id = $entity->nid;
    file_usage_add($file, 'file', $type, $id);
//    if(isset($node_info->new_content_field_indirect_customers) && !empty($node_info->new_content_field_indirect_customers)) {
//      $ewrapper->field_indirect_customers->set($node_info->new_content_field_indirect_customers);
//    }
//    else {
//      $ewrapper->field_indirect_customers = array();
//    }
    return $entity;
  }
  else {
    $values = array(
      'type' => 'broadsoft_software',
      'uid' => $node_info->new_uid,
      'status' => 1,
      'comment' => 2,
      'promote' => 0,
      'created' => $node_info->created,
      'updated' => $node_info->changed,
    );
    $entity = entity_create('node', $values);

    $ewrapper = entity_metadata_wrapper('node', $entity);

    $ewrapper->title->set($node_info->title);
    $ewrapper->revision->set(1);
    if (!empty($node_info->body)) {
      $ewrapper->body->set(
        array(
          'value' => $node_info->body,
          'summary' => '',
          'format' => 'filtered_html',
        ));
    }

    $ewrapper->field_software_file_md5sum->set($node_info->field_software_file_md5sum_value);
    // Old nid
    $ewrapper->field_xchange_nid->set($node_info->nid);

    if(isset($node_info->aoi_access)) {
      $ewrapper->field_aoi_access->set($node_info->aoi_access);
    }
    // Single
    if(isset($node_info->broadcloud_component)) {
      $ewrapper->field_broadcloud_component->set($node_info->broadcloud_component[0]);
    }
    if(isset($node_info->broadtouch_component)) {
      $ewrapper->field_broadtouch_component->set($node_info->broadtouch_component);
    }
    if(isset($node_info->broadtouch_release)) {
      $ewrapper->field_broadtouch_release->set($node_info->broadtouch_release);
    }
    if(isset($node_info->broadworks_component)) {
      $ewrapper->field_broadworks_component->set($node_info->broadworks_component);
    }
    if(isset($node_info->broadworks_os)) {
      $ewrapper->field_broadworks_os->set($node_info->broadworks_os);
    }
    if(isset($node_info->broadworks_release)) {
      $ewrapper->field_broadworks_release->set($node_info->broadworks_release);
    }
    // Single
    if(isset($node_info->connect_release)) {
      $ewrapper->field_connect_release->set($node_info->connect_release[0]);
    }
    // Single
    if(isset($node_info->loki_release)) {
      $ewrapper->field_loki_release->set($node_info->loki_release[0]);
    }
    if(isset($node_info->synergy)) {
      $ewrapper->field_synergy->set($node_info->synergy);
    }
  }
  // file attachment
  $uri = str_replace('/bw/broadworks/xchangeRepos', 's3://file-storage-100/xchange', $node_info->filepath);
  // check if uri already exists
  $fid = db_select('file_managed', 'f')
    ->fields('f', array('fid'))
    ->condition('uri', $uri,'=')
    ->execute()
    ->fetchField();
  if($fid) {
    $file = file_load($fid);
  }
  else {
    $filename = $node_info->filename;
    $file = new stdClass;
    $file->uid = $node_info->uid;
    $file->filename = $filename;
    $file->uri = $uri;
    $file->filemime = $node_info->filemime;
    $file->filesize = $node_info->filesize;
    $file->status = $node_info->status;
    drupal_write_record('file_managed', $file);
  }
  // File attachment field
  $ewrapper->field_software_file->set(array(
    'fid' => $file->fid,
    'display' => 1,
    'description' => '',
  ));
  $ewrapper->save();
  // add file usage
  $type = 'node';
  $id = $entity->nid;
  file_usage_add($file, 'file', $type, $id);
  return $entity;
}