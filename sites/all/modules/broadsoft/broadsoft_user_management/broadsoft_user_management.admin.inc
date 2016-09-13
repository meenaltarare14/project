<?php 

define("USER_MANAGEMENT_NODE_DEFAULT_BODY_TEST", "This page gives you access to various User Management Actions. For details and help, please consult <a href=\"https://home.unite.broadsoft.com/display/IS/Extranet+User+Management\">the user management help page</a>");

/** =============================================================================================
*/
function broadsoft_user_management_enableUserManagementGUI() {
  // make sure we are under a Taxonomy-Access-Control site, otherwise abort (could code it for other cases)
  $vocabObj = taxonomy_vocabulary_machine_name_load(USER_MANAGEMENT_ACCESS_TERM_VOCABULARY);
  if(!$vocabObj) {
    watchdog('BroadSoft Code', 'Unable to enable User Management - current site is not under Taxonomy Access Control', NULL, WATCHDOG_ERROR);
    drupal_set_message('Unable to enable User Management - current site is not under Taxonomy Access Control', 'error');
    return;
  }    
  $terms = taxonomy_get_term_by_name(USER_MANAGEMENT_ACCESS_TERM_NAME, USER_MANAGEMENT_ACCESS_TERM_VOCABULARY);
  if(!$terms) {
    watchdog('BroadSoft Code', 'Creating term ('.USER_MANAGEMENT_ACCESS_TERM_NAME.') under taxonomy ('.USER_MANAGEMENT_ACCESS_TERM_VOCABULARY.')', NULL, WATCHDOG_NOTICE);
    $new_term = array(
        'vid' => $vocabObj->vid,
        'name' => USER_MANAGEMENT_ACCESS_TERM_NAME,
    );
    $new_term = (object) $new_term;
    taxonomy_term_save($new_term);
    $terms = taxonomy_get_term_by_name(USER_MANAGEMENT_ACCESS_TERM_NAME, USER_MANAGEMENT_ACCESS_TERM_VOCABULARY);
    watchdog('BroadSoft Code', 'You MUST adjust the Taxonomy Access Control settings for term ('.USER_MANAGEMENT_ACCESS_TERM_NAME.')', NULL, WATCHDOG_WARNING);
    drupal_set_message('You MUST adjust the Taxonomy Access Control settings for term "'.USER_MANAGEMENT_ACCESS_TERM_NAME.'". <br><br><b>STEP 1</b>: (from the admin menu) Configuration -> Taxonomy access control -> "enable access control" for role "User Manager" -> <b>Add/Allow</b> term "user management" for "User Manager". <br><br><b>STEP 2:</b> (from the admin menu) Configuration -> Taxonomy access control -> "edit access rules" for role "authenticated user" -> <b>Add/Deny</b> View for term "user management" for "authenticated user"
    ', 'warning');
  }
     
  watchdog('BroadSoft Code', 'Enabling User Management GUI', NULL, WATCHDOG_NOTICE);

  global $user;

  $node = new stdClass();

  $node->type = 'page';

  $node->title = 'BroadSoft LDAP User Management';
  $node->language = 'und';
  $node->uid = $user->uid;
  $node->path['alias'] = 'BroadSoftUserManagement';
  $node->comment = 0;
  $node->status = 1;        // 1 means published
  $node->promote = 0;
  $node->revision = 0;
  $node->changed = $_SERVER['REQUEST_TIME'];
  $node->created = $_SERVER['REQUEST_TIME'];
  
  $term_keys = array_keys($terms);
  $node->field_access_terms[$node->language][]['tid'] = $term_keys[0];

  node_object_prepare($node);

  $node->menu['enabled'] = 1;       
  $node->menu['link_title'] = 'Manage Users';
  $node->menu['description'] = 'BroadSoft User Management';
  $node->menu['parent'] = 'main-menu';
  $node->menu['weight'] = 5;
  $node->body[$node->language][0]['value'] = "<? print '".USER_MANAGEMENT_NODE_DEFAULT_BODY_TEST."'; print bum_handle_LDAP_GUI(); ?>";
  $node->body[$node->language][0]['format'] = 'php_code';
  
  $node = node_submit($node);  
  node_save($node); 
  
  variable_set('broadsoft_user_management_User_Management_Page_NID', $node->nid);  
  watchdog('BroadSoft Code', 'Created node ('.variable_get('broadsoft_user_management_User_Management_Page_NID').') for User Management', NULL, WATCHDOG_NOTICE);
  
  // create role and permission
	// create any roles we will be using
	$permissions = array(USER_MANAGEMENT_PERMISSION_NAME);

  $role = bum_helper_get_role(XCHANGE_USER_MANAGER_ROLE_NAME);
	user_role_grant_permissions($role->rid, $permissions);

  $role = bum_helper_get_role(EXTRANET_GROUP_MANAGER_ROLE_NAME);
	user_role_grant_permissions($role->rid, $permissions);

  $role = bum_helper_get_role(ADMINISTRATOR_ROLE_NAME);
	user_role_grant_permissions($role->rid, $permissions);

  $role = bum_helper_get_role(POWER_USER_ROLE_NAME);
	user_role_grant_permissions($role->rid, $permissions);
}

/** =============================================================================================
*/
function broadsoft_user_management_disableUserManagementGUI() {
  $nid = variable_get('broadsoft_user_management_User_Management_Page_NID');
  if($nid) {
    watchdog('BroadSoft Code', 'Disabling User Management GUI - deleting node ('.$nid.')', NULL, WATCHDOG_NOTICE);
    node_delete($nid); // also takes care of the menu item
    variable_del('broadsoft_user_management_User_Management_Page_NID');
  } else {
    watchdog('BroadSoft Code', 'Disabling User Management GUI - cannot find User Management page to be deleted?', NULL, WATCHDOG_ERROR);
  }
  user_role_delete(USER_MANAGER_ROLE_NAME);  
  
  $terms = taxonomy_get_term_by_name(USER_MANAGEMENT_ACCESS_TERM_NAME, USER_MANAGEMENT_ACCESS_TERM_VOCABULARY);
  
  if ($terms) {
    foreach (array_keys($terms) as $tid) {
      taxonomy_term_delete($tid);
    }
  }  
}

/** =============================================================================================
*/
function broadsoft_user_management_admin_config_form_submit($form, &$form_state) {
/* interesting to look at form arrays
  drupal_set_message("I am inside submit handler. Printed form values below");
  drupal_set_message("<pre>" . print_r($form_state['values'], 1) . "</pre>");
  drupal_set_message("Printing Form Structure Below !");
  drupal_set_message("<pre>" . print_r($form, 1) . "</pre>");  
  */
  if(variable_get('broadsoft_user_management_Enable_User_Management', BROADSOFT_USER_MANAGEMENT_ENABLE_USER_MANAGEMENT) != $form_state['values']['broadsoft_user_management_Enable_User_Management']) {
    if($form_state['values']['broadsoft_user_management_Enable_User_Management']) 
      broadsoft_user_management_enableUserManagementGUI();      
    else
      broadsoft_user_management_disableUserManagementGUI();
  }
}


/** =============================================================================================
*/
function broadsoft_user_management_admin_config_form($form, &$form_state) {
  $form = array();
  $form['broadsoft_user_management_LDAP_Server_Address'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Primary Server Address'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Server_Address', BROADSOFT_USER_MANAGEMENT_LDAP_SERVER_ADDRESS),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );

  $form['broadsoft_user_management_LDAP_Secondary_Server_Address'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Secondary Server Address'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Secondary_Server_Address', BROADSOFT_USER_MANAGEMENT_LDAP_SECONDARY_SERVER_ADDRESS),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => false,
  );

  $form['broadsoft_user_management_LDAP_Server_Port'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Server Port'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Server_Port', BROADSOFT_USER_MANAGEMENT_LDAP_SERVER_PORT),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  
  $form['broadsoft_user_management_LDAP_Base_Bind_DN'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Base Bind DN'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Base_Bind_DN', BROADSOFT_USER_MANAGEMENT_LDAP_BASE_BIND_DN),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );

  $form['broadsoft_user_management_LDAP_Base_DN'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Base DN'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Base_DN', BROADSOFT_USER_MANAGEMENT_LDAP_BASE_DN),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );

  $form['broadsoft_user_management_LDAP_Auth_Bind_DN'] = array(
    '#type' => 'textfield',
    '#title' => t('LDAP Auth Bind DN'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Auth_Bind_DN', BROADSOFT_USER_MANAGEMENT_LDAP_AUTH_BIND_DN),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );

  $form['broadsoft_user_management_LDAP_Auth_Bind_Password'] = array(
    '#type' => 'password', // or keep plain text & visible 'textfield',
    '#title' => t('LDAP Auth Bind Password'),
    '#default_value' => variable_get('broadsoft_user_management_LDAP_Auth_Bind_Password', BROADSOFT_USER_MANAGEMENT_LDAP_AUTH_BIND_PASSWORD),
    '#size' => 40,
    '#maxlength' => 60,
    '#required' => TRUE,
  );
  
  $form['broadsoft_user_management_Enable_Username_Override'] = array(
      '#type' => 'checkbox',
      '#title' => t('Simple Drupal username'),
      '#description' => t('Override the Drupal Username with the the email prefix instead of the full email'),
      '#default_value' => variable_get('broadsoft_user_management_Enable_Username_Override', BROADSOFT_USER_MANAGEMENT_ENABLE_USERNAME_OVERRIDE),
      '#required' => FALSE,
  );
  
    
  $form['broadsoft_user_management_Enable_User_Management'] = array(
    '#type' => 'checkbox',
    '#title' => t('Enable BroadSoft LDAP User Management (BUMS)'),
    '#description' => t('This will enable the BroadSoft LDAP User Management page and menu item'),
    '#default_value' => variable_get('broadsoft_user_management_Enable_User_Management', BROADSOFT_USER_MANAGEMENT_ENABLE_USER_MANAGEMENT),
    '#required' => FALSE,
  );  
  
  $form['#submit'][] = 'broadsoft_user_management_admin_config_form_submit';  

  return system_settings_form($form);
}

?>