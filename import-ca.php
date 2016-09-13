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
  'field_can_access_ticketing_value',
  'field_cid_value',
  'field_account_status_value',
  'field_xchange_access_level_value',
  'field_sfdc_id_value',
  'field_email_domains_value',
  'field_jira_account_status_value',
  'field_region_value',
  'field_team_value',
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
// field_indirect_customers @todo for this we have rerun the script because old nid has new nid as reference
$query->leftJoin("content_type_customer_account", "a", "a.nid = n.nid AND a.vid = n.vid");
$query->condition("n.status", 1);
$query->condition("n.type", 'customer_account');
//$query->condition("n.nid", 440685);
$query->orderBy("nr.nid", "ASC");
//$query->orderBy("nr.vid", "DESC");
//$query->range(0, 3);
//dpq($query);

//$result = $query->countQuery()->execute()->fetchField(); //->fetchAllAssoc('nid');
$result = $query->execute()->fetchAllAssoc('nid');
foreach ($result as $nid => $old_node) {
  $query = db_select("term_node", "tn");
  $query->fields('tn', array('tid'));
  $query->condition("tn.nid", $old_node->nid);
  $query->condition("tn.vid", $old_node->vid);
  $taxo_result = $query->execute()->fetchAllKeyed(0,0);
  if(!empty($taxo_result)) {
    $result[$nid]->aoi_access = $taxo_result;
  }

  $query = db_select("content_field_indirect_customers", "b");
  $query->fields('b', array('field_indirect_customers_nid'));
  $query->condition("b.nid", $old_node->nid);
  $query->condition("b.vid", $old_node->vid);
  $field_indirect_customers_result = $query->execute()->fetchAllKeyed(0,0);
  if(!empty($field_indirect_customers_result)) {
    $result[$nid]->content_field_indirect_customers = $field_indirect_customers_result;
  }

}

db_set_active('default');

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_cs_nid", "old_nid");
  $query->fields('old_nid', array('entity_id'));
  $query->condition("old_nid.field_xchange_cs_nid_value", $node_info->nid);
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
      // Fetch new tid values for aoi access
      if(isset($node_info->aoi_access)) {
        $new_aoi_access = array();
        foreach ($node_info->aoi_access as $tid) {
          $query = db_select("field_data_field_xchange_old_tid", "access_aoi");
          $query->fields('access_aoi', array('entity_id'));
          $query->condition("access_aoi.field_xchange_old_tid_value", $tid);
          $new_tid = $query->execute()->fetchField();
          if($new_tid) {
            $new_aoi_access[] = array('tid' => $new_tid);
          }
        }
        if(!empty($new_aoi_access)) {
          $additional_info['new_aoi_access'] = $new_aoi_access;
        }
      }
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array) $node_info, $additional_info);
      $new_node = broadworks_create_ca_node($node_info);
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
  else {
//    var_dump($_GET);
    if(isset($_GET['update']) && $_GET['update'] == 1) {
      $additional_info['new_nid'] = $old_node;
      // Fetch new tid values for aoi access
      if(isset($node_info->content_field_indirect_customers)) {
        $new_content_field_indirect_customers = $additional_info['new_content_field_indirect_customers'] = array();
        foreach ($node_info->content_field_indirect_customers as $nid) {
          $query = db_select("field_data_field_xchange_cs_nid", "old_nid");
          $query->fields('old_nid', array('entity_id'));
          $query->condition("old_nid.field_xchange_cs_nid_value", $nid);
          $new_nid = $query->execute()->fetchField();
          if($new_nid) {
            $new_content_field_indirect_customers[] = array($new_nid);
          }
        }
        if(!empty($new_content_field_indirect_customers)) {
          $additional_info['new_content_field_indirect_customers'] = $new_content_field_indirect_customers;
        }
      }

      $node_info = (object) array_merge((array) $node_info, $additional_info);
//      var_dump($node_info);
      $updated_node = broadworks_create_ca_node($node_info, TRUE);
      echo "Node UPDATED with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
    else {
      echo "Node already created with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }

  }
}

function broadworks_create_ca_node($node_info = '', $update = FALSE) {
  global $user;

  if($update) {
    $entity = node_load($node_info->new_nid);
    $ewrapper = entity_metadata_wrapper('node', $entity);
    if(isset($node_info->new_content_field_indirect_customers) && !empty($node_info->new_content_field_indirect_customers)) {
      $ewrapper->field_indirect_customers->set($node_info->new_content_field_indirect_customers);
    }
    else {
      $ewrapper->field_indirect_customers = array();
    }
  }
  else {
    $values = array(
      'type' => 'customer_account',
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

    $ewrapper->field_can_access_ticketing->set($node_info->field_can_access_ticketing_value);
    $ewrapper->field_cid->set($node_info->field_cid_value);
    $ewrapper->field_account_status->set($node_info->field_account_status_value);
    $ewrapper->field_xchange_access_level->set($node_info->field_xchange_access_level_value);
    $ewrapper->field_sfdc_id->set($node_info->field_sfdc_id_value);
    $ewrapper->field_email_domains->set($node_info->field_email_domains_value);
    $ewrapper->field_jira_account_status->set($node_info->field_jira_account_status_value);
    $ewrapper->field_region->set($node_info->field_region_value);
    $ewrapper->field_team->set($node_info->field_team_value);
    // Old nid
    $ewrapper->field_xchange_cs_nid->set($node_info->nid);

    if(isset($node_info->new_aoi_access)) {
      $ewrapper->field_aoi_access->set($node_info->new_aoi_access);
    }
  }
    $ewrapper->save();
    return $entity;
}