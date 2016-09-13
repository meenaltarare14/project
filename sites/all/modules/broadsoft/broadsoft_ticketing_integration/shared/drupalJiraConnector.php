<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
if(!defined('ISTOOLS')) {
  define('ISTOOLS', 'sites/all/isTools/lib/');
}
if(!defined('DFLT_JIRA_USER')) {
  define('DFLT_JIRA_USER', 'vlad.d');
}
if(!defined('DFLT_JIRA_PWD')) {
  define('DFLT_JIRA_PWD', 'BroadSoft2016#');
}
if(!defined('DFLT_JIRA_SERVER')) {
  define('DFLT_JIRA_SERVER', 'https://jira-sb.broadsoft.com');
}
define('EMAIL_TOKEN','noreply@broadsoft.com');

include_once ISTOOLS.'JIRAConnector.class.php';
include_once dirname(__FILE__) . "/TicketJira.class.php";



class drupalJiraConnector extends JIRA {

  var $isConnected;

  /**
   *
   * @var /jiraProjectMeta
   */

  var $customerFacingStatuses = array(
    'open' => 'open',
    'closed' => 'closed',
    'pending' => 'pending');
  var $statusValues = array(
    'open'    => array("\"On Hold\"","\"Open with T3\"","\"Open\"","\"New\"","\"SE Review\"","\"(T3) Requesting Information\"","\"Work As Design\""),
    'closed'  => array("\"Closed\""),
    'pending' => array("\"Pending Customer\"","\"Pending Closure\"","\"(T3) Pending Customer\""));

  var $customerFacingSeverities = array(
    'informational' => 'informational',
    'minor' => 'minor',
    'major' => 'major',
    'critical' => 'critical');
  var $severityValues = array(
    'informational' => array("\"4 - Informational\""),
    'minor' => array("\"3 - Minor\""),
    'major' => array("\"2 - Major\""),
    'critical' => array("\"1 - Critical\""));

  var $severityMap =array(
    '1 - Critical' => 'CRITICAL',
    '2 - Major' => 'HIGH',
    '3 - Minor' => 'MEDIUM',
    '4 - Informational' => 'LOW');
  protected $projectMeta;
  protected $mainIssueType = null;
  private $dependencies = array();
  private $options = array();
  var $TotalSearchResults = 0;
  /**
   * IMPORTANT: Any change to this variable needs to be documented on Unite at
   * https://home.unite.broadsoft.com/display/IS/JIRA+field+management+for+TAC+Project
   */
  var $affectedServiceFieldToOption=array(
    'Business Communicator Version/Release'=>"12976",
    'Connect Version/Release'=>"26446",
    'Conference Room Version/Release'=>"27917", // 27917 is the ID for component "Conference Room"
    'Msg Now Version/Release'=>"14009",
    'Meet Version/Release'=>"28016", // 28016 is the ID for component "Meet"
    'MobileLink Version/Release'=>"14008",
    'BroadWorks Version/Release'=>"12975", // 12975 is "Server"
    'Call Center Client Version/Release'=>"12977",
    'Packet Smart Version/Release'=>"12978",
    'Assistant Enterprise Version/Release'=>"12981",
    'Receptionist Version/Release'=>"12980",
    'Loki Version/Release'=>"22902", // 22902 is "Loki"
    'Meet-Me Addon Version/Release'=>"12983",
		'Contact Center Version/Release'=>"27027", // https://jira.broadsoft.com/browse/IST-46092
        'BroadSoft Design Version/Release'=>"28100", // 28100 is the ID for component "Design Portal"
  );
  /**
   * IMPORTANT: Any change to this variable needs to be documented on Unite at
   * https://home.unite.broadsoft.com/display/IS/JIRA+field+management+for+TAC+Project
   */
  var $componentReleaseMap = array(
      "BroadCloud" => array(
        "Call Center - Call Center Reporting" => array("Receptionist Client","Call Center Client"),
        "Front Office -Auto Attendant -ACD" => array("Receptionist Client","Call Center Client"),
        "UC-One" => array("Business Communicator"),
        "VoIP Network Assessment and QoS" => array("PacketSmart Device", "PacketSmart Portal","Server"),
      ),
      "BroadWorks" => array(
        "BCOSS" => array("Server"),
        "Business Trunking - SIP Trunking" => array("Server"),
        "Call Center - Call Center Reporting" => array("Receptionist Client","Call Center Client","Server"),
        "Call Processing -Hosted PBX general features" => array("Assistant Enterprise","Server"),
        "Call Recording" => array("Server"),
        "Conferencing and Video Collaboration" => array("Meet-Me Addon","Server"),
        "Custom Development" => array("Server"),
        "Device Management" => array("Server"),
        "Front Office-Auto Attendant-ACD" => array("Receptionist Client","Call Center Client","Server"),
        "General Support" => array("Server"),
        "IM&P" => array("Server"),
        "Lawful Intercept - CALEA" => array("Server"),
        "Loki Portal" => array("Loki"),
        "Loki Provisioning" => array("Loki"),
        "Mobility and VoLTE" => array("Server"),
        "Monitoring" => array("Server"),
        "UC-One" => array("Business Communicator","Server","Connect","Conference Room","Meet","Msg Now","MobileLink"),
        "VoIP Network Assessment & QoS" => array("PacketSmart Device", "PacketSmart Portal","Server"),
      ),
      "PS Development" => array(
        "General Support" => array("ATT-EFFS","ATT-EVAS","Contact Center", "Design Portal"),
      ),
      "Third Party" => array(
        "SurgeMail" => array("Server"),
      ),
    );

  /**
   * IMPORTANT: Any change to this variable needs to be documented on Unite at
   * https://home.unite.broadsoft.com/display/IS/JIRA+field+management+for+TAC+Project
   */
  var $excludedReleases = array("1.0","2.0","3.0","4.0","5.0","6.0","7.0","8.0","8.1","8.2","9.0","9.1","10.0","10.1","11.0","11.1","12.0");

  const MAX_OPTION_RENAMING_NB = 5;


  /** ============================================================================================= */
  public function __construct($useHTTPRequest = false, $projectKey = "TAC", $mainIssueType = "Problem Report")
  {
    $user = variable_get('ticketing_user', DFLT_JIRA_USER);
    $psswd = variable_get('ticketing_password', DFLT_JIRA_PWD);
    $server = trim(variable_get('ticketing_server', DFLT_JIRA_SERVER));
    parent::__construct($user, $psswd, $server.'/', $useHTTPRequest);
    // When Jira is unreachable, Project Meta cannot be fetched
    $this->projectMeta = $this->getCachedProjectMeta($projectKey);
	  $this->mainIssueType = $mainIssueType;
    if(!$this->projectMeta) {
      $this->handleConnectionFailure();
      $this->isConnected = FALSE;
    } else {
      $this->isConnected = TRUE;
    }
  }

  /**
   *
   * @param string $projectKey
   * @param boolean $forceRefresh
   * @return type
   */
  function getCachedProjectMeta($projectKey,$forceRefresh = false, $mainIssueType = "Problem Report") {
    if($forceRefresh){
      unset($this->projectMeta);
      $meta = $this->getProjectMeta($projectKey);
      variable_set("JIRA_{$projectKey}_meta",  $meta);
      $this->projectMeta = $meta;
    }
    require_once ISTOOLS.'jira/jiraProjectMeta.class.php';
    $meta = variable_get("JIRA_{$projectKey}_meta",null);
    if (empty($meta)){
      $meta = $this->getProjectMeta($projectKey);
    }
    if(!$forceRefresh){
      $this->fixObject($meta);
    }
    $this->projectMeta = $meta;
    $this->mainIssueType = $mainIssueType;
    return $meta;
  }

  /**
   *
   * @param unknown $mainIssueType
   */
  function setMainIssueType( $mainIssueType ) {
    $this->mainIssueType = $mainIssueType;
  }
  /**
   *
   * @return Ambigous <unknown, string>
   */
  function getMainIssueType() {
    return $this->mainIssueType;
  }


/**
 * Drupal somehow does not unserialize properly and a second pass is required
 *
 * @param type $object
 * @return type
 */
private function fixObject (&$object)
  {
    if (!is_object ($object) && gettype ($object) == 'object'){
      return ($object = unserialize (serialize ($object)));
    }
    return $object;
  }

  /** ============================================================================================= */
  /**
   *
   * @param unknown $originator
   * @param unknown $customer_company
   * @param unknown $partner_company
   * @param unknown $title
   * @param unknown $severity
   * @param unknown $customer_priority
   * @param unknown $description
   * @param unknown $product_category
   * @param unknown $component
   * @param unknown $rel_found
   * @param unknown $platform
   * @param unknown $problemCategory
   * @param unknown $systemType
   * @param unknown $user_name
   * @param unknown $user_email
   * @param unknown $contact_phone_nb
   * @param unknown $customer_note
   * @param unknown $email_list
   * @param string $ic_support
   * @param string $endCustomer
   */
  function create_ticket($originator, $customer_company, $partner_company,
      $title, $severity, $customer_priority, $description, $product_category, $component, $rel_found,
      $platform, $problemCategory, $systemType, $user_name, $user_email, $contact_phone_nb, $customer_note,$email_list,
      $ic_support=null,$endCustomer=null){

    if(!$this->isConnected) {
      return;
    }

    //force IDs to int so that the jiraIssue will recognize that it is an ID and not a value
    $issue = new jiraIssue();
    $issue->setProjectKey( $this->projectMeta->getProjectKey() );
    $issue->setIssueType( $this->getMainIssueType() );
    $fieldListLookup = $this->projectMeta->fieldListLookup;
    $fieldList = $this->projectMeta->getFieldList( $this->getMainIssueType() );
    $issue->setFieldList($fieldList);
    //map release to the correct field
    //reporter not exposed, we will fill originator
    //same for user name?
    $sevMap = array_flip($this->severityMap);
    $issue->setTitle($title)
        ->setDescription($description)
        // this has to be exploded and fill as either id or id + child
        ->setField($fieldListLookup['Product Category'], $this->mergeFieldTransform($product_category))
        //need to reverse map severity
        ->setField($fieldListLookup['Severity'], $sevMap[$severity])
        ->setField($fieldListLookup['Customer Priority'], $customer_priority)
        ->setField($fieldListLookup['Contact Email'], $user_email)
        ->setField($fieldListLookup['Alternate ID'], $customer_note)


        //should this be filled, does look like it is used
        ->setField($fieldListLookup['Affected Services'], (int)$component)
        ->setField($fieldListLookup['Customer Visible'], "Yes")
        ->setField($fieldListLookup['Customer Name'],  array("parent"=>$customer_company,"child"=>$originator))
        ->setField($fieldListLookup['Contact'], $user_name)
        ->setField($fieldListLookup['Contact Number'], $contact_phone_nb);
    if(!empty($partner_company)){
      $issue->setFieldIfFound($fieldListLookup['Partner Company'], $partner_company);
      $temp =$issue->getField($fieldListLookup['Partner Company']);
      if(empty($temp)){
        //fire email notification from drupal to whoever should get it
      }
    }
    if (!empty($problemCategory)) {
      if (is_array($problemCategory)) {
        $temp=array();
        foreach ($problemCategory as $problem){
          $temp[]= array('id'=>(string)$problem);
        }
        $issue->setField($fieldListLookup['Problem Categories'],$temp);
      } else {
        $issue->setField($fieldListLookup['Problem Categories'], array('id'=>(string)$problemCategory));
      }
    }
    if (!empty($email_list)) {
      $this->addEmailToken($email_list);
      $issue->setField($fieldListLookup['Customer Notification Emails'], $email_list);
    }
    if (!empty($systemType)) {
      $issue->setField($fieldListLookup['System Type'], (int)$systemType);
    } else {
      $issue->setField($fieldListLookup['System Type'], "Production");
    }
    if(!empty($ic_support)){
      $ic_field = $fieldListLookup['Country Restricted'];
      $issue->setFieldIfFound($ic_field,$ic_support);
    }

    //explode rel found, use first number to map to correct field, then use that to do the fieldList lookup of the release
    if (!empty($rel_found) && $rel_found !== 'noRelease' && (int)$rel_found !== 99999) {
      $rel_field=$this->relToFieldAndId($rel_found);
      if(!empty($rel_field)){
        if(!empty($platform)){
          $issue->setField($fieldListLookup[$rel_field['field']], array('parent'=>$rel_field['id'],'child'=>(int)$platform));
        } else {
          $issue->setField($fieldListLookup[$rel_field['field']], $rel_field['id']);
        }
      }
    }
    $this->createIssue($issue);
    return $issue->getIssueKey();
  }

  /** ============================================================================================= */
  public function mergeFieldTransform($field){
    $temp = explode('|',$field);
    if ($temp[1] === 'None' ) {
     $field = (int)$temp[0];
    } else {
      $field = array("parent"=>(int)$temp[0],"child"=>(int)$temp[1]);
    }
    return $field;
  }

  /** ============================================================================================= */
  private function relToFieldAndId($rel_found){
    $temp = explode ('|',$rel_found);
    if ($temp[0] !=='noRelease'){
      $map = array_flip($this->affectedServiceFieldToOption);
      $relField=$map[$temp[0]];
      $optionId = (int) $temp[1];
      return array("field"=>$relField,"id"=>$optionId);
    }
    return false;
  }


  /** =============================================================================================
  * $fieldName: (exemple) expecting 'Country Restricted' rather than the customfield_XXX id
  * returns an array of option id => option value (name)
  */
  public function getFieldOptions($fieldName){
    $retArray = array();
    $fieldID = $this->projectMeta->fieldListLookup[$fieldName];
    $fieldList = $this->projectMeta->getFieldList( $this->getMainIssueType() );
    foreach($fieldList[$fieldID]['options'] as $id => $optionArray) {
      $retArray[$id] = $optionArray['value'];
    }
    return $retArray;
  }

  /** ============================================================================================= */
  function attach_file($issueKey, $file_uri, $file_description=NULL){
    if($this->isConnected) {
      if (!isD6()){
        //this is for drupal 7
        $file_uri = drupal_realpath($file_uri);
      }
      return $this->attachFileToIssue($issueKey, $file_uri, basename($file_uri));
    }
  }

  /** ============================================================================================= */
  function get_attached_file($attachmentID){
    if($this->isConnected) {
      $file= $this->getAttachment($attachmentID);
      return $file;
    }
  }

  /** ============================================================================================= */
  function getTicket($issueKey){
    if(!$this->isConnected) {
      return null;
    }

    $issue=$this->getIssue($issueKey);
    if(is_numeric($issueKey)){
      $jql = 'jql=%20project%20=%20TAC%20AND%20"Extraview%20Ticket%20ID"%20~%20'.$issueKey;
      $results = $this->getProjectIssues('TAC',0,50,array('key'),$jql);
      if(empty($results)){
        watchdog('ticketing', 'Access by ev id failed', array('ev key' => $issueKey), WATCHDOG_NOTICE);
        return null;
      }
      foreach($results as $result){
        $issue=$this->getIssue($result->key);
        $issueKey =$result->key;
      }
    }
    if(empty($issue)) {
      watchdog('ticketing', 'JIRA failed to return an issue', array('jira-key' => $issueKey), WATCHDOG_NOTICE);
      return null;
    }

    $fieldListLookup =$this->projectMeta->fieldListLookup;

    if(empty($issue->$fieldListLookup['Customer Visible']) || $issue->$fieldListLookup['Customer Visible']->value !== 'Yes'){
      return null;
    }
    $retTicket = new TicketJira();

    if(!empty($issue->$fieldListLookup['Contact'])) {
      $retTicket->setContactName($issue->$fieldListLookup['Contact']);
    }
    if(!empty($issue->transitions)) {
      $retTicket->setTransitions($issue->transitions);
    }
    if(!empty($issue->$fieldListLookup['Contact Number'])){
      $retTicket->setContactPhoneNumber($issue->$fieldListLookup['Contact Number']);
    }
    if(!empty($issue->$fieldListLookup['Alternate ID'])) {
      $retTicket->setCustomerNote($issue->$fieldListLookup['Alternate ID']);
    }
    $retTicket->setSubmitter($issue->reporter->name);
    $retTicket->setSystemType($issue->$fieldListLookup['System Type']->id);
    if(!empty($issue->$fieldListLookup['Customer Name'])) {
      $temp = $issue->$fieldListLookup['Customer Name']->child->value;
      $retTicket->setOriginator($temp);
      $retTicket->setCustomerCompany($issue->$fieldListLookup['Customer Name']->value);
    }
    if(!empty($issue->$fieldListLookup['Country Restricted'])){
      $ic_field=$issue->$fieldListLookup['Country Restricted'];
      $retTicket->setInCountry($ic_field->value);
    }
    if(!empty($issue->$fieldListLookup['Partner Company'])){
      $retTicket->setPartnerCompany($issue->$fieldListLookup['Partner Company']->value);
    }
    if(!empty($issue->assignee->name))  {
      $retTicket->setOwner($issue->assignee->name);
    }
    if(!empty($issue->updated))  {
      $retTicket->setLastModifiedDate(strtotime($issue->updated));
    }
    $retTicket->setCreationDate(strtotime($issue->created));

    if(!empty($issue->customfield_11500)) {
      $retTicket->setFirstResponseDate(strtotime($issue->customfield_11500));
    }
    if(!empty($issue->customfield_12500)) {
      $retTicket->setClosedDate(strtotime($issue->customfield_12500));
    }
    if(!empty($issue->resolutiondate))  {
      $retTicket->setResolvedDate(strtotime($issue->resolutiondate));
    }
    if(!empty($issue->$fieldListLookup['Incident Start Date and Time']->value))  {
      $retTicket->setCreationDate(strtotime($issue->$fieldListLookup['Incident Start Date and Time']->value));
    }
    $retTicket->setSeverity($this->severityMap[$issue->$fieldListLookup['Severity']->value]);
	if(!empty($issue->$fieldListLookup['Customer Priority']->value)) {
      $retTicket->setCustomerPriority($issue->$fieldListLookup['Customer Priority']->value);
    } else {
      $retTicket->setCustomerPriority(Ticket::getDefaultCustomerPriority());
    }
    $retTicket->setStatus($this->remapStatuses($issue->status->name));
    $retTicket->setTitle($issue->summary);
    $retTicket->setDescription($issue->description);
    $retTicket->setID($issueKey);
    if(!empty($issue->$fieldListLookup['Product Category']->id)) {
      $prod = $issue->$fieldListLookup['Product Category']->id .'|'.(isset($issue->$fieldListLookup['Product Category']->child->id)?$issue->$fieldListLookup['Product Category']->child->id:'None');
      $retTicket->setProductMain($issue->$fieldListLookup['Product Category']->id);
      $retTicket->setProductCategory($prod);
    }

    if(!empty($issue->$fieldListLookup['Assignee'])) {
      $retTicket->setAssignee($issue->$fieldListLookup['Assignee']->name);
    }

    if(!empty($issue->customfield_10816)) {
      $retTicket->setUpdatedBy($issue->customfield_10816->name);
    }

    if(!empty($issue->$fieldListLookup['Affected Services']->id)) {
      $affectedServiceId=$issue->$fieldListLookup['Affected Services']->id;
      $retTicket->setComponent($affectedServiceId);
      //$relaseField depends of the affected Service
      $map = array_flip($this->affectedServiceFieldToOption);
      if (isset($map[$affectedServiceId])){
        $releaseField = $map[$affectedServiceId];
      }
    }
    if(!empty($releaseField) && isset($issue->$fieldListLookup[$releaseField]->id)) {
      $retTicket->setRelFound($affectedServiceId.'|'.$issue->$fieldListLookup[$releaseField]->id);
    } else {
      $retTicket->setRelFound('noRelease');
    }
    if(!empty($releaseField)&& isset($issue->$fieldListLookup[$releaseField]->child->id)){
      $retTicket->setPlatform($issue->$fieldListLookup[$releaseField]->child->id);
    }
    if(!empty($releaseField)&& isset($issue->$fieldListLookup[$releaseField]->child->id)){
      $retTicket->setPlatform($issue->$fieldListLookup[$releaseField]->child->id);
    }
    if(!empty($issue->$fieldListLookup['Customer Notification Emails'])){
      $temp = $issue->$fieldListLookup['Customer Notification Emails'];
      $this->removeEmailToken($temp);
      $retTicket->setEmailNotificationList($temp);
    }
    if(!empty($issue->$fieldListLookup['Problem Categories'])){
      $temp = array();
      foreach($issue->$fieldListLookup['Problem Categories'] as $pc) {
        $temp[]= $pc->id;
      }
      $retTicket->setProblemCategory($temp);
      //update this to support multiple values and display it
    }
    if(!empty($issue->customfield_11605))  {
      $retTicket->setPatchRequested(true);
    }

    if($retTicket->getPatchRequested()) {
      // in the ticket data returned by the api, there is a list of IDs which I have not been able to map to anything. Useless...
      // So, using a separate dynamic report query to get the list of patches
      $PatchRecordStr = $issue->customfield_11605;
      // returns a list of lines with fields defined in the template file i.e. __ID__;__STATUS__;__SHORT_DESCR__;__FORECAST_DATE__;
      // sample:
      //    182230;Closed;AP.platform.19.sp1.574.ap181827;&nbsp;;
      //    182229;Closed;AP.platform.19.0.574.ap181827;&nbsp;;
      //    182228;Closed;AP.platform.18.sp1.890.ap181827;&nbsp;;
      $PatchRecords = array_slice(explode("\n", $PatchRecordStr),1,-1);//first line is title, last line is blank
      foreach($PatchRecords as $line) {
        $parts = explode("|", $line);
        $patchRecord =  array(
                          'PR_ID' => $parts[1],
                          'PR_STATUS' => $parts[3],
                          'PR_TITLE' => $parts[2],
                          'PR_FORECAST_DATE' => $parts[6],
                        );
        $retTicket->addPatchRecord($patchRecord);
      }
    }
    $changeHistoryEntry = NULL;
    //get CH from user only comments
    if(!empty($issue->comment->comments)){
      foreach($issue->comment->comments as $comment){
        //only comments visible to users and comments not empty, once we remove the xchange tag
        $commentBody = trim($comment->body);
        if (!isset($comment->visibility)  && !empty($commentBody)){
          $changeHistoryEntry['comment']=" {$commentBody}";
          $changeHistoryEntry['evuser']= $comment->author->name;
          $changeHistoryEntry['timestamp']= strtotime($comment->updated);
          $retTicket->addChangeHistory($changeHistoryEntry);
        }
      }
    }

    // get attachment list
    if (!empty($issue->attachment)){
      foreach($issue->attachment as $attachment){
        $retTicket->addAttachment($attachment->filename,$attachment->created,$attachment->size,$attachment->id,$attachment->mimeType,"");
      }
    }
    return $retTicket;
  }

  /** ============================================================================================= */
  /**
   *
   * @param unknown $issueKey
   * @param unknown $curUpdateTextArr
   * @param unknown $updateArr
   * @return void|unknown
   */
  function update_ticket($issueKey, $curUpdateTextArr,$updateArr, $projectOptions = null){
    if(!$this->isConnected) {
      return;
    }

    //array as strings as they are compared to string later on
	if( is_null($projectOptions) ) {
      // Pick the default (TAC)
      $projectOptions = drupalJiraConnector::getJIRAProjectFromProductCategory( TICKETING_GROUP_DEFAULT );
    }



    $requireUpdate = false;
    $requireTransition = false;
    if(!empty($updateArr['transitions'])){
      $trans=  json_decode(urldecode($updateArr['transitions']));
    }
    if (isset($updateArr['STATUS']) && in_array( $updateArr['STATUS'], array('MONITOR','CLOSED','OPEN'))) {
      $requireTransition = true;
      switch ($updateArr['STATUS']) {
        case 'MONITOR':
		  $transitionArray = $projectOptions['transition'][$updateArr['STATUS']];
          $transition = $this->selectTransition($trans,$transitionArray);
          break;
        case 'CLOSED':
		  $transitionArray = $projectOptions['transition'][$updateArr['STATUS']];
          $transition = $this->selectTransition($trans,$transitionArray);
          break;
        case 'OPEN': //this is a back to  TAC transition
		  $transitionArray = $projectOptions['transition'][$updateArr['STATUS']];
          $transition = $this->selectTransition($trans,$transitionArray);
          break;
        default: //unknown status required, no transition available
          $requireTransition = false;
      }
    } elseif (!empty($updateArr)){
      $requireUpdate = true;
    }

    $comment=$curUpdateTextArr['CUSTOMER_COMMENTS'];
    if($requireUpdate){ //dealing with update first, then transition / comment if required
      $issue = new jiraIssue();
      $issue->setProjectKey( $this->projectMeta->getProjectKey() );

      $issue->setIssueType($this->getMainIssueType());
      $fieldListLookup =$this->projectMeta->fieldListLookup;
      $sevMap = array_flip($this->severityMap);
      foreach($updateArr as $key => $val){
        switch ($key){
          case 'SHORT_DESCR':
            $issue->setTitle($val);
            break;
          case 'IN_COUNTRY_SUPPORT':
            $issue->setField($fieldListLookup['Country Restricted'], $val);
            break;
          case 'SEVERITY_LEVEL':
            $issue->setField($fieldListLookup['Severity'], $sevMap[$val]);
            break;
			case 'CUSTOMER_PRIORITY_LEVEL':
            $issue->setField($fieldListLookup['Customer Priority'], $val);
            break;
          case 'DESCRIPTION':
            $issue->setDescription($val);
            break;
          case 'PRODUCT_CATEGORY':
            $issue->setField($fieldListLookup['Product Category'], $this->mergeFieldTransform($val));
            break;
          case 'EMAIL_LIST':
            $this->addEmailToken($val);
            $issue->setField($fieldListLookup['Customer Notification Emails'], strip_tags(urldecode(strip_tags($val))));
            break;
          case 'COMPONENT':
            $issue->setField($fieldListLookup['Affected Services'], (int)$val);
            break;
          case 'REL_FOUND':
            if (!empty($val) && $val !== 'noRelease' && (int)$val !== 99999) {
              $rel_field=$this->relToFieldAndId($val);
              if(!empty($rel_field)){
                $issue->setField($fieldListLookup[$rel_field['field']], $rel_field['id']);
              }
            }
            break;
          case 'PLATFORM':
            // platform id a child of (dependent of) release id in JIRA
            if (!empty($updateArr['REL_FOUND']) && $updateArr['REL_FOUND'] !== 'noRelease' && (int)($updateArr['REL_FOUND']) !== 99999) {
              $rel_field=$this->relToFieldAndId($updateArr['REL_FOUND']);
              if(!empty($val) && !empty($rel_field)){
                $issue->setField($fieldListLookup[$rel_field['field']], array('parent'=>$rel_field['id'],'child'=>(int)$val));
              }
            }
            break;

          case 'PROBLEM_CATEGORY':
            if((int)$val != 999999){
              if (is_array($val)) {
                $temp=array();
                foreach ($val as $problem){
                  if((int)$problem != 999999){
                    $temp[]= array('id'=>(string)$problem);
                  }
                }
                if(!empty($temp)){
                  $issue->setField($fieldListLookup['Problem Categories'],$temp);
                }
              } else {
                $issue->setField($fieldListLookup['Problem Categories'], array('id'=>(string)$val));
              }
            }
            break;
          case 'SYSTEM_TYPE':
            $issue->setField($fieldListLookup['System Type'], (int)$val);
            break;
          case 'CONTACT_NAME':
            $issue->setField($fieldListLookup['Contact'], $val);
            break;
          case 'CONTACT_NUMBER':
            $issue->setField($fieldListLookup['Contact Number'], $val);
            break;
          case 'ALT_ID':
            $issue->setField($fieldListLookup['Alternate ID'], $val);
            break;
        }
      }

      $data= $issue->getIssueObject($this->projectMeta->getFieldList( $this->getMainIssueType()),true);
      $this->updateFromGet($issueKey,  $data);
    }
    if($requireTransition){//require a transition and maybe a comment
      if (!empty($comment)) {
          $commentObject = array("update"=>array("comment"=>$this->makeCommentObject($comment,'',false,true)));
          $this->transitionIssue($issueKey, $transition,$commentObject);
      } else {
        $this->transitionIssue($issueKey, $transition);
      }
    } else {//require a simple comment
      $retMsg = $this->addCommentToIssue($issueKey, $comment,"");
    }
    return $issueKey;
  }

  /** ============================================================================================= */
/**
   *
   * @param unknown $available
   * @param unknown $toUse
   * @return NULL|Ambigous <>
   */
  private function selectTransition($available, $toUse){
    // find a preferred if present
    $preferred = null;
    $inter = array();
    foreach( $toUse as $entry) {
      if( isset( $entry['preferred'] ) && $entry['preferred']==true) {
        $preferred = $entry['id'];
      }
      if( in_array($entry['id'], $available) ) {
        $inter[] = $entry['id'];
      }
    }
    //$inter = array_intersect($toUse, $available);
    //$inter = array_values( $inter);//reset array keys
    if (in_array($preferred,$inter)){
      return $preferred;

    }
    return $inter[0];
  }


  /** =============================================================================================
  * Called only when re-synchronizing portal DB with JIRA metadata
  */
  /**
   *
   * @param string $defaultTicketingGroupType
   * @return void|multitype:multitype:
   */
  function getOptions($productCategoryID){
    if(!$this->isConnected) {
      return;
    }

    $this->options = array();

    // Get the field list from JIRA
    $fieldList = $this->projectMeta->getFieldList($this->getMainIssueType());  // Main Issue for TAC is Problem Report

    $this->options = $this->buildHardCodedOptions($fieldList, $productCategoryID);

    // after the previous line. $this->options['REL_FOUND'] contains ALL possible release options
    // add default JIRA values
    $this->options['REL_FOUND']['noRelease'] = 'None';
    $this->options['PLATFORM'][-1] = 'None';

    // arrayMerge below is required because $this->dependencies has been added content while into buildHardCodedOptions()
    $this->dependencies = $this->ArrayMergePreserveKeys($this->dependencies, $this->buildHardCodedDependencies($productCategoryID));

    return array("all_options"=>$this->options,"dependencies"=>$this->dependencies);
  }

  /**
   *
   * @param unknown $id
   * @param unknown $type
   * @return unknown
   */
  private function _getParentOptionNameFromChildID( $id, $parentDataArray ) {
    $tempName = explode("|",$id);
    $parentId = $tempName[0];
    $parentName = $parentDataArray[$parentId];
    return $parentName;
  }
  /** ============================================================================================= */
  /**
   *
   * @param unknown $fieldOptions
   * @param string $addMergeField
   * @param string $mergeName
   * @param unknown $excludeMap
   * @return multitype:string unknown
   */
  private function buildOptions($fieldOptions,$addMergeField=false,$mergeName=false,$excludeMap=array()){
    $options = array();
    // Is there something to merge ?
    if (!empty($mergeName) && !empty($addMergeField)){
      $options[$addMergeField.'|None']= $mergeName;
    }
    // Build the options
    foreach ($fieldOptions as $id=>$option){
      if(!in_array($option['value'], $excludeMap)){
        // Is valid and should not be excluded
        if (!$addMergeField){
          // Single option
          $options[$option['id']]=$option['value'];
        } else {
          // Something to merge
          if(!$mergeName){
            $options[$addMergeField.'|'.$option['id']]=$option['value'];
          } else {
            $options[$addMergeField.'|'.$option['id']]= $mergeName.' > '.$option['value'];
          }
        }
      }
    }
    return $options;
  }

  /**
   *
   * @param unknown $fieldList
   * @return multitype:multitype:string  multitype:string Ambigous <multitype:string, multitype:string string unknown > multitype:
   */
  private function buildHardCodedOptions( $fieldList, $productCategoryID ) {
    $options = array();
    foreach($fieldList as $fieldId=>$field){
      switch ($field['name']) {
        case 'Product Category':
          if( !isset($options['PRODUCT_MAIN']) ) {
            $options['PRODUCT_MAIN'] = array();
          }
          $mainProducts = $this->buildOptions($field['options']);
          $mainProductFilter = $this->getOptionsFilter('PRODUCT_MAIN', $productCategoryID);
          // Filter out the main products based on the project
          foreach( $mainProducts as $mainProductId => $mainProduct ) {
            if( !in_array($mainProduct,$mainProductFilter) ) {
              $options['PRODUCT_MAIN'][$mainProductId] = $mainProduct;
            }
          }
          // filter out the children based on the filtered main product
          $optionChildren = array();
          foreach( $field['options'] as $mainProductId => $mainProductArray ) {
            if( !in_array($mainProductArray['value'],$mainProductFilter) ) {
              $optionChildren[$mainProductId] = $mainProductArray;
            }
          }
          if( !isset($options['PRODUCT_CATEGORY']) ) {
            $options['PRODUCT_CATEGORY'] = array();
          }
          $productCategories=$this->buildOptionsFromChild($optionChildren);
          // Filter out the wrong solutions
          $productCategoryFilter = $this->_getOptionsFilterProductCategory($productCategories, $productCategoryID);
          // Something to filter
          foreach( $productCategories as $productCategoryId => $productCategory ) {
            $mainProductName = $this->_getParentOptionNameFromChildID($productCategoryId, $options['PRODUCT_MAIN']);
            if( !empty($mainProductName) ) {
              if( !isset($productCategoryFilter[$mainProductName]) || !in_array($productCategory,$productCategoryFilter[$mainProductName]) ) {
                $options['PRODUCT_CATEGORY'][$productCategoryId] = $productCategory;
              }
            }
          }
          break;
        case 'Priority':
          $options['SEVERITY_LEVEL']=array('CRITICAL'=>'CRITICAL','HIGH'=>'HIGH','MEDIUM'=>'MEDIUM','LOW'=>'LOW');
          break;
        case 'Customer Priority':
          $options['CUSTOMER_PRIORITY_LEVEL']=$this->buildOptions($field['options']);
          break;
        case 'Component/s':
          break;
        case 'Problem Categories':
          $options['PROBLEM_CATEGORY']=$this->buildOptions($field['options']);
          break;
        case 'System Type':
          $options['SYSTEM_TYPE']=$this->buildOptions($field['options']);
          break;
        case 'Affected Services':
          $options['COMPONENT']=$this->buildOptions($field['options']);
          break;

        // all these are dependencies based on https://home.unite.broadsoft.com/display/IS/2014-09-30+Meeting+Notes
        case 'Business Communicator Version/Release':
        case 'Connect Version/Release':
        case 'Conference Room Version/Release':
        case 'Meet Version/Release':
        case 'Msg Now Version/Release':
        case 'MobileLink Version/Release':
        case 'BroadWorks Version/Release':
        case 'Call Center Client Version/Release':
        case 'Packet Smart Version/Release':
        case 'Assistant Enterprise Version/Release':
        case 'Receptionist Version/Release':
        case 'Meet-Me Addon Version/Release':
        case 'Loki Version/Release':
        case 'Contact Center Version/Release':
        case 'BroadSoft Design Version/Release':
          // map field Id to affected service, encode affected service instead of field Id
          $applicableExcludedReleases = array();
          if($field['name'] != 'Loki Version/Release' &&
             $field['name'] != 'Contact Center Version/Release') {
            // Filter out some very old BW releases
            $applicableExcludedReleases = $this->excludedReleases;
          }
          $mergeId = $this->affectedServiceFieldToOption[$field['name']];

          $temp=$this->buildOptions($field['options'],$mergeId,false,$applicableExcludedReleases);
          $options['REL_FOUND'] = $this->ArrayMergePreserveKeys($options['REL_FOUND'],$temp);

          // buildOptionsFromChildWithParentMerge() also modifies $this->dependencies
          $temp2 =$this->buildOptionsFromChildWithParentMerge($field['options'],"PLATFORM",$mergeId);
          $options['PLATFORM'] = $this->ArrayMergePreserveKeys($options['PLATFORM'],$temp2);
          break;
      }
    }
    return $options;
  }


  /** ============================================================================================= */
  /**
   *
   * @return Ambigous <multitype:, string>
   */
  private function buildHardCodedDependencies($productCategoryID){
    // Remove all previous dependencies
    $dependencies= array();

    //add system type as dependencies for lab/production for product category, except broadcloud which gets only production
    $filterProblemCategory = $this->getOptionsFilter('PROBLEM_CATEGORY', $productCategoryID);
    $filterSystemType = $this->getOptionsFilter('SYSTEM_TYPE', $productCategoryID);
    $filterProductCategory = $this->getOptionsFilter('PRODUCT_CATEGORY', $productCategoryID);
    $filterAffectedServices = $this->getOptionsFilter('COMPONENT', $productCategoryID);

    foreach($this->options['PRODUCT_MAIN'] as $idp=>$namep){
      $mainProductName = $this->options['PRODUCT_MAIN'][$idp];
      foreach($this->options['PRODUCT_CATEGORY'] as $idpc=>$namepc){
        $mainProductId = explode("|",$idpc);
        if ($mainProductId[0] == $idp){
          if(isset($filterProductCategory[$mainProductName])){
            // Is it in the filter
            if( !in_array($namepc, $filterProductCategory[$mainProductName]) ) {
              $dependencies[$idp]['PRODUCT_CATEGORY'][]=$idpc;
            }
          }else {
            $dependencies[$idp]['PRODUCT_CATEGORY'][]=$idpc;
          }
        }
      }
    }
    //we build problem category selection based on diff with filter, third party gets all options
    foreach($this->options['PRODUCT_CATEGORY'] as $id=>$name){
      //////////////////////
      // PROBLEM_CATEGORY
      $mainProductName = $this->_getParentOptionNameFromChildID($id, $this->options['PRODUCT_MAIN']);
      if( !empty($mainProductName) ) {
        if(isset($filterProblemCategory[$mainProductName])){
          $dependencies[$id]['PROBLEM_CATEGORY']=array_keys(array_diff($this->options['PROBLEM_CATEGORY'], $filterProblemCategory[$mainProductName]));
        }else {
          $dependencies[$id]['PROBLEM_CATEGORY']=array_keys($this->options['PROBLEM_CATEGORY']);
        }
      }
      //////////////////////
      // SYSTEM_TYPE
      //we build system type based on broadcloud or not. BC gets production only, everything else gets lab and prod options
      if(isset($filterSystemType[$mainProductName])){
        $dependencies[$id]['SYSTEM_TYPE']=array_keys(array_diff($this->options['SYSTEM_TYPE'], $filterSystemType[$mainProductName]));
      }else {
        $dependencies[$id]['SYSTEM_TYPE']=array_keys($this->options['SYSTEM_TYPE']);
      }
    }

    //////////////////////
    // COMPONENT
    foreach($this->options['PRODUCT_CATEGORY'] as $id=>$name){
      $mainProductName = $this->_getParentOptionNameFromChildID($id, $this->options['PRODUCT_MAIN']);
      foreach($this->componentReleaseMap as $productMain=>$productCategories){
        if( $productMain === $mainProductName ) {
          // Only for the main selected
          foreach($productCategories as $productCategory=>$affectedServices){
            //need to check parent to prevent cross dependancies between cloud and works
            if($productCategory === $name){
              foreach($affectedServices as $affectedService){
                if(isset($filterAffectedServices[$productMain])){
                  // Is it in the filter
                  if( !in_array($affectedService, $filterAffectedServices[$productMain]) ) {
                    $serviceKey = array_keys($this->options['COMPONENT'],$affectedService);
                    $dependencies[$id]['COMPONENT'][] = $serviceKey[0];
                  }
                }else {
                  $serviceKey = array_keys($this->options['COMPONENT'],$affectedService);
                  $dependencies[$id]['COMPONENT'][] = $serviceKey[0];
                }
              }
            }
          }
        }
      }
      // Handle NO component
      if (empty($dependencies[$id]['COMPONENT'])){
        $serviceKey = array_keys($this->options['COMPONENT'],"Others");
        $dependencies[$id]['COMPONENT'][] = $serviceKey[0];
      }
    }
    //////////////////////
    // REL_FOUND
    foreach($this->options['COMPONENT'] as $id=>$field){
      $noRelease = TRUE;
      foreach($this->options['REL_FOUND'] as $idRel=>$rel){
        $relAffectedService= explode('|',$idRel);
        if($id === (int)$relAffectedService[0]){
          $noRelease = FALSE;
          $dependencies[$id]['REL_FOUND'][]=$idRel;
        }
      }
      if($noRelease) {
        $dependencies[$id]['REL_FOUND'][]='noRelease';
      }
    }
    $others_id = array_keys($this->options['COMPONENT'],"Others");
    if( !empty($others_id) ) {
      $dependencies[$others_id[0]]['REL_FOUND'][]='noRelease';
    }
    return $dependencies;
  }

  /** ============================================================================================= */
  private function buildOptionsFromChild($fieldOptions,$childOnly = false){
    $options = array();
    foreach ($fieldOptions as $id=>$option){
      if (isset($option['children'])){
        if($childOnly) {
          $temp =$this->buildOptions($option['children']);
        }else {
          $temp =$this->buildOptions($option['children'],$id);
        }
        $options=$this->ArrayMergePreserveKeys($options,$temp);
      }
    }
    return $options;
  }

  /** =============================================================================================
  * MODIFIES $this->dependencies along the way
  */
  private function buildOptionsFromChildWithParentMerge($fieldOptions,$name,$mergefield){
    $options = array();
    foreach ($fieldOptions as $id=>$option){
      if (isset($option['children'])){
        $temp =$this->buildOptions($option['children']);
        $options=$this->ArrayMergePreserveKeys($options,$temp);
        $this->dependencies[$mergefield.'|'.$id][$name] = array_keys($temp);
      }
    }

    return $options;
  }


  /** =============================================================================================
   * this works because we already know jira uses unique ids
   */
  private function ArrayMergePreserveKeys() {
    $arrays = func_get_args();
    foreach((array)$arrays as $array){
      foreach((array)$array as $key => $value){
              $preserved[$key]=$value;
      }
    }
    return $preserved;
  }

  /** =============================================================================================
  Possible $paramArr values:

  - ticket status
    $paramArr['filters']['status']['open']    = TRUE|FALSE
    $paramArr['filters']['status']['closed']  = TRUE|FALSE
    $paramArr['filters']['status']['pending'] = TRUE|FALSE

  - ticket severity
    $paramArr['filters']['severity']['informational'] = TRUE|FALSE
    $paramArr['filters']['severity']['minor']         = TRUE|FALSE
    $paramArr['filters']['severity']['major']         = TRUE|FALSE
    $paramArr['filters']['severity']['critical']      = TRUE|FALSE

  - required fields
    $paramArr['fields'][''] = TRUE
  */
  public function getTicketList2($gid, $paramArr, $startAt=0, $pagedBatchSize=50){ // @@@ merge with getTicketList
    $return = array();

    if(!$this->isConnected) {
      return $return;
    }

    $originator = BTI_get_exportable_group_name($gid);  //@@@ works on D7?
    $customer_company = BTI_get_exportable_account_name_CID(BTI_getAccountFromGroup($gid)); //@@@ works on D7?

    $fields = array('key');
    $fieldListLookup =$this->projectMeta->fieldListLookup;

    foreach($paramArr['fields'] as $requestedField) {
      switch($requestedField) {
        case TICKET_FIELD__EMAIL_LIST:
          $fields[] = $fieldListLookup['Customer Notification Emails'];
        break;
      }
    }

    // create filter elements if not provided - to simplify processing below
    if(!$paramArr['filters'])  {
      $paramArr['filters'] = array();
    }
    if(!array_element('status', $paramArr['filters']))  {
      $paramArr['filters']['status'] = array();
    }
    if(!array_element('severity', $paramArr['filters']))  {
      $paramArr['filters']['severity'] = array();
    }

    // build custom JQL query from filters
    $jql = 'jql=%20project%20=%20TAC%20AND%20issuetype%20%3D%20"Problem%20Report"';

    // 1 - status
    // if ALL, do not specify filtering, otherwise restrict jql
    $none_selected = (array_element('open', $paramArr['filters']['status'])==NULL && array_element('closed', $paramArr['filters']['status'])==NULL && array_element('pending', $paramArr['filters']['status'])==NULL);
    $all_selected = (array_element('open', $paramArr['filters']['status'])==TRUE && array_element('closed', $paramArr['filters']['status'])==TRUE && array_element('pending', $paramArr['filters']['status'])==TRUE);
    if($none_selected) {
      return $return;
    }

    if(!$all_selected) {
      $statusArr = array();
      foreach($this->customerFacingStatuses as $customerFacingVal) {
        if(array_element($customerFacingVal, $paramArr['filters']['status'])) {
          $statusArr = array_merge($statusArr, $this->statusValues[$customerFacingVal]);
        }
      }
      $jql .= 'AND%20status%20IN%20('.urlencode(implode(",",$statusArr)).')';
    }

    // 2 - severity
    // if ALL, do not specify filtering, otherwise restrict jql
    $none_selected = (array_element('informational', $paramArr['filters']['severity'])==NULL && array_element('minor', $paramArr['filters']['severity'])==NULL && array_element('major', $paramArr['filters']['severity'])==NULL && array_element('critical', $paramArr['filters']['severity'])==NULL);
    $all_selected = (array_element('informational', $paramArr['filters']['severity'])==TRUE && array_element('minor', $paramArr['filters']['severity'])==TRUE && array_element('major', $paramArr['filters']['severity'])==TRUE && array_element('critical', $paramArr['filters']['severity'])==TRUE);
    if($none_selected) {
      return $return;
    }

    if(!$all_selected) {
      $severityArr = array();
      foreach($this->customerFacingSeverities as $customerFacingVal) {
        if(array_element($customerFacingVal, $paramArr['filters']['severity'])) {
          $severityArr = array_merge($severityArr, $this->severityValues[$customerFacingVal]);
        }
      }
      $jql .= 'AND%20Severity%20IN%20('.urlencode(implode(",",$severityArr)).')';
    }

    // 3 - extra jql
    $alwaysCustomerVisible = '%20AND%20"Customer%20Visible"%20=%20Yes';
    $jql .= $alwaysCustomerVisible;
    $jql .= urlencode(" AND \"Customer Name\" in cascadeOption('{$customer_company}','{$originator}') ");

    // order by
    $jql .= '%20ORDER%20BY%20created%20DESC';

    // query and process results
    $results = $this->getPagedReport($jql, $startAt, $pagedBatchSize, $fields);
    foreach($results->issues as $issue){
      $return[$issue->key] = array('ID'=>$issue->key);
      foreach($paramArr['fields'] as $requestedField) {
        switch($requestedField) {
          case TICKET_FIELD__EMAIL_LIST:
            $return[$issue->key][TICKET_FIELD__EMAIL_LIST] = $issue->fields->$fieldListLookup['Customer Notification Emails'];
            break;
        }
      }
    }

    return $return;
  }

  /** =============================================================================================
  Possible filter values:
  $filterArr['status']['open']    = TRUE|FALSE
  $filterArr['status']['closed']  = TRUE|FALSE
  $filterArr['status']['pending'] = TRUE|FALSE

  $filterArr['severity']['informational'] = TRUE|FALSE
  $filterArr['severity']['minor']         = TRUE|FALSE
  $filterArr['severity']['major']         = TRUE|FALSE
  $filterArr['severity']['critical']      = TRUE|FALSE
   */
  public function getTicketList($filterArr, $originator, $customer_company, $includeDescription=FALSE, $includeComments=FALSE, $startAt=0, $pagedBatchSize=50, $order_by = 'created_on', $sort = 'DESC'){
  $return = array();
  $this->TotalSearchResults=0;

  if(!$this->isConnected){
    return $return;
  }
  if(isset($filterArr['status']['cuser'])){
    $filterArr['status']['open']    = TRUE;
    $filterArr['status']['closed']  = TRUE;
    $filterArr['status']['pending'] = TRUE;
    unset($filterArr['status']['cuser']);
    global $user;
    $filterArr['contact_email'] = $user->mail;
  }

  $fieldListLookup =$this->projectMeta->fieldListLookup;
  $fields = array('created','key',$fieldListLookup['Severity'],'status','summary',$fieldListLookup['Contact Email'],'updated','resolutiondate',$fieldListLookup['Customer Update']); // get all potential fields
  if($includeDescription) {
    $fields[] = 'description';
  }
  if($includeComments) {
    $fields[] = 'comment';
  }

  // create filter elements if not provided - to simplify processing below
  if(!$filterArr)  {
    $filterArr = array();
  }
  if(!array_element('status', $filterArr))  {
    $filterArr['status'] = array();
  }
  if(!array_element('severity', $filterArr))  {
    $filterArr['severity'] = array();
  }

  // build custom JQL query from filters
  $issueType = preg_replace('/\s/', '%20', $this->getMainIssueType());
  $jql = 'jql=%20project%3D'.$this->projectMeta->getProjectKey().'%20AND%20issuetype%20%3D%20"'.$issueType.'"';

  // 1 - status
  // if ALL, do not specify filtering, otherwise restrict jql
  $none_selected = (array_element('open', $filterArr['status'])==NULL && array_element('closed', $filterArr['status'])==NULL && array_element('pending', $filterArr['status'])==NULL);
  $all_selected = (array_element('open', $filterArr['status'])==TRUE && array_element('closed', $filterArr['status'])==TRUE && array_element('pending', $filterArr['status'])==TRUE);
  if($none_selected) {
    return $return;
  }

  if(!$all_selected) {
    $statusArr = array();
    foreach($this->customerFacingStatuses as $customerFacingVal) {
      if(array_element($customerFacingVal, $filterArr['status'])) {
        $statusArr = array_merge($statusArr, $this->statusValues[$customerFacingVal]);
      }
    }
    $jql .= 'AND%20status%20IN%20('.urlencode(implode(",",$statusArr)).')';
  }

  // 2 - severity
  // if ALL, do not specify filtering, otherwise restrict jql
  $none_selected = (array_element('informational', $filterArr['severity'])==NULL && array_element('minor', $filterArr['severity'])==NULL && array_element('major', $filterArr['severity'])==NULL && array_element('critical', $filterArr['severity'])==NULL);
  $all_selected = (array_element('informational', $filterArr['severity'])==TRUE && array_element('minor', $filterArr['severity'])==TRUE && array_element('major', $filterArr['severity'])==TRUE && array_element('critical', $filterArr['severity'])==TRUE);
  if($none_selected) {
    return $return;
  }

  if(!$all_selected) {
    $severityArr = array();
    foreach($this->customerFacingSeverities as $customerFacingVal) {
      if(array_element($customerFacingVal, $filterArr['severity'])) {
        $severityArr = array_merge($severityArr, $this->severityValues[$customerFacingVal]);
      }
    }
    $jql .= 'AND%20Severity%20IN%20('.urlencode(implode(",",$severityArr)).')';
  }

  // 3 - extra jql
  $alwaysCustomerVisible = '%20AND%20"Customer%20Visible"%20=%20Yes';
  $jql .= $alwaysCustomerVisible;
  $jql .= urlencode(" AND \"Customer Name\" in cascadeOption('{$customer_company}','{$originator}') ");

  // search tickets filter
  if(!empty($filterArr['title'])) {
    $jql .=  urlencode(" AND text ~ \"{$filterArr['title']}\"");
  }
  if(!empty($filterArr['start_date'])) {
    $jql .=  urlencode(" AND \"created\" >= \"{$filterArr['start_date']}\" ");
  }
  if(!empty($filterArr['end_date'])) {
    $jql .=  urlencode(" AND \"created\" <= \"{$filterArr['end_date']}\" ");
  }
  if(!empty($filterArr['has_attachments'])) {
    $jql .=  urlencode(" AND NOT attachments is EMPTY");
  }

  if (!empty($filterArr['contact_email'])){
    $jql .= urlencode(" AND \"Contact Email\" ~ \"{$filterArr['contact_email']}\" ");
  }
  // order by $order_by = null, $desc = null
  $fields_map = array(
    'id'=>'key',
    'title'=>'summary',
    'contact'=>'"Contact%20Email"',
    'severity_level'=>'Severity',
    'created_on'=>'created',
    'updated_on'=>'"Customer%20Update"'
  );
  //default sorting being taken care of by default values
  if( array_key_exists($order_by, $fields_map)){
    $jql .= '%20ORDER%20BY%20' . $fields_map[$order_by];
    if(isset($sort)){
      $jql .= '%20' . $sort;
    }
  }
  // query and process results

  // query and process results
  $results = $this->getPagedReport($jql, $startAt, $pagedBatchSize, $fields);
  // note: $result can be incomplete in case of mismatch between EndUser portal and JIRA (https://jira.broadsoft.com/browse/IST-51306)

  if(!empty($results->total)) {
    $this->TotalSearchResults = $results->total;
  }
  if(isset($results->issues)){
    foreach($results->issues as $issue){
      $return[$issue->key] = array(
        'DATE_CREATED'=>$this->dateIsoToHumanReadable($issue->fields->created),
        'ID'=>$issue->key,
        'EV_ID'=>!empty($issue->fields->$fieldListLookup['Extraview Ticket ID'])?$issue->fields->$fieldListLookup['Extraview Ticket ID']:"",
        'STATUS'=>$this->remapStatuses(!empty($issue->fields->status->name)?$issue->fields->status->name:''),
        'SEVERITY_LEVEL'=>!empty($issue->fields->$fieldListLookup['Severity'])?substr($issue->fields->$fieldListLookup['Severity']->value,4):'',
        'SHORT_DESCR'=>!empty($issue->fields->summary)?$issue->fields->summary:'',
        'CONTACT_NAME'=>!empty($issue->fields->customfield_11100)?$issue->fields->customfield_11100:'',
        'TIMESTAMP'=>!empty($issue->fields->$fieldListLookup['Customer Update'])?$this->dateIsoToHumanReadable($issue->fields->$fieldListLookup['Customer Update']):'',
        'DATE_CLOSED'=>$this->dateIsoToHumanReadable($issue->fields->resolutiondate));
      if($includeDescription) {
        $return[$issue->key]['DESCRIPTION'] = !empty($issue->fields->description)?$issue->fields->description:'';
      }

      if($includeComments) {
        foreach($issue->fields->comment->comments as $comment) {
          // filter out if visibility is set
          if(!isset($comment->visibility))  {
            $return[$issue->key]['COMMENTS'][] = $this->parseCommentObj($comment);
          }
        }
      }
    }
  }

  return $return;
}

  /** ============================================================================================= */
  protected function handleConnectionFailure() {
  // could not get required data from Jira. What now?
  BTI_log_jira_connection_failure();
  drupal_set_message('The ticketing system is currently unavailable and should be back shortly. Call the Global TAC at +1-240-364-9234 if you have an urgent issue.', 'error');
}

  /** ============================================================================================= */
  protected function dateIsoToTimestamp($date) {
  return strtotime($date);
}

  /** =============================================================================================
   * Parse a date and return it with a (11/13/14 2:20 AM type of format M/D/Y H:M )
   *
   * @param string $date
   * @return string
   *
   */
  protected function dateIsoToHumanReadable($date) {
  $readableDate =  date("m/d/y h:i A", strtotime($date));
  return $readableDate;
}

  /** =============================================================================================
  returns an array with fields
  ['timestamp'] - Jira ISO time
  ['original_comment']
  ['user'] - always: name
  ['email'] - maybe
  ['xuid'] - maybe
   */
  protected function parseCommentObj($comment) {
  if($comment) {
    $retArr = array();
    $retArr['timestamp'] = $comment->created;
    if(preg_match('/\[xuid:/', $comment->body)) {
      $tmpUserStr = preg_replace('/.*\[xuid:/', '', $comment->body);
      $tmpUserStr = preg_replace('/\].*/s', '', $tmpUserStr);
      $tmpUserStr = preg_replace('/email:/', '', $tmpUserStr);
      $tmpUserArr = explode(';', $tmpUserStr);
      $retArr['xuid'] = $tmpUserArr[0];
      $retArr['email'] = $tmpUserArr[1];
      $retArr['user'] = preg_replace('/@.*/', '', $tmpUserArr[1]); // strip email domain

      $retArr['original_comment'] = trim(preg_replace('/\[xuid(.*?)\]/', '', $comment->body)); // non-greedy
    } else {
      if(isset($comment->displayName)) {
        $retArr['user'] = $comment->displayName;
      } elseif(isset($comment->name)) {
        $retArr['user'] = $comment->name;
      }
      $retArr['original_comment'] = $comment->body;
    }

    return $retArr;
  }

  return NULL;
}

  /** ============================================================================================= */
  function remapStatuses($status){
    $remappedStatus ='';
    switch (strtolower($status)){
      case 'new':
        $remappedStatus= 'new';
        break;
      case 'open':
      case 'open with t3'://not sure if this is closed or open... child of other task required?
      case 'se review':
      case 'on hold':
      case '(t3) requesting information'://is this internal and still open for the customer?
      case 'work as design':
        $remappedStatus= 'open';
        break;
      case '(t3) pending customer':
        $remappedStatus= 'pending customer';
        break;
      case 'pending closure'://is this closed or action required?
        $remappedStatus= 'pending closure';
        break;
      case 'pending customer':
        $remappedStatus= 'pending customer';
        break;
      case 'closed':
        $remappedStatus= 'closed';
        break;
      default:
        $remappedStatus = $status;
    }
    return $remappedStatus;
  }

  /** ============================================================================================= */
  function getAvailableTransitionsNumbers($transitions) {
    if (empty($transitions)){
      return null;
    }
    $return = array();
    foreach($transitions as $transition){
      $return[]=$transition->id;
    }
    return $return;
  }

  /** =============================================================================================
   * Returns a list of tickets which have been closed in ...

   * params:
   *   $case : either "lastNdays" or "lastNminutes" or "duringMonthNyearM"
   *   $N    : month, 1..12
   *   $M    : year, e.g. 2015

   * Returns an array of elements:
   *   [title]
   *   [ID]
   *   [resolutiondate_timestamp]
   *   [originator_email]
   *   [groupNid]
   *   [accountNid]

   * Example call:       $ticketingService->getClosedTickets('duringMonthNyearM', 6, 2015);
   */
  public function getClosedTickets($case='lastNdays', $N=2, $M=0) {
  if(!$this->isConnected)
    return array();
  $dateField = 'closed';
  $jql = 'jql=%20project%20=%20TAC%20AND%20issuetype%20%3D%20"Problem%20Report"';
  $jql .= '%20AND%20"Customer%20Visible"%20=%20Yes';
  $statusArr = $this->statusValues['closed'];
  $jql .= '%20AND%20status%20IN%20('.urlencode(implode(",",$statusArr)).')';
  if($case=='lastNdays') {
    $jql .= "%20AND%20".$dateField."%20%3E%3D%20-".$N."d";
  } elseif($case=='lastNminutes') {
    $jql .= "%20AND%20".$dateField."%20%3E%3D%20-".$N."m";
  } elseif($case=='duringMonthNyearM') {
    $jql .= "%20AND%20Severity%20in%20(%221%20-%20Critical%22%2C%20%222%20-%20Major%22)";
    $jql .= "%20AND%20".$dateField."%20%3E%3D%20%22".$M."-".sprintf ("%02d", $N)."-01%2000%3A00%22%20AND%20".$dateField."%20%3C%3D%20%22".$M."-".sprintf ("%02d", $N+1)."-01%2000%3A00%22";
  } else {
    return array();
  }

  $fieldListLookup = $this->projectMeta->fieldListLookup;
  $fields = array('summary', 'resolutiondate', $fieldListLookup['Contact Email'], $fieldListLookup['Customer Name']);
  $resArr = array();
  $pagedBatchSize = 50;
  $curIdx = 0;
  $end = FALSE;
  while(!$end) {
    $results = $this->getPagedReport($jql, $curIdx, $pagedBatchSize, $fields);
    if(count($results->issues)>0) {
      foreach($results->issues as $issue) {
        $mailtmp = NULL;
        if(preg_match('/@/', $issue->fields->$fieldListLookup['Contact Email'])) {
          $mailtmp = $issue->fields->$fieldListLookup['Contact Email'];
        }
        $curIdx++;
        $resArr[$issue->key] = array(
          'title' => $issue->fields->summary,
          'ID' => $issue->key,
          'resolutiondate_timestamp' => strtotime($issue->fields->resolutiondate),
          'originator_email' => $mailtmp,
          'groupNid' => NULL,
          'accountNid' => NULL,
        );
        if(!empty($issue->fields->$fieldListLookup['Customer Name'])){
          $groupName = $issue->fields->$fieldListLookup['Customer Name']->child->value;
          $accountName = $issue->fields->$fieldListLookup['Customer Name']->value;
          // note: the next line actually sets 'groupNid' and 'accountNid' array values

          if(!BTI_getAccountGroupFromStr($resArr[$issue->key]['groupNid'], $resArr[$issue->key]['accountNid'], $groupName, extractCID($accountName))) {
            // could not map account group - discard ticket and report
            unset($resArr[$issue->key]);
          }
        }
      }
    } else {
      $end = TRUE;
    }
  }
  return $resArr;
}

  /** =============================================================================================
   * Returns an array with ALL patch records corresponding to the last update date restriction
  https://jira.broadsoft.com/issues/?jql=project%20%3D%20TIII%20AND%20issuetype%20%3D%20"Patch%20Record"
   */
  public function getPatchRecords() {
  if(!$this->isConnected) {
    return array();
  }

  $ticketing_patch_record_query_days_since_last_update = variable_get('ticketing_patch_record_query_days_since_last_update', 30);

  $jql = "jql=project%20%3D%20TIII%20AND%20summary%20~%20AP.%20AND%20issuetype%20%3D%20%22Patch%20Record%22%20AND%20updated%20%3E%3D%20-".$ticketing_patch_record_query_days_since_last_update."d";
  $fields = array('summary', 'status', 'resolutiondate', 'customfield_11097' /*customfield_11097 -> system critical*/);

  watchdog('ticketing', 'Starting Patch Record Update', NULL, WATCHDOG_NOTICE);
  $beg = time();
  $resArr = array();
  $pagedBatchSize = 50;
  $curIdx = 0;
  $end = FALSE;
  while(!$end) {
    $results = $this->getPagedReport($jql, $curIdx, $pagedBatchSize, $fields);
    if(count($results->issues)>0) {
      foreach($results->issues as $patchRecordData) {
        $systemCritical = ($patchRecordData->fields->customfield_11097->value=='Yes');
        $curIdx++;
        $resArr[] = array(
          'title' => $patchRecordData->fields->summary,
          'status' => $patchRecordData->fields->status->name,
          'resolutiondate_timestamp' => strtotime($patchRecordData->fields->resolutiondate),
          'systemCritical_bool' => $systemCritical
        );
      }
    } else {
      $end = TRUE;
    }
  }
  watchdog('ticketing', 'Patch Record Update: processed '.$curIdx.' records (elapsed = '.(time()-$beg).' seconds)', NULL, WATCHDOG_NOTICE);

  return $resArr;
}

  /** =============================================================================================
   * Outputs the list of monthly bundles in the exact same format used in the old EV world, either all or for specific month
   *
   * was in EV, done on int3:
   *   /usr/bin/mysql -uroot extraview -e "select id, short_descr, 'n/a', status, DATE_CLOSED from extraview where project=13 AND short_descr like 'PB%'" > tmp/allBundlesStatus.txt
   *
   * output format in EV (examples):
  248723  PB.as.18.0.890.pb20150201       n/a     CLOSED  02/01/15 14:01:57
  177822  PB.as.18.sp1.890.pb20120201     n/a     CLOSED  10/29/12 11:27:41
   * current output format in Jira (examples):
  TIII-37542 PB.ems.21.0.551.pb20150101 n/a  CLOSED 01/04/15 09:37:13
   */
  public function genMonthlyBundleRecordsDataStr($queryYearStr = NULL /* e.g. 2015 */, $queryMonthStr = NULL /* e.g. 01 (for Jan) .. 12 */) {
  if(!$this->isConnected) {
    return;
  }

  $separator=';';
  $jql = "jql=project%20%3D%20TIII%20AND%20summary%20~%20%22PB*";
  if($queryYearStr) {
    $jql .= ".pb".$queryYearStr;
    if($queryMonthStr) {
      $jql .= $queryMonthStr;
    }
    $jql .= "*";
  }
  $jql .= "%22%20AND%20issuetype%20%3D%20%22Patch%20Record%22%20Order%20by%20created%20DESC";
  $fields = array('key', 'summary', 'status', 'updated', 'resolutiondate');

  $pagedBatchSize = 50;
  $curIdx = 0;
  $end = FALSE;
  while(!$end) {
    $results = $this->getPagedReport($jql, $curIdx, $pagedBatchSize, $fields);
    if(count($results->issues)>0) {
      foreach($results->issues as $item) {
        $curIdx++;
        print
          $item->key
          .$separator.$item->fields->summary
          .$separator."n/a"
          .$separator.($item->fields->status->name=='Released'?'CLOSED':'OPEN')
          .$separator.date("m/d/y h:i:s",strtotime($item->fields->resolutiondate))
          ."\n";
      }
    } else {
      $end = TRUE;
    }
  }
  watchdog('ticketing', 'Monthly Bundle Processing: processed '.$curIdx.' records', NULL, WATCHDOG_NOTICE);
}

  /** =============================================================================================
   *  take the current list of csv emails and add noreply@broadsoft.com in the list to prevent the listener from updating
   *
   * @param string $csvEmailList
   */
  private function addEmailToken(&$csvEmailList){
  $map = explode(',', $csvEmailList);
  if(!in_array(EMAIL_TOKEN, $map)){
    $map[]=EMAIL_TOKEN;
  }
  $csvEmailList = implode(',',$map);
}

  /** =============================================================================================
   * check the inbound email list and removes the token from display to prevent the users adding and removing it
   * @param type $csvEmailList
   */
  private function removeEmailToken(&$csvEmailList){
  $map = explode(',', $csvEmailList);
  if(($key = array_search(EMAIL_TOKEN, $map)) !== false) {
    unset($map[$key]);
  }
  $csvEmailList = implode(',',$map);
}

  /** ============================================================================================= */
  protected function getServerTime() {
  $result = $this->jiraGet("serverInfo");
  if($decoded = json_decode($result['exec']))
    $serverTime = $decoded->serverTime;
  else
    $this->handleConnectionFailure();
  return (!empty($serverTime)?$serverTime:NULL);
}

  /** ============================================================================================= */
  public function serverIsAlive() {
  return ($this->getServerTime()!=NULL);
}

  /**
   * Update yellow Banner status, "Active" is the expected value to remove it
   *
   * @param string $cid
   * @param string $status
   * @return boolean TRUE if the update was a success, FALSE otherwise
   */
  public function setAccountStatus($cid, $status){
  $result = JIRA::setAccountStatus($cid, $status);
  return ($result['exec']=='true');
}

  /**
   *
   * @return multitype:Ambigous <The, unknown>
   */
  function getJiraVisibleFieldMappingArray() {
    $nameMap = array();
    for($i=0;$i< drupalJiraConnector::MAX_OPTION_RENAMING_NB;$i++) { //
      $from = variable_get('ticketing_JIRA_name_map_from_'.$i, NULL);
      $to = variable_get('ticketing_JIRA_name_map_to_'.$i, NULL);
      if( !empty($from) && !is_null($to) ){
        $nameMap[$from] = $to;
      }
    }
    return $nameMap;
  }

  /**
   *
   * @return Ambigous <multitype:, multitype:string >
   */
  private function _getOptionsFilterMainCategory($productCategoryID) {
  $filterOut = array();
  switch($this->getMainIssueType()) {
    case 'Client Feedback':
      $filterOut = array(
        //"BroadWorks",
        "BroadCloud",
        "Third Party",
        "PS Development",
        "Cloud Services"
      );
      break;
    default:
      // TAC Problem Report - product categories:
      switch($productCategoryID) {
        case BROADWORKS_GROUP_DB_VAL:
          $filterOut = array(
            //"BroadWorks",
            "BroadCloud",
            //"Third Party",
            //"PS Development",
            "Cloud Services"
          );
          break;
        case CPBX_GROUP_DB_VAL:
          $filterOut = array(
            "BroadWorks",
            //"BroadCloud",
            "Third Party",
            //"PS Development",
            "Cloud Services"
          );
          break;
        case CLOUD_SERVICES_GROUP_DB_VAL:
          $filterOut = array(
            "BroadWorks",
            "BroadCloud",
            "Third Party",
            "PS Development",
            //"Cloud Services"
          );
          break;
      }
      break;
  }
  return $filterOut;

}

  /**
   *
   * @param unknown $options
   */
  private function _getOptionsFilterProductCategory( $options, $productCategoryID ) {
  $filterOut = array();
  switch($this->getMainIssueType()) {
    case 'Client Feedback':
      // Lets filter out everything but "Clients" and a few others
      $betaProductCategoryIncluding = array( 'UC-One' );
      $betaProductCategoryBroadWorks = array();
      $betaProductCategoryOthers = array();
      foreach( $options as $item ) {
        if( !in_array( $item, $betaProductCategoryIncluding) ) {
          $betaProductCategoryBroadWorks[] = $item;
        }
        $betaProductCategoryOthers[] = $item;
      }
      // Beta is only for BW at this point
      $filterOut = array(
        "BroadWorks"=> $betaProductCategoryBroadWorks,
        "BroadCloud"=> $betaProductCategoryOthers,
        "Third Party"=> $betaProductCategoryOthers,
        "PS Development"=> $betaProductCategoryOthers,
        "Cloud Services"=> $betaProductCategoryOthers);
      break;
    default:
      // No filter for TAC Problem Report
      break;
  }
  return $filterOut;
}

  /**
   *
   * @param unknown $options
   */
  private function _getOptionsFilterComponent( $options, $productCategoryID ) {
  $filterOut = array();
  switch($this->getMainIssueType()) {
    case 'Client Feedback':
      // Lets filter out everything but "Clients" and a few others
      // NOTE: 'Outlook Add-in' is not available because there is no exposed Solution for it.
      $ProductCategoryIncluding = array( 'Business Communicator', 'Connect', 'Conference Room', 'Meet', 'MobileLink', 'Msg Now', 'Outlook Add-in' );
      $ProductCategoryBroadWorks = array();
      $ProductCategoryOthers = array();
      foreach( $this->options['COMPONENT'] as $item ) {
        if( !in_array( $item, $ProductCategoryIncluding) ) {
          $ProductCategoryBroadWorks[] = $item;
        }
        $ProductCategoryOthers[] = $item;
      }
      // Beta is only for BW at this point
      $filterOut = array(
        "BroadWorks"=> $ProductCategoryBroadWorks,
        "BroadCloud"=> $ProductCategoryOthers,
        "Third Party"=> $ProductCategoryOthers,
        "PS Development"=> $ProductCategoryOthers,
        "Cloud Services"=> $ProductCategoryOthers);
      break;
    default:
      // only 1 filter for TAC Problem Report: cpbx - PS Development
      if($productCategoryID==CPBX_GROUP_DB_VAL) {
        // filter out everything but "Contact center" and "Design Portal", for PS Development only
        $filterOut = array(
          "PS Development"=> array('ATT-EFFS', 'ATT-EVAS'),
        );
      }
      break;
  }
  return $filterOut;
}

  /**
   *
   * @param unknown $options
   */
  private function _getOptionsFilterSystemType( $options, $productCategoryID ) {
  //we build system type based on broadcloud or not. BC gets production only, everything else gets lab and prod options
  $filterOut = array(
    "BroadWorks" => array('Trial'),
    "BroadCloud" => array('Lab', 'Trial'),
    "Third Party" => array('Trial'),
    "PS Development" => array('Trial'),
    "Cloud Services" => array('Lab', 'Trial'),
  );
  return $filterOut;
}

  /**
   *
   * @param unknown $options
   */
  private function _getOptionsFilterProblemCategory( $options, $productCategoryID ) {
  $filterOut = array();
  switch($this->getMainIssueType()) {
    case 'Problem Report':
      $filterOut = array(
        "BroadWorks"=> array("Call Dropped","Call DTMF","Dasboard","Disconnect","Faulty Order","Network Issue","New Installation","Ordering","Outage (Circuit)",
          "Outage (Customer)","Outage (Network)","RMA","Service Request"),
        "BroadCloud" => array("CCXML/VXML","Information / Maintenance","Licensing","Patching / Patchtool","Performance Trending Program","Redundancy / Replication",
          "Tools/Scripts/API","Upgrade / Rollback","Upgrade / Rollback - Side Effect"),
      );
      break;
    case 'Client Feedback':
      // Lets filter out everything but "Clients" and a few others
      $betaProblemCategoryIncluding = array( 'Branding', 'Clients', 'Other / Unknown / TBD', 'Unified Communications' );
      $betaProblemCategory = array();
      foreach( $options as $item ) {
        if( !in_array( $item, $betaProblemCategoryIncluding) ) {
          $betaProblemCategory[] = $item;
        }
      }
      // Beta is only for BW at this point
      $filterOut = array("BroadWorks"=> $betaProblemCategory);
  }
  return $filterOut;
}

  /**
   *
   * @param string $filterType
   * @return Ambigous <The, unknown>
   */
  function getOptionsFilter( $filterType, $productCategoryID ) {
    $filter = array();
    if($filterType == 'PRODUCT_MAIN') {
      $filter = $this->_getOptionsFilterMainCategory($productCategoryID);
    } elseif($filterType == 'PRODUCT_CATEGORY') {
      $filter = $this->_getOptionsFilterProductCategory($this->options['PRODUCT_CATEGORY'], $productCategoryID);
    } elseif($filterType == 'COMPONENT') {
      $filter = $this->_getOptionsFilterComponent($this->options['COMPONENT'], $productCategoryID);
    } elseif($filterType == 'PROBLEM_CATEGORY') {
      $filter = $this->_getOptionsFilterProblemCategory($this->options['PROBLEM_CATEGORY'], $productCategoryID);
    } elseif($filterType == 'SYSTEM_TYPE') {
      $filter = $this->_getOptionsFilterSystemType($this->options['SYSTEM_TYPE'], $productCategoryID);
    }
    return $filter;
  }

  /**
   * @param unknown $optionArr
   * @param unknown $nameMap
   * @param number $ticketingGroupCategory
   * @return multitype:number
   */
  function writeTicketingOptions( $optionArr, $nameMap, $ticketingGroupCategory = 0 ) {
    $nbOptions = 0;
    $nbDependencies = 0;
    foreach($optionArr['all_options'] as $category_name => $options) {
      // Remove any previous options based on category
      db_query("DELETE FROM {ticketing_options} WHERE category='".$category_name."' AND to_ticketing_group_category=".$ticketingGroupCategory.";");
      foreach($options as $option_id => $option_name) {
        // Remap the field name from JIRA to external name
        if(array_key_exists($option_name, $nameMap)) {
          $option_name = $nameMap[$option_name];
        }

        // so many options do not have a direct translation. Manual matches required...
        $EVNamePatttern = $option_name;
        switch($option_name) {
          case 'Media Server': $EVNamePatttern = 'MS' ; break;
          case 'Access Mediation Server': $EVNamePatttern = 'AMS' ; break;
          case 'Application Server': $EVNamePatttern = 'AS' ; break;
          case 'Call Center - Agent - Supervisor': $EVNamePatttern = 'Call Center' ; break;
          case 'Call Center Reporting Server': $EVNamePatttern = 'CDS/CCRS' ; break;
          case 'Call Detail Server': $EVNamePatttern = 'CDS/CCRS' ; break;
          case 'Conference Server': $EVNamePatttern = 'CS' ; break;
          case 'Database Server': $EVNamePatttern = 'DBS' ; break;
          case 'Meet-Me Moderator': $EVNamePatttern = 'Meet-Me Conferencing Moderator' ; break;
          case 'Meet-Me Outlook Plugin': $EVNamePatttern = 'Meet-Me Conferencing Outlook Add-in' ; break;
          case 'Network Server': $EVNamePatttern = 'NS' ; break;
          case 'Profile Server': $EVNamePatttern = 'PS' ; break;
          case 'SCF Server': $EVNamePatttern = 'SCF' ; break;
          case 'Virtual License Server': $EVNamePatttern = 'VLS' ; break;
          case 'Web Server': $EVNamePatttern = 'WS' ; break;
          case 'Xtended Services Platform': $EVNamePatttern = 'XSP' ; break;
          case 'WebRTC Server': $EVNamePatttern = 'WebRTC File Server App' ; break;
          case 'IBM Sametime Adapter': $EVNamePatttern = 'Sametime Connector' ; break;
          case 'IM and Presence': $EVNamePatttern = 'Instant Messaging & Presence' ; break;
          case 'IsCoord Lync Plugin': $EVNamePatttern = 'Iscoord' ; break;
          case 'Web Collab Outlook Plug-in': $EVNamePatttern = 'Web Collaboration' ; break;
          default:
            $EVNamePatttern = $option_name;
            if(preg_match('/[.]sp/', $EVNamePatttern)) {
              $EVNamePatttern = 'R'.$EVNamePatttern; // e.g. R14.sp1 -> 14.sp1
            } else {
              $EVNamePatttern = preg_replace('/[.]/', '%', $EVNamePatttern);
            }
            break;
        }
        // Map names to taxonomy terms (for search purposes)
        $result = db_query("SELECT * FROM {term_data} WHERE name like '%".$EVNamePatttern."%'");
        $tid = NULL;
        if($row = db_fetch_array($result)) {
          $tid = $row['tid'];
        }

        // Insert in the database
        $opRet = db_query("INSERT INTO {ticketing_options} (oid, name, category, tid, to_ticketing_group_category) VALUES ('%s', '%s', '%s', '%d', '%d')", $option_id, $option_name, $category_name, $tid, $ticketingGroupCategory);
        $nbOptions++;
        if( $opRet != false ) {
          $nbOptions++;
        }
      }
    }

    // Build the dependencies
    foreach($optionArr['dependencies'] as $parent_option_id => $dependenciesArr) {
      db_query("DELETE FROM {ticketing_options_dependencies} where oid='".$parent_option_id."' AND tod_ticketing_group_category=".$ticketingGroupCategory.";");
      foreach($dependenciesArr as $child_category_name => $allowed_option_list_arr) {
        foreach($allowed_option_list_arr as $key => $allowed_id) {
          $opRet = db_query("INSERT INTO {ticketing_options_dependencies} (oid, dependant_category, allowed_oid, tod_ticketing_group_category) VALUES ('%s', '%s', '%s', '%d')", $parent_option_id, $child_category_name, $allowed_id, $ticketingGroupCategory);
          if( $opRet != false ) {
            $nbDependencies++;
          }
        }
      }
    }

    return array( 'options' => $nbOptions, 'dependencies' => $nbDependencies );
  }

  /**
   *
   * @param unknown $category
   * @return multitype:string |multitype:
   */
  static function getJIRAProjectFromProductCategory( $category ) {
  if( $category == TICKETING_GROUP_DEFAULT) {
    //////////////////////////////////////////////////////////////////////
    // open -> To customer for more info (11)
    // open with T3 -> T3 Pending Customer (421)
    // (T3) Resquesting Information -> Pending Customer (121)
    //////////////////////////////////////////////////////////////////////
    // open -> Close (21)
    // closed -> Closed (401)
    //////////////////////////////////////////////////////////////////////
    // closed -> Reopen (51)
    // pending customer -> Back to TAC (41)
    // (T3) Pending Customer -> Information received (131)
    $transition = array(
      'MONITOR' => array(
        array( 'id' => 11, 'name' => 'To customer for more info', 'preferred' => true),
        array( 'id' => 421, 'name' => 'T3 Pending Customer'),
        array( 'id' => 121, 'name' => 'Pending Customer'),
      ),
      'CLOSED' => array(
        array( 'id' => 21, 'name' => 'Close', 'preferred' => true),
        array( 'id' => 401, 'name' => 'Closed'),
      ),
      'OPEN' => array(
        array( 'id' => 51, 'name' => 'Reopen', 'preferred' => true),
        array( 'id' => 41, 'name' => 'Back to TAC'),
        array( 'id' => 131, 'name' => 'Information recevied'),
      ),
    );
    return array('projectKey' => "TAC", 'mainIssueType' => "Problem Report", 'transition' => $transition);
  } elseif( $category == CLIENTS_BETA_GROUP_DB_VAL) {
    //////////////////////////////////////////////////////////////////////
    // open -> Pending Customer (21)
    //////////////////////////////////////////////////////////////////////
    // open -> Close (41)
    // open -> Work as Designed (51)
    //////////////////////////////////////////////////////////////////////
    // closed -> ReOpen (61)
    $transition = array(
      'MONITOR' => array(
        array( 'id' => 21, 'name' => 'Pending Customer', 'preferred' => true),
      ),
      'CLOSED' => array(
        array( 'id' => 41, 'name' => 'Close', 'preferred' => true),
        array( 'id' => 51, 'name' => 'Work as Designed'),
      ),
      'OPEN' => array(
        array( 'id' => 61, 'name' => 'ReOpen', 'preferred' => true),
      ),
    );

    return array('projectKey' => "BETA", 'mainIssueType' => "Client Feedback", 'transition' => $transition);
  }
  return array();
}

}

