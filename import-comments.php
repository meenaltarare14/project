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

$query = db_query("SELECT c.* FROM comments c
INNER JOIN node n ON n.nid=c.nid
WHERE n.type = 'forum'
AND n.`status`=1
ORDER by c.nid ASC");
//$query->range(0, 10);
$result = $query->fetchAllAssoc('cid');
db_set_active('default');

foreach ($result as $nid => $comment_info) {
  $query = db_select("field_data_field_xchange_cmt_cid", "xchange_cmt_cid");
  $query->fields('xchange_cmt_cid', array('entity_id'));
  $query->innerJoin("field_data_field_xchange_cmt_source", "xchange_cmt_source", "xchange_cmt_cid.entity_id = xchange_cmt_source.entity_id");
  $query->condition("xchange_cmt_cid.field_xchange_cmt_cid_value", $comment_info->cid);
  $old_comment = $query->execute()->rowCount();
  if ($old_comment == 0) {
    $additional_info['db_source'] = $db;
    // If PID is present, find corresponding cid from the broadsoft database
    if($comment_info->pid) {
      $query = db_select("field_data_field_xchange_cmt_cid", "xchange_cmt_cid");
      $query->fields('xchange_cmt_cid', array('entity_id'));
      $query->innerJoin("field_data_field_xchange_cmt_source", "xchange_cmt_source", "xchange_cmt_cid.entity_id = xchange_cmt_source.entity_id");
      $query->condition("xchange_cmt_cid.field_xchange_cmt_cid_value", $comment_info->pid);
      $new_pid = $query->execute()->fetchField();

      if($new_pid) {
        $additional_info['pid'] = $new_pid;
      }
      else {
        $additional_info['pid'] = '0';
      }
    }

    // Find node associated with old comment
    $query = db_select("field_data_field_xchange_forum_nid", "xchange_forum_nid");
    $query->fields('xchange_forum_nid', array('entity_id'));
    $query->innerJoin("field_data_field_xchange_forum_source", "xchange_forum_source", "xchange_forum_nid.entity_id = xchange_forum_source.entity_id");
    $query->condition("xchange_forum_nid.field_xchange_forum_nid_value", $comment_info->nid);
    $query->condition("xchange_forum_source.field_xchange_forum_source_value", $db);
    $new_node_id = $query->execute()->fetchField();

    // Find user associated with old comment
    $query = db_select("field_data_field_xchange_uid", "xchange_uid");
    $query->fields('xchange_uid', array('entity_id'));
    $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
    $query->condition("xchange_uid.field_xchange_uid_value", $comment_info->uid);
    $query->condition("xchange_source.field_xchange_source_value", $db);
    $new_user_id = $query->execute()->fetchField();

    if(!empty($new_node_id) && !empty($new_user_id)) {
      $additional_info['uid'] = $new_user_id;
      $additional_info['nid'] = $new_node_id;
      // We are creating new comment anyways
      $comment_info = (object) array_merge((array)$comment_info, $additional_info);
      $new_comment = broadworks_create_comment($comment_info);
      echo "{$new_comment->subject} old cid {$comment_info->cid} and new cid {$new_comment->cid} <br>" . "\n";
    }
    else {
      echo "No user found with UID: {$comment_info->uid}: OLD comment CID: {$comment_info->cid}: OLD comment NID: {$comment_info->nid} <br>" . "\n";
    }
  }
}

function broadworks_create_comment($comment_info = '', $save = TRUE) {
  global $user;
  $values = array(
    'node_type' => 'comment_node_forum',
    'status' => 1,
    'uid' => $comment_info->uid,
    'pid' => $comment_info->pid,
    'nid' => $comment_info->nid,
    'created' => $comment_info->timestamp,
    'updated' => $comment_info->timestamp,
  );
  $entity = entity_create('comment', $values);

  $ewrapper = entity_metadata_wrapper('comment', $entity);
//  $ewrapper->language->set('en');

  $ewrapper->subject->set($comment_info->subject);
  $ewrapper->comment_body->set(
    array(
      'value' => $comment_info->comment,
      'summary' => '',
      'format' => 'filtered_html',
    ));

  $ewrapper->field_xchange_cmt_source->set($comment_info->db_source);
  $ewrapper->field_xchange_cmt_cid->set($comment_info->cid);

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