<?php

/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());

require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

/*$external_database = array(
  'database' => 'xchange',
  'username' => 'root',
  'password' => '',
  'host' => 'localhost',
  'driver' => 'mysql',
  'prefix' => '',
);

$databasekey = 'xchange';

Database::addConnectionInfo($databasekey, 'default', $external_database);*/
$db = 'xchange';
define("XC_TAXO_BROADWORKS_COMPONENT", 4);
define("XC_TAXO_BROADWORKS_RELEASE", 3);
define("XC_TAXO_AOI_ACCESS", 8);
define("XC_TAXO_BROADCLOUD_COMPONENT", 11);
define("XC_TAXO_BROADTOUCH_COMPONENT", 15);
define("XC_TAXO_BROADTOUCH_RELEASE", 17);

define("XC_TAXO_CPE_KIT", 12);
define("XC_TAXO_XCELERATE", 14);
define("XC_TAXO_PLATFORM", 18);
define("XC_TAXO_BS_DOC_CATEGORY", 5);

define("XC_TAXO_CONNECT_RELEASE", 20);
define("XC_TAXO_LOKI_RELEASE", 19);
define("XC_TAXO_SYNERGY", 13);
db_set_active($db);

$query = db_select("node", "n");
$query->fields('n', array('nid', 'uid', 'title', 'created', 'changed'));
$query->fields('nr', array('body', 'vid'));
$query->fields('a', array(
  'fid',
  'description',
));
$query->fields('doc', array(
  'field_document_date_value',
  'field_document_version_value',
  'field_hide_from_publishers_value',
));
$query->fields('ff', array(
  'field_file_format_value',
));
$query->fields('f', array(
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status'
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
$query->leftJoin("upload", "a", "a.nid = n.nid AND a.vid = n.vid");
$query->leftJoin("content_type_broadsoft_document", "doc", "doc.nid = n.nid AND doc.vid = n.vid");
$query->leftJoin("content_field_file_format", "ff", "ff.nid = n.nid AND ff.vid = n.vid AND delta = 0"); // delta > 0 only has null values
$query->leftJoin("files", "f", "a.fid = f.fid");
$query->condition("n.status", 1);
$query->condition("n.type", 'broadsoft_document');
$query->orderBy("nr.nid", "ASC");

$result = $query->execute()->fetchAllAssoc('nid');

// taxonomy terms and file format
foreach ($result as $nid => $old_node) {
  // taxo
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
db_set_active('default');
$count = 0;
$ucount = 0;

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_doc_nid", "old_nid");
  $query->fields('old_nid', array('entity_id'));
  $query->condition("old_nid.field_xchange_doc_nid_value", $node_info->nid);
  $query->condition("old_nid.bundle", 'documentation');
  $old_node = $query->execute()->fetchField();
  if ($old_node == 0) {
      $user_id = 1;
    if (!empty($user_id)) {
      // Fetch new tid values for categories
      $additional_info = load_taxonomies($node_info);
      $additional_info['new_uid'] = $user_id;
      $node_info = (object)array_merge((array)$node_info, $additional_info);
      $new_node = broadworks_create_doc_node($node_info);
      $count++;
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
  else {
    echo $node_info->nid.'<br/>';
    if(isset($_GET['update']) && $_GET['update'] == 1) {
      $user_id = 1;
      $additional_info = load_taxonomies($node_info);
      $additional_info['new_uid'] = $user_id;
      $additional_info['new_nid'] = $old_node;
      $node_info = (object)array_merge((array)$node_info, $additional_info);
      $new_node = broadworks_create_doc_node($node_info, TRUE);
      $ucount++;
      echo "Node UPDATED with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
    else {
      echo "Node already created with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
}

print "total document nodes created: ".$count."<br/>";
print "total document nodes updates: ".$ucount."<br/>";

function broadworks_create_doc_node($node_info = '', $update = FALSE) {

  if($update) {
    $entity = node_load($node_info->new_nid);
  }
  else {
    $values = array(
      'type' => 'documentation',
      'uid' => $node_info->new_uid,
      'status' => 1,
      'comment' => 2,
      'promote' => 0,
      'created' => $node_info->created,
      'updated' => $node_info->changed,
    );
    $entity = entity_create('node', $values);
  }

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

    // file attachments
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
    $file->uid = $node_info->new_uid;
    $file->filename = $filename;
    $file->uri = $uri;
    $file->filemime = $node_info->filemime;
    $file->filesize = $node_info->filesize;
    $file->status = $node_info->status;
    drupal_write_record('file_managed', $file);
  }
    // Old nid
    $ewrapper->field_xchange_doc_nid->set($node_info->nid);
    // File attachment field
    $ewrapper->field_document->set(array(
    'fid' => $file->fid,
    'display' => 1,
    'description' => $node_info->description,
  ));
  // Document version
  $ewrapper->field_document_version->set($node_info->field_document_version_value);
  // Document hide from publisher
  $ewrapper->field_hide_from_publishers->set($node_info->field_hide_from_publishers_value);
  // Document date
  $ewrapper->field_document_date->set($node_info->field_document_date_value);
  // Document file format
  if(isset($node_info->field_file_format_value)) {
    $ewrapper->field_file_format[0]->set($node_info->field_file_format_value);
  }
  // set all taxonomies
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
  // Single
  if(isset($node_info->cpe_kit_audience)) {
    $ewrapper->field_cpe_kit_audience->set($node_info->cpe_kit_audience[0]);
  }
  if(isset($node_info->xcelerate_ii)) {
    $ewrapper->field_xcelerate_ii->set($node_info->xcelerate_ii);
  }
  // Single
  if(isset($node_info->broadsoft_document_category)) {
    $ewrapper->field_broadsoft_doc_category->set($node_info->broadsoft_document_category[0]);
  }
  if(isset($node_info->platform)) {
    $ewrapper->field_platform->set($node_info->platform);
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

  $ewrapper->save();

  // add file usage
  $type = 'node';
  $id = $entity->nid;
  file_usage_add($file, 'file', $type, $id);

  return $entity;
}

function load_taxonomies($node_info) {
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
        case XC_TAXO_CPE_KIT:
          $query->condition("b.bundle", 'cpe_kit_audience');
          break;
        case XC_TAXO_XCELERATE:
          $query->condition("b.bundle", 'xcelerate_ii');
          break;
        case XC_TAXO_BS_DOC_CATEGORY:
          $query->condition("b.bundle", 'broadsoft_document_category');
          break;
        case XC_TAXO_PLATFORM:
          $query->condition("b.bundle", 'platform');
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
  return $additional_info;
}