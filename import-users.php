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

//$query = db_select("users", "u");
//$query->fields('u', array('uid', 'name', 'mail', ));
//$query->fields('up', array('field_interests_value','field_company_value','field_favorite_sites_value','field_location_value','field_full_name_value','field_is_partner_representative_value','field_professional_bio_value'));
//$query->innerJoin("node", "n", 'n.uid = u.uid AND n.type = :type', array(':type' => 'user_profile'));
//$query->innerJoin("content_type_user_profile", "up", "up.nid = n.nid");
//$query->condition("u.status", 1);
//$query->condition("u.uid", array(1), "NOT IN");
//$query->orderBy("u.created", "ASC");
//$query->range(0, 1);
//$result = $query->execute()->fetchAllAssoc('nid');

$query = db_query("SELECT
	u.*,up.*,GROUP_CONCAT(r.name SEPARATOR  ',') roles
FROM
	users u
INNER JOIN users_roles ur ON ur.uid = u.uid
INNER JOIN role r ON r.rid = ur.rid
INNER JOIN node n ON n.uid = u.uid
AND n.type = 'user_profile'
INNER JOIN content_type_user_profile up ON up.nid = n.nid
WHERE
	u. STATUS = 1
AND u.uid NOT IN(1)
GROUP BY ur.uid
ORDER BY
	u.created ASC");

$result = $query->fetchAllAssoc('uid');

//var_dump($result);

db_set_active('default');

foreach ($result as $nid => $node_info) {
  $query = db_select("field_data_field_xchange_uid", "xchange_uid");
  $query->fields('xchange_uid', array('entity_id'));
  $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
  $query->condition("xchange_uid.field_xchange_uid_value", $node_info->uid);
  $query->condition("xchange_source.field_xchange_source_value", $db);
  $old_user = $query->execute()->rowCount();
  if($old_user != 0) {
    continue;
  }
  $additional_info['db_source'] = $db;
  $additional_info['role_ids'] = get_all_roles($node_info->roles);
  $node_info = (object) array_merge((array)$node_info, $additional_info);
  $new_user = broadworks_create_user($node_info);
  var_dump("{$new_user->name} old uid {$node_info->uid} and new uid {$new_user->uid}");
}

function broadworks_create_user($user_info = '', $save = TRUE) {
  global $user;

  $values = array(
    'name' => $user_info->name,
    'mail' => $user_info->mail,
    'pass' => user_password(5),
    'status' => 1,
    'roles' => $user_info->role_ids,
  );

  $entity = entity_create('user', $values);

  $ewrapper = entity_metadata_wrapper('user', $entity);


//  $ewrapper->body->set(
//    array(
//      'value' => $node_info->body,
//      'summary' => '',
//      'format' => 'filtered_html',
//    ));

  $ewrapper->field_xchange_uid->set($user_info->uid);
  $ewrapper->field_xchange_source->set($user_info->db_source);
  if(!empty($user_info->field_favorite_sites_value)) {
    $ewrapper->field_xchange_favorite_sites->set($user_info->field_favorite_sites_value);
  }

  if(!empty($user_info->field_interests_value)) {
    $ewrapper->field_xchange_interests->set($user_info->field_interests_value);
  }

  if(!empty($user_info->field_company_value)) {
    $ewrapper->field_xchange_company->set($user_info->field_company_value);
  }

  if(!empty($user_info->field_location_value)) {
    $ewrapper->field_xchange_location->set($user_info->field_location_value);
  }

  if(!empty($user_info->field_full_name_value)) {
    $fullname = $user_info->field_full_name_value;
    if(!empty($fullname)) {
      $ewrapper->field_xchange_full_name->set($user_info->field_full_name_value);
      $fullname = explode(" ", $fullname);
      $ewrapper->field_name_first->set($fullname[0]);
      if(isset($fullname[1])) {
        $ewrapper->field_name_last->set($fullname[1]);
      }
    }
  }


//  $ewrapper->field_bio->set($user_info->field_professional_bio_value);
  if(!empty($user_info->field_professional_bio_value)) {
    $ewrapper->field_bio->set($user_info->field_professional_bio_value);
  }

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

function get_all_roles($role_names) {
  $roles = explode(",", $role_names);
  $query = db_select('role', 'r');
  $query->fields('r', array('rid', 'rid'));
  $query->condition('r.name', $roles, "IN");
  $result = $query->execute()->fetchAllKeyed(0);
  return drupal_map_assoc($result + array(DRUPAL_AUTHENTICATED_RID));
}