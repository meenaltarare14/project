<?php

/************************************************************************************************************
 ************************************************************************************************************
                                               EXTRA VIEW STUFF
 ************************************************************************************************************
 ************************************************************************************************************
 Credentials to use in configuration:
 - username = xchange_api
 - password = BBMA2014AU30
 ************************************************************************************************************
 ************************************************************************************************************
http://avalon.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=get&id=214219 
https://secure.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=get&id=235750
https://secure.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=get&id=244395 
https://secure.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=get&id=245220

 */

/* HTTP1
require_once "HTTP/Request.php";
*/
/* HTTP2 
require_once "HTTP/Request2.php";
*/
include("shared/Ticket.class.php");


/** =============================================================================================
 * Sample Utilisation:
 *
 * $evUtil=new ExtraViewConnector($user_id, $password);
 * $extraParms=array(
 *       'patched_server'=> '*',
 *       'rel_sched'=>'*'
 * );
 * $records=$evUtil->processReport('public', 'xchange_script_v1', $extraParms);
 * print_r($records);
 */
class ExtraViewConnector {
  var $printDebugTrace=false;

  var $userId;
  var $password;
  
  // UNSecure API: var $base_url_production="http://broadsoft-open.extraview.net/broadsoft/ExtraView/ev_api.action?";
  var $base_url_production="https://secure.extraview.net/broadsoft/ExtraView/ev_api.action?";
  var $base_url_sandbox="http://avalon.extraview.net/broadsoft/ExtraView/ev_api.action?";
  var $base_url;
  
  // Data
  var $reports;

  // List of commands
  var $getTicketStr="&statevar=get&id=%%%TICKET_ID%%%";
  var $searchCmd="&statevar=search&page_length=%%%PAGE_LENGTH%%%&record_start=%%%START_RECORD%%%";
  var $patchSearchCmd="&statevar=search&page_length=1000&record_start=1&record_count=1000";
  var $getReports="&statevar=get_reports";
  var $getUsersStr="&statevar=get_users";
  var $getUserInfoStr="&statevar=get_user_info&security_user_id=%%%USER_ID%%%";
  
  var $getMetadata="&statevar=get_valid_meta_data&all=Y";
    
  private function getAllowedList($param) { return ("&statevar=allowed_list&field=".$param); }
    
  // Specific report queries
  var $reportQuery="&statevar=run_report&id=%%%REPORT_ID%%%&page_length=%%%PAGE_LENGTH%%%&record_start=%%%START_RECORD%%%";


  /** ============================================================================================= */
  function ExtraViewConnector($context='sandbox', $EVUserID=NULL, $EVUserPsswd=NULL) {
    $drupalAvailable = function_exists('variable_get') ? true : false;
    
    if( !empty($EVUserID) )
      $this->userId = $EVUserID;
    else if( $drupalAvailable )
      $this->userId = variable_get('ticketing_user', TICKETING_USER);    
    else
      $this->userId = 'xchange';
    
    if( !empty($EVUserPsswd) )
      $this->password = $EVUserPsswd;
    else if( $drupalAvailable )
      $this->password = variable_get('ticketing_password', TICKETING_PASSWORD);    
    else
      $this->password = 'BBMA2014AU30';
    
    if( $drupalAvailable ) 
      $this->base_url = trim(variable_get('ticketing_server', TICKETING_SERVER_ADDRESS)).'/ev_api.action?';    
    else if( $context == 'sandbox' )
      $this->base_url = $this->base_url_sandbox;  //default to the sandbox
    else if( $context == 'production' )
      $this->base_url = $this->base_url_production;  //default to the sandbox
    
    $this->reports=NULL;
  }


  /** ============================================================================================= */
  function create_ticket($originator, $customer_company, $partner_company, $title, $severity, $description, $product_category, $component, $rel_found, $platform, $problemCategory, $systemType, $user_name, $user_email, $contact_phone_nb, $customer_note, $email_notifier_list){
    //@@@ $email_notifier_list is unused at this point
    global $user;
    $command  = "&statevar=insert";

    // for stats    
    $command .= "&CUSTOMER_CREATION_METHOD=31467";

    $command .= "&short_descr=".urlencode($title);
    $command .= "&description=".urlencode($description);
    
    $command .= "&SEVERITY_LEVEL=".urlencode($severity);

    $PROJECT = 10; // problem report
    $command .= "&PROJECT=".$PROJECT."&proj_id=".$PROJECT."&area_id=7"; // 7 is Bugs
    
//    $command .= "&CONTACT_NAME=".urlencode($user_name.' ('.$user_email.')');
    $command .= "&CONTACT_NAME=".urlencode($user_name);
    $command .= "&CONTACT_NUMBER=".urlencode($contact_phone_nb);
    if(strlen($customer_note))
      $command .= "&ALT_ID=".urlencode($customer_note);
    
    $command .= "&OWNER=supportcpbx";

    if($partner_company) {
      $command .= "&PARTNER_COMPANY=".urlencode($partner_company);
      $command .= "&ISSUE_SRC=277"; // 277 means source=Partner
    } else {
      $directCompanyEVID = 2032;
      $command .= "&PARTNER_COMPANY=".urlencode($directCompanyEVID); // PARTNER_COMPANY;2032;Direct 
      $command .= "&ISSUE_SRC=282"; // 282 means source=Customer 
      }
        
    $command .= "&CUSTOMER_COMPANY=".urlencode($customer_company);
    $command .= "&ORIGINATOR=".urlencode($originator);    
    $command .= "&PRODUCT_CATEGORY=".$product_category;
    $command .= "&COMPONENT=".$component;
    $command .= "&REL_FOUND=".$rel_found;
    if(strlen($platform))
      $command .= "&PLATFORM=".$platform;
    if(strlen($problemCategory))
      $command .= "&PROBLEM_CATEGORY=".$problemCategory;
    if(strlen($systemType))
      $command .= "&SYSTEM_TYPE=".$systemType;

/* 
    update below works perfectly, but strange to add an update on creation... left out for now    
    $embededXUID = '[xuid:'.$user->uid.';email:'.$user_email.']';
    $command .= "&CUSTOMER_COMMENTS=".urlencode($embededXUID.'ticket creation');
*/

    $retMsg = $this->send_request($command);

    if(preg_match('/ID \#\d* added/', $retMsg))
      return preg_replace('/\D*/', '', preg_replace('/ID \#/', '', $retMsg));
    return NULL;
  }
  
  /** =============================================================================================
  */
  function update_ticket($ticketID, $updateArr){
    //@@@ handle email_notifier_list 
    global $user;
    $command  = "&statevar=update&ID=".$ticketID;
    
    // for stats
    $command .= "&CUSTOMER_UPDATE_METHOD=API";
        
    foreach($updateArr as $key => $val)    
      $command .= "&".$key."=".$val;
    
    $retMsg = $this->send_request($command);
    
    if(preg_match('/ID \#\d* updated/', $retMsg))
      return preg_replace('/\D/', '', $retMsg);
    return NULL;
  }
  
  /** ============================================================================================= 
  */
  function get_attached_file($attachmentID){    
    $header = NULL;
    return $this->send_request("&statevar=get_attachment&attachment_id=".$attachmentID);
  }

  /** ============================================================================================= */
  function attach_file($ticketID, $file_uri, $file_description=NULL){
    $command  = "&statevar=add_attachment&p_id=".$ticketID;
    $url = $this->base_url."user_id=".$this->userId."&password=".$this->password.$command;
    
    /* HTTP1
    $req =& new HTTP_Request($url);
    $req->setMethod('POST');
    $req->addFile('file', $file_uri);
    $req->addPostData("p_id", $ticketID);
    if($file_description)
      $req->addPostData("p_attach_desc", $file_description);
    if (!PEAR::isError($req->sendRequest())) {
      $body = $req->getResponseBody();
      return $body;
    }
    */
    /* HTTP2
    $request = new HTTP_Request2($url, HTTP_Request2::METHOD_POST);
    $request->setConfig(array(
        'ssl_verify_peer'   => FALSE,
        'ssl_verify_host'   => FALSE
    ));
    $request->addUpload('file', $file_uri);
    $request->addPostParameter("p_id", $ticketID);
    if($file_description)
      $request->addPostParameter("p_attach_desc", $file_description);
    try {
      $response = $request->send();
      if (200 == $response->getStatus()) {
        return $response->getBody();
      }
    } catch (HTTP_Request2_Exception $e) {
    }
    */
    $ch = curl_init ( $url );
    if(!$ch)
      return FALSE;
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($ch, CURLOPT_USERAGENT, 'runscope/0.1' );
    curl_setopt($ch, CURLOPT_POST, true); 
    $post = array(
      'p_id' => $ticketID,
      'file' => '@'.drupal_realpath($file_uri)
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

    $html_response = curl_exec($ch);
    curl_close($ch);
    return $html_response;
    
    return NULL; // error case
  }

  /** ============================================================================================= */
  private function send_request($command){
    global $REQUEST_LIB;
    $ret = NULL;
    $url = $this->base_url."user_id=".$this->userId."&password=".$this->password."$command";
    if ($this->printDebugTrace) print $url."<br>";
    
    if($REQUEST_LIB=='HTTP1') {    
      /*
      $req =& new HTTP_Request($url);
      if (!PEAR::isError($req->sendRequest()))
        $ret = $req->getResponseBody();
        */
    } elseif($REQUEST_LIB=='HTTP2') {    
      /*
      $request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET);
      $request->setConfig(array(
          'ssl_verify_peer'   => FALSE,
          'ssl_verify_host'   => FALSE
      ));
      try {
        $response = $request->send();
        if (200 == $response->getStatus())
          $ret = $response->getBody();
      } catch (HTTP_Request2_Exception $e) { }
      */
    } else {
      /* cURL */
      $ch = curl_init($url);
      if(!$ch)
        return $ret;
      
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_USERAGENT, 'runscope/0.1' );

      $html_response = curl_exec($ch);
      $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);
      if($http_status == 200)
        $ret = $html_response;
    }
    
    return $ret;
  }

  /** ============================================================================================= */
  public function getAllAvailableReports(){
    $result = $this->send_request($this->getReports);

    $this->reports=array();
    $currentList="unknown";
    $lineId=0;
    foreach(explode("\n", $result) as $line) {
      if (preg_match('/^private/', $line)){
        $currentList="private";
      } else if (preg_match('/^public/', $line)){
        $currentList="public";
      } else {
        $lineData = explode(';', $line);
        if (count($lineData)>2){
          $this->reports[$currentList][]=array (
            'id' => $lineData[0],
            'name' => $lineData[1],
            'type' => $lineData[2],
            'description' => $lineData[3]
          );
        }
      }
    }

    return $this->reports;
  }

  /** =============================================================================================
  */
  public function getCustomers(){
    $allMetaData = $this->send_request($this->getMetadata);//  var $getMetadata="&statevar=get_valid_meta_data&all=Y";

    $allCustomers = array();
    $tmpArr = array();

    foreach(explode("\n", $allMetaData) as $line) {
      $parts = explode(";", $line);
      $category = trim($parts[0]);
      if($category=='CUSTOMER_COMPANY') {
        $EVID = trim($parts[1]);
        $name = trim($parts[2]);
        $CID = NULL;
        if(preg_match('/\(C.*\)/', $name)) 
          $CID = preg_replace('/\).*/', '', preg_replace('/.*\(/', '', $name));
        if($CID) {
          $tmpArr[$CID] = $EVID.':'.$name;
          $CuGrp = $tmpArr[$CID];
          if($CuGrp)
            $tmpArr[$CID] = $EVID.':'.$name;
        }
        $allCustomers[$EVID] = $name;
}
    }
    
    foreach(explode("\n", $allMetaData) as $line) {
      $parts = explode(";", $line);
      $category = trim($parts[0]);
      if($category=='PARTNER_COMPANY') {
        if($this->printDebugTrace)
          $EVID = trim($parts[1]);
          $name = trim($parts[2]);
        $CID = NULL;
        if(preg_match('/\(C.*\)/', $name))
          $CID = preg_replace('/\).*/', '', preg_replace('/.*\(/', '', $name));
        $allCustomers[$EVID] = $name;
      }
    }    
  }

  /** =============================================================================================
  Returns an array of the form:
  
    $retArr['all_options'][category_name][option_id] = option_name
    
    $retArr['dependencies'][parent_option_id][child_category_name] = allowed_option_list (;-separated list of ID)
    
  */
  public function getOptions() {
    $retArr = array();

    // 1: gather some required options - more will be added in step 2
    $categories = array('PRODUCT_CATEGORY', 'COMPONENT', 'SEVERITY_LEVEL');
    foreach($categories as $category_name) {
      $result = $this->send_request($this->getAllowedList($category_name));
      foreach(explode("\n", $result) as $line) {
        $parts = explode(";", $line);
        $id = trim($parts[0]);
        $name = trim($parts[1]);
        if(strlen($id) && strlen($name))
          $retArr['all_options'][$category_name][$id] = $name;
      }
    }

    // 2: gather option dependencies + some more option list
    //.... with parent=PRODUCT_CATEGORY
    $parent_category_name = 'PRODUCT_CATEGORY';
    $child_category_names = array('COMPONENT', 'PROBLEM_CATEGORY', 'SYSTEM_TYPE');
    foreach($child_category_names as $child_category_name) {
      foreach($retArr['all_options'][$parent_category_name] as $parent_option_ID => $parent_option_name) {
        $result = $this->send_request($this->getAllowedList($child_category_name.'&parent='.$parent_category_name.'&parent_val='.$parent_option_ID));
        foreach(explode("\n", $result) as $line) {
          $parts = explode(";", $line);
          $id = trim($parts[0]);
          $name = trim($parts[1]);
          if(strlen($id) && strlen($name) && ($name!='Trial')) { // system type==Trial is not a valid option
            $retArr['all_options'][$child_category_name][$id] = $name;
            $retArr['dependencies'][$parent_option_ID][$child_category_name][] = $id;
          }
        }
      }
    }
    //.... with parent=COMPONENT
    $parent_category_name = 'COMPONENT';
    $child_category_names = array('REL_FOUND', 'PLATFORM');
    foreach($child_category_names as $child_category_name) {
      foreach($retArr['all_options'][$parent_category_name] as $parent_option_ID => $parent_option_name) {
        $result = $this->send_request($this->getAllowedList($child_category_name.'&parent='.$parent_category_name.'&parent_val='.$parent_option_ID));
        foreach(explode("\n", $result) as $line) {
          $parts = explode(";", $line);
          $id = trim($parts[0]);
          $name = trim($parts[1]);
          if(strlen($id) && strlen($name)) {        
            $retArr['all_options'][$child_category_name][$id] = $name;
            $retArr['dependencies'][$parent_option_ID][$child_category_name][] = $id;
          }
        }
      }
    }

    return $retArr;
  }

  /** ============================================================================================= */
  public function getReportDefinition($scope, $reportName){
    if ($this->reports==NULL){
      $this->getAllAvailableReports();
    }

    for ($i=0; $i<count($this->reports[$scope]); $i++){
      if ($this->reports[$scope][$i]['name']==$reportName){
        return $this->reports[$scope][$i];
      }
    }

    return NULL;
  }

  /** =============================================================================================
  search call which will return the associated patches
  example:
    http://avalon.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=search&page_length=1000&record_start=1&record_count=1000&PRODUCT_NAME=PATCH_RECORD&short_descr=*ap181827&p_template_file=xchange_patch_search.html

  xchange_patch_search.html template file (needs to be uploaded to server as admin):
    __ID__;__STATUS__;__SHORT_DESCR__;__FORECAST_DATE__;
    <br>
  */
  public function getPatchRecords($PRID) {
    // this query is NOT paged
    $ret = $this->send_request("&statevar=search&page_length=1000&record_start=1&record_count=1000&PRODUCT_NAME=PATCH_RECORD&short_descr=*ap".$PRID."&p_template_file=xchange_patch_search.html");
    return $ret;
  }

  /** =============================================================================================
  example http://avalon.extraview.net/broadsoft/ExtraView/ev_api.action?user_id=xchange&password=BBMA2014AU30&statevar=get&id=214219 
  */
  public function getTicket($ticketID){ 
    // this query is NOT paged
    $tmpQueryNotPaged = preg_replace('/\%\%\%TICKET_ID\%\%\%/', $ticketID, $this->getTicketStr);

    $results=array();

    $rawResults = $this->send_request($tmpQueryNotPaged);

    $xmlReader = new XMLReader();
    $xmlReader->xml($rawResults);

    $paramName="";
    $currentResult=NULL;
    while ($xmlReader->read()) {
      if ($xmlReader->nodeType ==  XMLReader::ELEMENT){
        if ($xmlReader->name == "PROBLEM_RECORD"){
          $currentResult=array();
        }
        $paramName=$xmlReader->name;
      } else if ($xmlReader->nodeType ==  XMLReader::END_ELEMENT){
        if($currentResult!=NULL && $xmlReader->name == "PROBLEM_RECORD"){
          $results[]=$currentResult;
          $currentResult=NULL;
        }
      } else if ($xmlReader->nodeType ==  XMLReader::CDATA){
        $currentResult[$paramName]=$xmlReader->value;
      }
    }
    
    // sub-optimal: to improve
    if(count($results)==0)
      return NULL;
    
    $retTicket = new Ticket();
    if(array_key_exists('CONTACT_NAME', $results[0]))
      $retTicket->setContactName($results[0]['CONTACT_NAME']);
    if(array_key_exists('CONTACT_NUMBER', $results[0]))
      $retTicket->setContactPhoneNumber($results[0]['CONTACT_NUMBER']);
    if(array_key_exists('ALT_ID', $results[0]))
      $retTicket->setCustomerNote($results[0]['ALT_ID']);
    if(array_key_exists('SUBMITTED_BY', $results[0]))
      $retTicket->setSubmitter($results[0]['SUBMITTED_BY']);
    if(array_key_exists('SYSTEM_TYPE', $results[0]))
      $retTicket->setSystemType($results[0]['SYSTEM_TYPE']);
    if(array_key_exists('PARTNER_COMPANY', $results[0]))
      $retTicket->setPartnerCompany($results[0]['PARTNER_COMPANY']);
    if(array_key_exists('CUSTOMER_COMPANY', $results[0]))
      $retTicket->setCustomerCompany($results[0]['CUSTOMER_COMPANY']);
    if(array_key_exists('OWNER', $results[0]) && !empty($results[0]['OWNER'])) $retTicket->setOwner($results[0]['OWNER']);

    if(!empty($results[0]['TIMESTAMP'])) $retTicket->setLastModifiedDate(strtotime($results[0]['TIMESTAMP']));
    if(!empty($results[0]['RESPONSE_TIME'])) $retTicket->setFirstResponseDate(strtotime($results[0]['RESPONSE_TIME']));
    if(!empty($results[0]['DATE_RESOLVED'])) $retTicket->setResolvedDate(strtotime($results[0]['DATE_RESOLVED']));
    if(!empty($results[0]['DATE_CREATED'])) $retTicket->setCreationDate(strtotime($results[0]['DATE_CREATED']));
    
    if(array_key_exists('ORIGINATOR', $results[0]))
      $retTicket->setOriginator($results[0]['ORIGINATOR']);
    if(array_key_exists('SEVERITY_LEVEL', $results[0]))
      $retTicket->setSeverity($results[0]['SEVERITY_LEVEL']);
    if(array_key_exists('STATUS', $results[0]))
      $retTicket->setStatus($results[0]['STATUS']);
    if(array_key_exists('SHORT_DESCR', $results[0]))
      $retTicket->setTitle($results[0]['SHORT_DESCR']);
    if(array_key_exists('DESCRIPTION', $results[0]))
      $retTicket->setDescription($results[0]['DESCRIPTION']);
    if(array_key_exists('ID', $results[0]))
      $retTicket->setID($results[0]['ID']);
    if(array_key_exists('PRODUCT_CATEGORY', $results[0]))
      $retTicket->setProductCategory($results[0]['PRODUCT_CATEGORY']);
    if(array_key_exists('COMPONENT', $results[0]))
      $retTicket->setComponent($results[0]['COMPONENT']);
    if(array_key_exists('REL_FOUND', $results[0]))
      $retTicket->setRelFound($results[0]['REL_FOUND']);    
    if(array_key_exists('PLATFORM', $results[0]))
      $retTicket->setPlatform($results[0]['PLATFORM']);    
    if(array_key_exists('PROBLEM_CATEGORY', $results[0]))
      $retTicket->setProblemCategory($results[0]['PROBLEM_CATEGORY']);    
    if(array_key_exists("PATCH_REQ", $results[0]))
      $retTicket->setPatchRequested($results[0]['PATCH_REQ']=="Yes");

    if($retTicket->getPatchRequested()) {
      // in the ticket data returned by the api, there is a list of IDs which I have not been able to map to anything. Useless...
      // So, using a separate dynamic report query to get the list of patches
      $PatchRecordStr = $this->getPatchRecords($ticketID);
      // returns a list of lines with fields defined in the template file i.e. __ID__;__STATUS__;__SHORT_DESCR__;__FORECAST_DATE__;
      // sample:
      //    182230;Closed;AP.platform.19.sp1.574.ap181827;&nbsp;;
      //    182229;Closed;AP.platform.19.0.574.ap181827;&nbsp;;
      //    182228;Closed;AP.platform.18.sp1.890.ap181827;&nbsp;;

      foreach(explode("\n", $PatchRecordStr) as $line) {
        $parts = explode(";", $line);
        $patchRecord =  array(
                          'PR_ID' => $parts[0],
                          'PR_STATUS' => $parts[1],
                          'PR_TITLE' => $parts[2],
                          'PR_FORECAST_DATE' => $parts[3],
                        );
        $retTicket->addPatchRecord($patchRecord);
      }      
    }

    $EVDatePattern       = '/\d{1,2}\/\d{1,2}\/\d{1,2} \d{1,2}:\d{1,2} [AP]M/'; 
    $newEntryDatePattern = '/^\d{1,2}\/\d{1,2}\/\d{1,2} \d{1,2}:\d{1,2} [AP]M \S*$/'; // also contains author user e.g. xchange
    $changeHistoryEntry = NULL;
    foreach(explode("\n", $results[0]['CUSTOMER_COMMENTS']) as $line) {
      if(preg_match($newEntryDatePattern, $line)) {
        if($changeHistoryEntry && strlen(preg_replace('/\W/', '', $changeHistoryEntry['comment'])))
          $retTicket->addChangeHistory($changeHistoryEntry);
        $changeHistoryEntry = array();
        if(preg_match($EVDatePattern, $line, $matches)) { // cannot be false        
          $changeHistoryEntry['timestamp'] = strtotime($matches[0]);
          $changeHistoryEntry['evuser'] = trim(preg_replace($EVDatePattern, '', $line));
        }
      } else {
        if(!$changeHistoryEntry)
          $changeHistoryEntry = array();
        if(!array_key_exists('comment', $changeHistoryEntry))
          $changeHistoryEntry['comment'] = '';
        $changeHistoryEntry['comment'] .= $line;
      }
    }
    if($changeHistoryEntry && strlen(preg_replace('/\W/', '', $changeHistoryEntry['comment'])))
      $retTicket->addChangeHistory($changeHistoryEntry);

    // get attachment list
    $result = $this->send_request("&statevar=list_attachment&id=".$ticketID);
    if(!preg_match('/No attachments/', $result)) {
      foreach(explode("\n", $result) as $line) {
        $parts = explode(";", $line);
        $_parts = array();
        for($x=0 ; $x<7 ; $x++) {
          $_parts[$x] = NULL;
          if(array_key_exists($x, $parts))
            $_parts[$x] = $parts[$x];
        }
        // 2014-05-05;wallmartbbq2_2.PNG;88871;xchange;1762332;null;image/png
        //                             size-^               ^-fileid?
        $desc = NULL;
        if(strlen(trim($_parts[5])) && (trim($_parts[5])!='null'))
          $desc = trim($_parts[5]);
        $retTicket->addAttachment(trim($_parts[1]), trim($_parts[0]), trim($_parts[2]), trim($_parts[4]), trim($_parts[6]), $desc);
      }      
    }
    
    return $retTicket;
  }

  /** ============================================================================================= */
  public function processReport($scope, $reportName, $extraParams, $cachedReport = null){
    if ($reportName == "SEARCH"){
      $tmpQueryNotPaged=$this->searchCmd;
    }else if( !empty($cachedReport) ) {
      $myReport = $cachedReport;
      $tmpQueryNotPaged=preg_replace('/\%\%\%REPORT_ID\%\%\%/', $myReport['id'], $this->reportQuery);
    } else {
      $myReport=$this->getReportDefinition($scope, $reportName);
      if ($myReport==NULL){
        print "ERROR 10 - Cannot process request.\n";
        exit(10);
      }
      $tmpQueryNotPaged=preg_replace('/\%\%\%REPORT_ID\%\%\%/', $myReport['id'], $this->reportQuery);
    }
    if (isset($extraParams)){
      foreach($extraParams as $key => $value) {
        $tmpQueryNotPaged.="&".$key."=".$value;
      }
    }

    $done=false;
    $nbPerPage=100;
    $startRecordId=1;
    $results=array();
    while(!$done){
      $done=true;

      $pagedReport=preg_replace('/\%\%\%PAGE_LENGTH\%\%\%/', $nbPerPage, $tmpQueryNotPaged);
      $pagedReport=preg_replace('/\%\%\%START_RECORD\%\%\%/', $startRecordId, $pagedReport);      
      $pagedResults = $this->send_request($pagedReport);

      $xmlReader = new XMLReader();
      $xmlReader->xml($pagedResults);

      $paramName="";
      $currentResult=NULL;
      while ($xmlReader->read()) {
        if ($xmlReader->nodeType ==  XMLReader::ELEMENT){
          if ($xmlReader->name == "PROBLEM_RECORD"){
            $currentResult=array();
            $done=false;
          }
          $paramName=$xmlReader->name;
        } else if ($xmlReader->nodeType ==  XMLReader::END_ELEMENT){
          if($currentResult!=NULL && $xmlReader->name == "PROBLEM_RECORD"){
            $results[]=$currentResult;
            $currentResult=NULL;
          }
        } else if ($xmlReader->nodeType ==  XMLReader::CDATA){
          $currentResult[$paramName]=$xmlReader->value;
        }

      }
      $startRecordId+=$nbPerPage;
    }
    return $results;
  }

  /** ============================================================================================= */
  public function getAllUsers(){
    $allUsersData = $this->send_request($this->getUsersStr);

    $allUsers = array();
    foreach(explode("\n", $allUsersData) as $line) {
      $parts = explode(";", $line);
      array_push( $allUsers, $parts );
    }
    return $allUsers;
  }
  /** ============================================================================================= */
  public function getUserInfo( $id ){
    $tmpQuery=preg_replace('/\%\%\%USER_ID\%\%\%/', $id, $this->getUserInfoStr);
    $userData = $this->send_request($tmpQuery);
    $multiLine = explode("\n", $userData);
    $infos = array();
    foreach( $multiLine as $key_value) {
      $parts = explode(";", $key_value);
      $infos[$parts[0]] = trim($parts[1]);
    }
    return $infos;
  }
}

?>
