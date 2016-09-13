<?php

/** **********************************************************************************************
 *  ********** CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS CLASS ***********
 *  **********************************************************************************************
 */

class LDAPIntegration
{
  var $ldapConnection;
  var $ldapConfig; // array
  var $ldapIsConnectedAsAdmin;
  
  /* --- OLD (198.170.71.247) LDAP Server settings:
  ['server']        = '198.170.71.247';                               <-- CHANGED
  ['port']          = '389';                                          <-- same
  ['baseBindDN']    = ',ou=Astro,dc=ext,dc=broadsoft,dc=com';         <-- same
  ['baseDN']        = 'DC=ext,DC=broadsoft,DC=com';                   <-- same
  ['authBindDN']    = 'cn=Administrator,dc=ext,dc=broadsoft,dc=com';  <-- CHANGED
  ['authBindPass']  = see Xchangebones                                <-- CHANGED

  /* --- NEW (174.122.85.50) LDAP Server settings:
  ['server']        = '174.122.85.50';                                <-- CHANGED
  ['port']          = '389';                                          <-- same
  ['baseBindDN']    = ',ou=Astro,dc=ext,dc=broadsoft,dc=com';         <-- same
  ['baseDN']        = 'DC=ext,DC=broadsoft,DC=com';                   <-- same
  ['authBindDN']    = 'cn=manager,dc=broadsoft,dc=com';               <-- CHANGED
  ['authBindPass']  = same as bwadmin shell                           <-- CHANGED
  */  

  /** =============================================================================================
  */
  function LDAPIntegration() {
    $this->ldapConfig['baseBindDN']   	= variable_get('broadsoft_user_management_LDAP_Base_Bind_DN', BROADSOFT_USER_MANAGEMENT_LDAP_BASE_BIND_DN);
    $this->ldapConfig['baseBindDN']     = preg_replace('/^,/', '', $this->ldapConfig['baseBindDN']);    
    $this->ldapConfig['baseDN']					= variable_get('broadsoft_user_management_LDAP_Base_DN', BROADSOFT_USER_MANAGEMENT_LDAP_BASE_DN);
    $this->ldapConfig['authBindDN']     = variable_get('broadsoft_user_management_LDAP_Auth_Bind_DN', BROADSOFT_USER_MANAGEMENT_LDAP_AUTH_BIND_DN);
    $this->ldapConfig['authBindPass']   = variable_get('broadsoft_user_management_LDAP_Auth_Bind_Password', BROADSOFT_USER_MANAGEMENT_LDAP_AUTH_BIND_PASSWORD);
    $this->ldapConfig['server']         = variable_get('broadsoft_user_management_LDAP_Server_Address', BROADSOFT_USER_MANAGEMENT_LDAP_SERVER_ADDRESS);
    $this->ldapConfig['server2']        = variable_get('broadsoft_user_management_LDAP_Secondary_Server_Address', BROADSOFT_USER_MANAGEMENT_LDAP_SECONDARY_SERVER_ADDRESS);
    $this->ldapConfig['port']           = variable_get('broadsoft_user_management_LDAP_Server_Port', BROADSOFT_USER_MANAGEMENT_LDAP_SERVER_PORT);
    $this->ldapConfig['useSecureLdap']  = false;

    $this->ldapIsConnectedAsAdmin = false;
  }


  /** =============================================================================================
  */
  function AssertConnectAsAdmin() {
    if(!$this->ldapIsConnectedAsAdmin) {
      if($this->adminLogin()) {
        $this->ldapIsConnectedAsAdmin = true;
      }
    }

    return $this->ldapIsConnectedAsAdmin;
  }


  /** =============================================================================================
  */
  function  adminLogin() {
    $this->connect();

    @ldap_bind($this->ldapConnection, $this->ldapConfig['authBindDN'], $this->ldapConfig['authBindPass']);
    if (ldap_errno($this->ldapConnection) !== 0) {
      watchdog('BroadSoft Code', 'LDAP-admin login ERROR! '.ldap_error($this->ldapConnection).' - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_ERROR);
      return false;
    }
    watchdog('BroadSoft Code', 'LDAP-admin login success', NULL, WATCHDOG_DEBUG);
    $this->ldapIsConnectedAsAdmin = true;
    return true;
  }

  /**
   * 
   * @param unknown $server
   * @return Ambigous <boolean, resource>
   */
  private function failsafeConnectLDAP($server) {
    $conn = false;
    if ($this->ldapConfig['useSecureLdap']){
      watchdog('BroadSoft Code', 'LDAP-connection attempt (SECURE) - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
      $conn = ldap_connect('ldaps://'.$server,686);
    } else {
      watchdog('BroadSoft Code', 'LDAP-connection attempt (not secure) - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
      $conn = ldap_connect($server, $this->ldapConfig['port']);
    }
    if( $conn != false ) {
      ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
      ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);
    }
    return $conn;    
  }

  /** =============================================================================================
  */
  function connect() {
    ldap_set_option(null, LDAP_OPT_DEBUG_LEVEL, 0);

    $this->ldapConnection = $this->failsafeConnectLDAP($this->ldapConfig['server']);
    /**  Test connection by using anonymous bind **/
    set_error_handler(function() { /* ignore errors */ });
    ldap_bind($this->ldapConnection);
    restore_error_handler();    
    if (ldap_errno($this->ldapConnection) !== 0) {
      watchdog('BroadSoft Code', 'LDAP-connection Primary FAILED! Error:'.ldap_error($this->ldapConnection), NULL, WATCHDOG_ERROR);
      // Try the secondary
      $unbind = ldap_close($this->ldapConnection);
      $this->ldapConnection = $this->failsafeConnectLDAP($this->ldapConfig['server2']);
      ldap_bind($this->ldapConnection);
      if (ldap_errno($this->ldapConnection) !== 0) {
        watchdog('BroadSoft Code', 'LDAP-connection FAILED! Error:'.ldap_error($this->ldapConnection), NULL, WATCHDOG_ERROR);
        watchdog('BroadSoft Code', 'LDAP-connection FAILED! Config: '.$this->ldapConfig['server'].':'.$this->ldapConfig['port'], NULL, WATCHDOG_ERROR);
        return false;
      }
    }

    watchdog('BroadSoft Code', 'LDAP-connection success - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
    return true;
  }

  /** =============================================================================================
  */
  function close() {
    ldap_close($this->ldapConnection);
  }

  /** =============================================================================================
  */
  function testUserCredentials($userDN, $password) {
    watchdog('BroadSoft Code', 'LDAP-connection attempt for DN['.$userDN.'] - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
    @ldap_bind($this->ldapConnection, $userDN, $password);
    if (ldap_errno($this->ldapConnection) !== 0) {
      watchdog('BroadSoft Code', 'LDAP-connection FAILURE - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
      return FALSE;
    }

    watchdog('BroadSoft Code', 'LDAP-connection SUCCESS - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_DEBUG);
    return TRUE;
  }
 
  /** =============================================================================================
  */
  function getLDAPUserDataFromEmail($email) {
    $this->AssertConnectAsAdmin();
    
    $data = NULL;

    $result = ldap_search($this->ldapConnection, $this->ldapConfig['baseBindDN'], "(mail=".$email.")");
    $res = ldap_get_entries($this->ldapConnection, $result);

    if($res['count']==0) {
      watchdog('BroadSoft Code', 'LDAP-searching for email=['.$email.'] not found in repository - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_NOTICE);
    } else {
      watchdog('BroadSoft Code', 'LDAP-searching for email=['.$email.'] - found '.$res["count"].' occurence(s) in repository, using first only - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_NOTICE);
      $data = $res[0];
      if($res["count"]>1) 
        watchdog('BroadSoft Code', 'LDAP-searching for email=['.$email.'] - found '.$res["count"].' occurence(s) in repository, using first only - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_WARNING);
    }
        
    return $data;
  }

  /** =============================================================================================
  */
  function getLDAPUserDataFromDn($userDn) {
    $this->AssertConnectAsAdmin();
    
    $data = NULL;

    $result = ldap_search($this->ldapConnection, $this->ldapConfig['baseBindDN'], "(dn=".$userDn.")");
    $res = ldap_get_entries($this->ldapConnection, $result);

    if($res['count']==0) {
      watchdog('BroadSoft Code', 'LDAP-searching for DN=['.$userDn.'] not found in repository - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_NOTICE);
    } else {
      watchdog('BroadSoft Code', 'LDAP-searching for DN=['.$userDn.'] - found '.$res["count"].' occurence(s) in repository, using first only - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_NOTICE);
      $data = $res[0];
      if($res["count"]>1) 
        watchdog('BroadSoft Code', 'LDAP-searching for DN=['.$userDn.'] - found '.$res["count"].' occurence(s) in repository, using first only - F['.basename(__FILE__).']L['.__LINE__.']', NULL, WATCHDOG_WARNING);
    }
        
    return $data;
  }

  /** =============================================================================================
  * Returns user *CNs*
  */
  function getLDAPGetUsersInGroup($LDAPGroupName) {
    $this->AssertConnectAsAdmin();
    $data = array();

    $filter = '(&(objectClass=posixGroup)(cn='.$LDAPGroupName.'))'; // complex filtering with AND clauses
    $result = ldap_search($this->ldapConnection, $this->ldapConfig['baseBindDN'], $filter, array("memberUid"));
    $res = ldap_get_entries($this->ldapConnection, $result);
    
    if($res && isset($res[0]['memberuid'])) {
      foreach($res[0]['memberuid'] as $key => $val) {
        if(preg_match('/cn=/', $val))
          $data[] = preg_replace('/.*=/', '', preg_replace('/,ou=.*/', '', $val));
      }
    }
    
    return $data;
  }

  /** =============================================================================================
  * $data is the returned array if found
  * If none found (at large or using optionnal filtering param), return NULL
  * $nameFilter can be used to filter for some sub string within group names
  */
  function getLDAPGroupList($namePrefixFilter='') {
    $this->AssertConnectAsAdmin();
    $data = NULL;

    $result = ldap_search($this->ldapConnection, $this->ldapConfig['baseBindDN'], "(objectClass=posixGroup)", array("dn"));
    $res = ldap_get_entries($this->ldapConnection, $result);

    if($res['count']==0) {
      watchdog('BroadSoft Code', 'LDAP-searching for groups: none found in repository', NULL, WATCHDOG_NOTICE);
    } else {
      $data = array();
      
      foreach($res as $key => $val) {
        if(preg_match('/='.$namePrefixFilter.'/', $val['dn'])) // = because this is a DN
          $data[] = $val['dn'];
      }
    }
        
    return $data;
  }
  
  /** =============================================================================================
  */
  function getUserDnFromEmail($email) {
    $data = $this->getLDAPUserDataFromEmail($email);
    if ($data && !empty($data['dn']))
      return $data['dn'];
    return NULL;
  }

  /** =============================================================================================
  */
  function getUserEmailFromDn($userDN) {
    $data = $this->getLDAPUserDataFromDn($userDN);
    if ($data && !empty($data['mail']))
      return $data['mail'];
    return NULL;
  }

  /** =============================================================================================
  */
	function grantLDAPGroupMembership($email, $LDAPGroupName) {
    $this->AssertConnectAsAdmin();
    $userDN = $this->getUserDnFromEmail($email);
    if(!$userDN) {
      drupal_set_message('LDAP User ('.$email.') does not exist.', 'warning');    
      return FALSE;
    }
    if(!$this->userIsInGroup($LDAPGroupName, $userDN)) {
  		$LDAPGroupDN = "cn=".$LDAPGroupName.",ou=Astro,dc=ext,dc=broadsoft,dc=com"; // no real need to lookup group DN as DN construction is always the same
  		$groupInfo['memberUid'] = $userDN; // User's DN is part of group's 'member' array
  		ldap_mod_add($this->ldapConnection, $LDAPGroupDN, $groupInfo);
  		if (ldap_errno($this->ldapConnection) !== 0) 
  			return FALSE;

  	  $this->addDescription($userDN, "Granted Acces to (".$LDAPGroupName.")");    
    }
		return TRUE;
	}

  /** =============================================================================================
  */
	function revokeLDAPGroupMembership($email, $LDAPGroupName) {
    $this->AssertConnectAsAdmin();
		$LDAPGroupDN = "cn=".$LDAPGroupName.",ou=Astro,dc=ext,dc=broadsoft,dc=com"; // no real need to lookup group DN as DN construction is always the same
    $userDN = $this->getUserDnFromEmail($email);
    if(!$userDN) {
      drupal_set_message('LDAP User ('.$email.') does not exist.', 'warning');    
      return FALSE;
    }
    // do not return false (will display an error) if user is not in group anyway
    if(!$this->userIsInGroup($LDAPGroupName, $userDN))
      return TRUE;
    
		$groupInfo['memberUid'] = $userDN; // User's DN is part of group's 'member' array
		ldap_mod_del($this->ldapConnection, $LDAPGroupDN, $groupInfo);
		if (ldap_errno($this->ldapConnection) !== 0) 
			return FALSE;
    
	  $this->addDescription($userDN, "Revoked Acces to (".$LDAPGroupName.")");
		return TRUE;
	}

  /** =============================================================================================
  */
	function addDescription($userDN, $text2add) {
    global $user;
    $now = time();
    $nowStr = date('Y-m-d', $now).' ('.$now.')';
    $this->AssertConnectAsAdmin();
		$userInfo['description'] = $text2add." on ".$nowStr." by ".$user->name;
		ldap_mod_add($this->ldapConnection, $userDN, $userInfo);
		if (ldap_errno($this->ldapConnection) !== 0) 
			return FALSE;

		return TRUE;
  }

  /** =============================================================================================
  returns the password OR NULL
  */
	function resetPassword($email) {
    $this->AssertConnectAsAdmin();
    $newPassword = LDAP_PASSWORD_RESET_PREFIX.time();
		$userInfo['userpassword'] = $newPassword;
    $userDN = $this->getUserDnFromEmail($email);
    if(!$userDN) {
      drupal_set_message('LDAP User ('.$email.') does not exist.', 'warning');
      return FALSE;
    }
		ldap_mod_replace($this->ldapConnection, $userDN, $userInfo);
		if (ldap_errno($this->ldapConnection) !== 0) 
			return NULL;
		return $newPassword;
  }

  /** =============================================================================================
  */
	function createGroup($LDAPGroupName, $gidNumber=0) {
    global $user;
    $data = $this->getLDAPGroupList($LDAPGroupName);
    if(!$data) {
      // does not exist on LDAP - create it!
      $newentrydn="cn=".$LDAPGroupName.",ou=Astro,dc=ext,dc=broadsoft,dc=com";
      $newentry['cn']=$LDAPGroupName;
      $newentry['objectClass'][]='posixGroup';
      $newentry['gidnumber']=$gidNumber;
      $newentry['description'] = "Created on ".time()." by ".$user->name;
      ldap_add($this->ldapConnection, $newentrydn , $newentry);

      if (ldap_errno($this->ldapConnection) !== 0) {
        watchdog('BroadSoft Code', 'LDAP group creation failed for ('.$LDAPGroupName.') error ('.ldap_error($this->ldapConnection).')', NULL, WATCHDOG_ERROR);
        return false;
      }
      drupal_set_message('LDAP Group ('.$LDAPGroupName.') has been created from sratch. If this is a <b>new group creation</b> or a <b>fresh broadsoft_user_management module install</b>, you are not concerned with this message. If this is a <b>LDAP Group Name MODIFICATION</b>, pls contact support to manually change Group name on LDAP  Server.', 'warning');
      watchdog('BroadSoft Code', 'Created LDAP group ('.$LDAPGroupName.')', NULL, WATCHDOG_NOTICE);
    } 
    return TRUE;
  }

  /* ======================================================================= */
  function setFieldFromEmail($email, $fieldName, $newVal) {
    $this->AssertConnectAsAdmin();

    $userBindDN = $this->getUserDnFromEmail($email);
    $data = array();
    $data[$fieldName][0] = $newVal;
    ldap_modify($this->ldapConnection, $userBindDN, $data);
    if (ldap_errno($this->ldapConnection) !== 0) {
      if ($this->ldapConfig['outputErrorMsg']){
        watchdog('ticketing', 'LDAP-ERROR %err', array('%err'=>ldap_error($this->ldapConnection)), WATCHDOG_ERROR);
      }
      return false;
    }
    return true;
  }
  
  /** =============================================================================================
	* returns true (user is in group) or false (not)
  */
	function userIsInGroup($LDAPGroupName, $userDN) {
    $this->AssertConnectAsAdmin();
    $filter = '(&(objectClass=posixGroup)(cn='.$LDAPGroupName.')(memberUid='.$userDN.'))'; // complex filtering with AND clauses
    $searchResults = ldap_search($this->ldapConnection, $this->ldapConfig['baseDN'], $filter);
    $entries = ldap_get_entries($this->ldapConnection, $searchResults);
    return intval($entries["count"]) > 0;
  }
}
?>
