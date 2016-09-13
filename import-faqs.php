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

//db_set_active('pbx');

db_set_active($db);

$query = db_select("node", "n");
//$query->distinct();
$query->fields('n', array('nid', 'uid', 'title', 'created', 'changed'));
$query->fields('nr', array('body', 'vid'));
//$query->fields('td', array('tid', 'vid', 'name'));
$query->fields('fq', array('question', 'detailed_question'));
$query->fields('ctf', array(
  'field_expiration_weeks_value',
  'field_last_validation_timestamp_value',
  'field_last_validator_uid_uid'
));
$query->fields('f', array(
  'fid',
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status',
  'timestamp'
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
$query->innerJoin("faq_questions", "fq", "fq.nid = n.nid AND fq.vid = n.vid");
$query->innerJoin("content_type_faq", "ctf", "ctf.nid = n.nid AND ctf.vid = n.vid");
$query->leftJoin("upload", "up", "up.nid = n.nid");
$query->leftJoin("files", "f", "f.fid = up.fid");
//$query->leftJoin("term_node", "tn", "tn.nid = n.nid AND tn.vid = n.vid");
//$query->leftJoin("term_data", "td", "td.tid = tn.tid");
$query->condition("n.status", 1);
$query->condition("n.type", 'faq');
//$query->condition("n.nid", 440685);
$query->orderBy("nr.nid", "ASC");
//$query->orderBy("nr.vid", "DESC");
//$query->range(0,50);
//dpq($query);
//$result = $query->countQuery()->execute()->fetchField(); //->fetchAllAssoc('nid');
$result = $query->execute()->fetchAllAssoc('nid');
//var_dump($result);
db_set_active('default');

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_faq_nid", "faq_nid");
  $query->fields('faq_nid', array('entity_id'));
  $query->innerJoin("field_data_field_xchange_faq_source", "faq_source", "faq_nid.entity_id = faq_source.entity_id");
  $query->condition("faq_nid.field_xchange_faq_nid_value", $node_info->nid);
  $old_node = $query->execute()->rowCount();
  if ($old_node == 0) {
    $node_info->db = $db;
//    $additional_info['topic_tid'] = BROADWORKS;
    $additional_info['db_source'] = $db;

    if ($node_info->uid == 1) {
      $user_id = 1;
    }
    else {
      $query = db_select("field_data_field_xchange_uid", "xchange_uid");
      $query->fields('xchange_uid', array('entity_id'));
      $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
      $query->condition("xchange_uid.field_xchange_uid_value", $node_info->uid);
      $query->condition("xchange_source.field_xchange_source_value", $db);
      $user_id = $query->execute()->fetchField();
    }

    $additional_info['validator_new_uid'] = '';
    // Old validator UID for xchange
    if ($db == 'xchange') {
      if ($node_info->field_last_validator_uid_uid == 1) {
        $additional_info['validator_new_uid'] = 1;
      }
      else {
        $query = db_select("field_data_field_xchange_uid", "xchange_uid");
        $query->fields('xchange_uid', array('entity_id'));
        $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
        $query->condition("xchange_uid.field_xchange_uid_value", $node_info->field_last_validator_uid_uid);
        $query->condition("xchange_source.field_xchange_source_value", $db);
        $validator_user_id = $query->execute()->fetchField();
        if (!empty($validator_user_id)) {
          $additional_info['validator_new_uid'] = $validator_user_id;
        }
      }

    }
    if (!empty($user_id)) {
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array) $node_info, $additional_info);
      $new_node = broadworks_create_faq_node($node_info);
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD FAQ ID: {$node_info->nid} <br>" . "\n";
    }
  }
}

function broadworks_create_faq_node($node_info = '', $save = TRUE) {
  global $user;

  $values = array(
    'type' => 'faq',
    'uid' => $node_info->new_uid,
    'status' => 1,
    'comment' => 2,
    'promote' => 0,
    'created' => $node_info->created,
    'updated' => $node_info->changed,
  );
  $entity = entity_create('node', $values);

  $ewrapper = entity_metadata_wrapper('node', $entity);
//  $ewrapper->language->set('en');

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

  if (!empty($node_info->detailed_question)) {
    $ewrapper->field_detailed_question->set(
      array(
        'value' => $node_info->detailed_question,
        'summary' => '',
        'format' => 'filtered_html',
      ));

  }

  $ewrapper->field_expiration_weeks->set($node_info->field_expiration_weeks_value);
  $ewrapper->field_last_validation_timestamp->set($node_info->field_last_validation_timestamp_value);
  if(!empty($node_info->validator_new_uid)) {
    $ewrapper->field_last_validator_uid->set($node_info->validator_new_uid);
  }
  $ewrapper->field_xchange_faq_source->set($node_info->db_source);
  $ewrapper->field_xchange_faq_nid->set($node_info->nid);

  // Now just save the wrapper and the entity
  // There is some suggestion that the 'true' argument is necessary to
  // the entity save method to circumvent a bug in Entity API. If there is
  // such a bug, it almost certainly will get fixed, so make sure to check.
  if ($save) {
    $ewrapper->save();
    return $entity;
  }
  else {
    return $ewrapper;
  }
}