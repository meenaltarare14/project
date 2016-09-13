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
  'field_update_binary_value',
  'field_ev_ticket_id_value',
  'field_schema_change_value',
  'field_affects_portal_comp_value',
  'field_restart_required_value',
  'field_oci_schema_change_value',
  'field_solaris_sparc_patch_file_fid',
  'field_solaris_x86_patch_file_fid',
  'field_linux_rh_patch_file_fid',
  'field_system_critical_patch_value',
  'field_accounting_change_value',
  'field_localization_changes_value',
  'field_help_change_value',
  'field_oss_dtd_change_value',
  'field_cap_change_value',
  'field_sip_change_value',
  'field_mgcp_change_value',
  'field_disposition_value',
  'field_test_instructions_value',
  'field_localization_details_value',
  'field_ev_ticket_desc_value',
  'field_solaris_sparc_patch_md5sum_value',
  'field_linux_rh_patch_md5sum_value',
  'field_solaris_x86_patch_md5sum_value',
  'field_config_schema_change_value',
  'field_snmp_restart_required_value',
  'field_minimal_tool_version_value',
  'field_target_application_value',
  'field_post_install_message_value',
  'field_pre_install_message_value',
  'field_upgrade_affecting_value',
));
$query->fields('b', array(
  'field_posted_date_value',
));
$query->fields('solaris_sparc_patch_file', array(
  'fid',
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status'
));
$query->fields('solaris_x86_patch_file', array(
  'fid',
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status'
));
$query->fields('linux_rh_patch_file', array(
  'fid',
  'filename',
  'filepath',
  'filemime',
  'filesize',
  'status'
));
$query->innerJoin("node_revisions", "nr", "nr.nid = n.nid AND nr.vid = n.vid");
$query->leftJoin("content_type_broadworks_patch", "a", "a.nid = n.nid AND a.vid = n.vid");
$query->leftJoin("content_field_posted_date", "b", "b.nid = n.nid AND b.vid = n.vid");
$query->leftJoin("files", "solaris_sparc_patch_file", "a.field_solaris_sparc_patch_file_fid = solaris_sparc_patch_file.fid");
$query->leftJoin("files", "solaris_x86_patch_file", "a.field_solaris_x86_patch_file_fid = solaris_x86_patch_file.fid");
$query->leftJoin("files", "linux_rh_patch_file", "a.field_linux_rh_patch_file_fid = linux_rh_patch_file.fid");
$query->condition("n.status", 1);
$query->condition("n.type", 'broadworks_patch');
//$query->condition("n.nid", 457332);
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
  $taxo_result = $query->execute()->fetchAllKeyed(0, 1);
//  var_dump($taxo_result);
  if (!empty($taxo_result)) {
    $result[$nid]->categories = $taxo_result;
  }

  // content_field_dependencies
  // content_field_inter_server_dependencies
  $query = db_select("content_field_dependencies", "a");
  $query->fields('a', array('field_dependencies_nid'));
  $query->isNotNull('field_dependencies_nid');
  $query->condition("a.nid", $old_node->nid);
  $query->condition("a.vid", $old_node->vid);
  $dependencies_result = $query->execute()->fetchAllKeyed(0, 0);
  //  var_dump($taxo_result);
  if (!empty($dependencies_result)) {
    $result[$nid]->field_dependencies = $dependencies_result;
  }

  // content_field_inter_server_dependencies
  $query = db_select("content_field_inter_server_dependencies", "a");
  $query->fields('a', array('field_inter_server_dependencies_nid'));
  $query->isNotNull('field_inter_server_dependencies_nid');
  $query->condition("a.nid", $old_node->nid);
  $query->condition("a.vid", $old_node->vid);
  $inter_server_dependencies_result = $query->execute()->fetchAllKeyed(0, 0);
  //  var_dump($taxo_result);
  if (!empty($inter_server_dependencies_result)) {
    $result[$nid]->field_inter_server_dependencies = $inter_server_dependencies_result;
  }
}

db_set_active('default');
//var_dump($result);

foreach ($result as $nid => $node_info) {
  // Check if we already imported old nid.
  $query = db_select("field_data_field_xchange_nid", "old_nid");
  $query->fields('old_nid', array('entity_id'));
  $query->condition("old_nid.field_xchange_nid_value", $node_info->nid);
  $query->condition("old_nid.bundle", 'broadworks_patch');
  $old_node = $query->execute()->fetchField();
  if ($old_node == 0) {
    if ($node_info->uid == 1) {
      $user_id = 1;
    }
    else {
      // Get the latest uid corresponding to the old node author uid
      $query = db_select("field_data_field_xchange_uid", "xchange_uid");
      $query->fields('xchange_uid', array('entity_id'));
//      $query->innerJoin("field_data_field_xchange_source", "xchange_source", "xchange_source.entity_id = xchange_uid.entity_id");
      $query->condition("xchange_uid.field_xchange_uid_value", $node_info->uid);
//      $query->condition("xchange_source.field_xchange_source_value", $db);
      $user_id = $query->execute()->fetchField();
      // Special case for Broadsoft Software.
      if (empty($user_id)) {
        $user_id = 1;
      }
    }
    if (!empty($user_id)) {
      // Fetch new tid values for categories
      $additional_info = array();
      if (isset($node_info->categories)) {
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
          if ($new_tid) {
            $additional_info[$new_tid['bundle']][] = $new_tid['entity_id'];
          }
        }
      }
      $additional_info['new_uid'] = $user_id;
      $node_info = (object) array_merge((array) $node_info, $additional_info);
//      var_dump($node_info);
      $new_node = broadworks_create_bwp_node($node_info);
      echo "{$new_node->title} old nid {$node_info->nid} and new nid {$new_node->nid} <br>" . "\n";
    }
    else {
      echo "No user found with uid {$node_info->uid}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
  }
  else {
//    var_dump($_GET);
    if (isset($_GET['update']) && $_GET['update'] == 1) {
      $additional_info['new_nid'] = $old_node;
      $new_field_dependencies_nid = $new_inter_server_dependencies = '';
      // Fetch new nid for field_dependencies & field_inter_server_dependencies
      if (isset($node_info->field_dependencies)) {
        $additional_info['new_field_dependencies'] = array();
        if ($new_field_dependencies_nid = get_commons_node_id($node_info->field_dependencies)) {
          $additional_info['new_field_dependencies'] = $new_field_dependencies_nid;
        }
      }

      if (isset($node_info->field_inter_server_dependencies)) {
        $additional_info['new_field_inter_server_dependencies'] = array();
        if ($new_inter_server_dependencies = get_commons_node_id($node_info->field_dependencies)) {
          $additional_info['new_field_inter_server_dependencies'] = $new_inter_server_dependencies;
        }
      }

      $node_info = (object) array_merge((array) $node_info, $additional_info);
//      var_dump($node_info);
      $updated_node = broadworks_create_bwp_node($node_info, TRUE);
      echo "Node UPDATED with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }
    else {
      echo "Node already created with nid {$old_node}: OLD ID: {$node_info->nid} <br>" . "\n";
    }

  }
}
//
function broadworks_create_bwp_node($node_info = '', $update = FALSE) {
  global $user;

  if ($update) {
    $entity = node_load($node_info->new_nid);
    $ewrapper = entity_metadata_wrapper('node', $entity);

    // field_dependencies
    if (isset($node_info->new_field_dependencies) && !empty($node_info->new_field_dependencies)) {
      $ewrapper->field_dependencies->set($node_info->new_field_dependencies);
    }
    else {
      $ewrapper->field_dependencies = array();
    }

    // field_inter_server_dependencies
    if (isset($node_info->new_field_dependencies) && !empty($node_info->new_field_dependencies)) {
      $ewrapper->field_dependencies->set($node_info->new_field_dependencies);
    }
    else {
      $ewrapper->field_dependencies = array();
    }

  }
  else {
    $values = array(
      'type' => 'broadworks_patch',
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

    if (isset($node_info->broadworks_release)) {
      $ewrapper->field_broadworks_release->set($node_info->broadworks_release);
    }
    if (isset($node_info->broadworks_os)) {
      $ewrapper->field_broadworks_os->set($node_info->broadworks_os);
    }
    if (isset($node_info->aoi_access)) {
      $ewrapper->field_aoi_access->set($node_info->aoi_access);
    }
    if (isset($node_info->broadworks_component)) {
      $ewrapper->field_broadworks_component->set($node_info->broadworks_component);
    }

    $ewrapper->field_ev_ticket_id->set($node_info->field_ev_ticket_id_value);
    $ewrapper->field_ev_ticket_desc->set($node_info->field_ev_ticket_desc_value);
    $ewrapper->field_system_critical_patch->set($node_info->field_system_critical_patch_value);
    $ewrapper->field_schema_change->set($node_info->field_schema_change_value);
    $ewrapper->field_update_binary->set($node_info->field_update_binary_value);
    $ewrapper->field_affects_portal_comp->set($node_info->field_affects_portal_comp_value);
    $ewrapper->field_restart_required->set($node_info->field_restart_required_value);

    $ewrapper->field_oci_schema_change->set($node_info->field_oci_schema_change_value);
    $ewrapper->field_accounting_change->set($node_info->field_accounting_change_value);
    $ewrapper->field_localization_changes->set($node_info->field_localization_changes_value);
    $ewrapper->field_help_change->set($node_info->field_help_change_value);
    $ewrapper->field_oss_dtd_change->set($node_info->field_oss_dtd_change_value);
    $ewrapper->field_snmp_restart_required->set($node_info->field_snmp_restart_required_value);
    $ewrapper->field_config_schema_change->set($node_info->field_config_schema_change_value);

    $ewrapper->field_cap_change->set($node_info->field_cap_change_value);
    $ewrapper->field_sip_change->set($node_info->field_sip_change_value);
    $ewrapper->field_mgcp_change->set($node_info->field_mgcp_change_value);
    // field_dependencies will be added during update phase as this is an entity reference field it is referencing it itself.
    // Long Text Fields
    if (!empty($node_info->field_patched_files_value)) {
      $ewrapper->field_patched_files->set(
        array(
          'value' => $node_info->field_patched_files_value,
          'summary' => '',
          'format' => 'filtered_html',
        ));
    }
    $ewrapper->field_disposition->set($node_info->field_disposition_value);
    if (!empty($node_info->field_test_instructions_value)) {
      $ewrapper->field_test_instructions->set(
        array(
          'value' => $node_info->field_test_instructions_value,
          'summary' => '',
          'format' => 'filtered_html',
        ));
    }
    if (!empty($node_info->field_localization_details_value)) {
      $ewrapper->field_localization_details->set(
        array(
          'value' => $node_info->field_localization_details_value,
          'summary' => '',
          'format' => 'filtered_html',
        ));
    }
    // field_solaris_sparc_patch_file will be added at the end
    $ewrapper->field_solaris_sparc_patch_md5sum->set($node_info->field_solaris_sparc_patch_md5sum_value);
    // field_solaris_x86_patch_file will be added at the end
    $ewrapper->field_solaris_x86_patch_md5sum->set($node_info->field_solaris_x86_patch_md5sum_value);
    // field_linux_rh_patch_file  will be added at the end
    $ewrapper->field_linux_rh_patch_md5sum->set($node_info->field_linux_rh_patch_md5sum_value);
    $ewrapper->field_minimal_tool_version->set($node_info->field_minimal_tool_version_value);
    $ewrapper->field_target_application->set($node_info->field_target_application_value);
    if (!empty($node_info->field_post_install_message_value)) {
      $ewrapper->field_post_install_message->set(
        array(
          'value' => $node_info->field_post_install_message_value,
          'summary' => '',
          'format' => 'filtered_html',
        ));
    }
    $ewrapper->field_posted_date->set($node_info->field_posted_date_value);
//    if (!empty($node_info->field_posted_date_value)) {
//      $ewrapper->field_posted_date->set(
//        array(
//          'value' => $node_info->field_posted_date_value,
//          'timezone' => 'America/New_York',
//          'timezone_db' => 'UTC',
//          'date_type' => 'datestamp',
//        ));
//    }
    $ewrapper->field_pre_install_message->set($node_info->field_pre_install_message_value);
    // field_inter_server_dependencies will be added during update phase as this is an entity reference field it is referencing it itself.
    $ewrapper->field_upgrade_affecting->set($node_info->field_upgrade_affecting_value);
    // Old nid
    $ewrapper->field_xchange_nid->set($node_info->nid);

    // Files Manupulation
    // field_solaris_sparc_patch_file
    if ($node_info->fid) {
      $solaris_sparc_patch = get_file_object(get_xchange_file_object($node_info, 'field_solaris_sparc_patch_file'), $node_info->new_uid);
      $ewrapper->field_solaris_sparc_patch_file->set(
        array(
          'fid' => $solaris_sparc_patch->fid,
          'display' => 1,
          'description' => '',
        )
      );
    }
    // field_solaris_x86_patch_file
    if ($node_info->solaris_x86_patch_file_fid) {
      $solaris_x86_patch = get_file_object(get_xchange_file_object($node_info, 'field_solaris_x86_patch_file'), $node_info->new_uid);
      $ewrapper->field_solaris_x86_patch_file->set(
        array(
          'fid' => $solaris_x86_patch->fid,
          'display' => 1,
          'description' => '',
        )
      );
    }
    // field_linux_rh_patch_file
    if ($node_info->linux_rh_patch_file_fid) {
      $linux_rh_patch = get_file_object(get_xchange_file_object($node_info, 'field_linux_rh_patch_file'), $node_info->new_uid);
      $ewrapper->field_linux_rh_patch_file->set(
        array(
          'fid' => $linux_rh_patch->fid,
          'display' => 1,
          'description' => '',
        )
      );
    }
  }
  $ewrapper->save();
  return $entity;
}

function get_xchange_file_object($nodeinfo, $field_name) {
  if ($field_name == 'field_solaris_sparc_patch_file') {
    $file = new stdClass();
    $file->filename = $nodeinfo->filename;
    $file->filepath = $nodeinfo->filepath;
    $file->filemime = $nodeinfo->filemime;
    $file->filesize = $nodeinfo->filesize;
    $file->status = $nodeinfo->status;
  }

  if ($field_name == 'field_solaris_x86_patch_file') {
    $file = new stdClass();
    $file->filename = $nodeinfo->solaris_x86_patch_file_filename;
    $file->filepath = $nodeinfo->solaris_x86_patch_file_filepath;
    $file->filemime = $nodeinfo->solaris_x86_patch_file_filemime;
    $file->filesize = $nodeinfo->solaris_x86_patch_file_filesize;
    $file->status = $nodeinfo->solaris_x86_patch_file_status;
  }

  if ($field_name == 'field_linux_rh_patch_file') {
    $file = new stdClass();
    $file->filename = $nodeinfo->linux_rh_patch_file_filename;
    $file->filepath = $nodeinfo->linux_rh_patch_file_filepath;
    $file->filemime = $nodeinfo->linux_rh_patch_file_filemime;
    $file->filesize = $nodeinfo->linux_rh_patch_file_filesize;
    $file->status = $nodeinfo->linux_rh_patch_file_status;
  }

  return $file;
}

function get_file_object($xchange_file_info = '', $user_id) {
  $file_object = FALSE;
  if (!empty($xchange_file_info)) {
    $file_url = str_replace('/bw/broadworks/xchangeRepos', 's3://file-storage-100/xchange', $xchange_file_info->filepath);
    // existing file check
    $query = db_select('file_managed', 'f');
    $query->fields('f', array('fid'));
    $query->condition('uri', $file_url, '=');
    $file_id = $query->execute()->fetchField();
    if ($file_id) {
      $file_object = file_load($file_id);
    }
    else {
      $file_object = new stdClass;
      $file_object->uid = $user_id;
      $file_object->uri = $file_url;
      $file_object->filename = drupal_basename($file_url);;
      $file_object->filemime = file_get_mimetype($file_object->filename);
      $file_object->type = file_get_mimetype($file_object->filename);
      $file_object->filesize = $xchange_file_info->filesize;
      $file_object->status = $xchange_file_info->status;
      $file_object->timestamp = REQUEST_TIME;
      drupal_write_record('file_managed', $file_object);
      $file_object->display = 1;
    }
  }
  return $file_object;
}

function get_commons_node_id($old_nid) {
  if(is_array($old_nid)) {
    $query = db_select("field_data_field_xchange_nid", "old_nid");
    $query->fields('old_nid', array('entity_id'));
    $query->condition("old_nid.field_xchange_nid_value", $old_nid, "IN");
    $query->condition("old_nid.bundle", 'broadworks_patch');
    $new_nid = $query->execute()->fetchAllKeyed(0, 0);
    if ($new_nid) {
      return $new_nid;
    }
  }
  else {
    return FALSE;
  }
}