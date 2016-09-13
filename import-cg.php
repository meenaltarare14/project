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
$query->fields('a', array(
  'field_customer_account_nid',
  'field_product_category_value',
  'field_target_client_options_value',
  'field_in_country_support_options_value',
  'field_notification_list_value',
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
// field_indirect_customers @todo for this we have rerun the script because old nid has new nid as reference
$query->leftJoin("content_type_customer_group", "a", "a.nid = n.nid AND a.vid = n.vid");
$query->condition("n.status", 1);
$query->condition("n.type", 'customer_group');
//$query->condition("n.nid", 440685);
$query->orderBy("nr.nid", "ASC");
//$query->orderBy("nr.vid", "DESC");
//$query->range(0, 1);
//dpq($query);

//$result = $query->countQuery()->execute()->fetchField(); //->fetchAllAssoc('nid');
$result = $query->execute()->fetchAllAssoc('nid');
//var_dump($result);
db_set_active('default');

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_cg_nid", "old_nid");
  $query->fields('old_nid', array('entity_id'));
  $query->condition("old_nid.field_xchange_cg_nid_value", $node_info->nid);
  $old_node = $query->execute()->fetchField();
  if ($old_node == 0) {
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
    if (!empty($user_id)) {
      // Fetch new node Customer Account: nid
      if(isset($node_info->field_customer_account_nid)) {
        $customer_account_nid = '';
        $query = db_select("field_data_field_xchange_cs_nid", "old_nid");
        $query->fields('old_nid', array('entity_id'));
        $query->condition("old_nid.field_xchange_cs_nid_value", $node_info->field_customer_account_nid);
        $customer_account_nid = $query->execute()->fetchField();

        if(!empty($customer_account_nid)) {
          $additional_info['new_customer_account_nid'] = $customer_account_nid;
        }
      }
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array) $node_info, $additional_info);
//      var_dump($node_info);
      $new_node = broadworks_create_cg_node($node_info);
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
  else {
    echo "Node already created with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
  }
}

function broadworks_create_cg_node($node_info = '', $save = TRUE) {
  global $user;

  $values = array(
    'type' => 'customer_group',
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


  $ewrapper->field_product_category->set($node_info->field_product_category_value);
  $ewrapper->field_target_client_options->set($node_info->field_target_client_options_value);
  $ewrapper->field_in_country_support_options->set($node_info->field_in_country_support_options_value);
  $ewrapper->field_notification_list->set($node_info->field_notification_list_value);
  // Old nid
  $ewrapper->field_xchange_cg_nid->set($node_info->nid);

  if(isset($node_info->new_customer_account_nid) && !empty($node_info->new_customer_account_nid)) {
    $ewrapper->field_customer_account->set($node_info->new_customer_account_nid);
  }
//  var_dump($node_info);
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

function _wrapper_debug($w) {
  $values = array();
  foreach ($w->getPropertyInfo() as $key => $val) {
    $values[$key] = $w->$key->value();
  }
  return $values;
}