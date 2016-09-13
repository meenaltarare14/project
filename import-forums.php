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
$query->fields('n', array('nid', 'uid', 'title', 'created'));
$query->fields('nr', array('body'));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid");
$query->condition("n.status", 1);
$query->condition("n.type", 'forum');
$query->orderBy("n.created", "ASC");
$result = $query->execute()->fetchAllAssoc('nid');

db_set_active('default');

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_forum_nid", "xchange_forum_nid");
  $query->fields('xchange_forum_nid', array('entity_id'));
  $query->innerJoin("field_data_field_xchange_forum_source", "xchange_forum_source", "xchange_forum_nid.entity_id = xchange_forum_source.entity_id");
  $query->condition("xchange_forum_nid.field_xchange_forum_nid_value", $node_info->nid);
  $old_node = $query->execute()->rowCount();
  if ($old_node == 0) {
    $node_info->db = $db;
    $additional_info['topic_tid'] = BROADWORKS;
    $additional_info['db_source'] = $db;

    $query = db_select("field_data_field_xchange_uid", "xchange_uid");
    $query->fields('xchange_uid', array('entity_id'));
    $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
    $query->condition("xchange_uid.field_xchange_uid_value", $node_info->uid);
    $query->condition("xchange_source.field_xchange_source_value", $db);
    $user_id = $query->execute()->fetchField();
    if(!empty($user_id)) {
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array)$node_info, $additional_info);
      $new_forum = broadworks_create_node('forum', $node_info);
      echo "{$new_forum->title} old nid {$node_info->nid} and new nid {$new_forum->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD FORUM ID: {$node_info->nid} <br>" . "\n";
    }
  }
}

function broadworks_create_node($type = 'forum', $node_info = '', $save = TRUE) {
  global $user;

  $values = array(
    'type' => $type,
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
  $ewrapper->body->set(
    array(
      'value' => $node_info->body,
      'summary' => '',
      'format' => 'filtered_html',
    ));

  $ewrapper->taxonomy_forums->set($node_info->topic_tid);
  $ewrapper->field_xchange_forum_source->set($node_info->db_source);
  $ewrapper->field_xchange_forum_nid->set($node_info->nid);

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