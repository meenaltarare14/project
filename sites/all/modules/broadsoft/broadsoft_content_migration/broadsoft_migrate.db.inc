<?php

function broadsoft_migrate_broadworks_patch_build_query(&$query) {
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
}

function broadsoft_migrate_broadworks_patch_build_result(&$result) {
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
}