<?php

define("TICKET_FIELD__DATE_CREATED", "DATE_CREATED");
define("TICKET_FIELD__ID", "ID");
define("TICKET_FIELD__EV_ID", "EV_ID");
define("TICKET_FIELD__STATUS", "STATUS");
define("TICKET_FIELD__SEVERITY_LEVEL", "SEVERITY_LEVEL");
define("TICKET_FIELD__CUSTOMER_PRIORITY", "CUSTOMER_PRIORITY_LEVEL");
define("TICKET_FIELD__SHORT_DESCR", "SHORT_DESCR");
define("TICKET_FIELD__CONTACT_NAME", "CONTACT_NAME");
define("TICKET_FIELD__TIMESTAMP", "TIMESTAMP");
define("TICKET_FIELD__EMAIL_LIST", "EMAIL_LIST");

/** =============================================================================================
The Ticket Class aims to abstract a ticket data from the ticketing system
Wether we connect to ExtraView or to Jira, the Ticket class API (setters&getters) will remain the same
 */
class Ticket {
  private $ASSIGNEE;
  private $UPDATED_BY;
  private $CONTACT_NAME;
  private $CONTACT_NUMBER;
  private $ALT_ID;
  private $DESCRIPTION;
  private $SUBMITTED_BY;
  private $OWNER;
  private $SYSTEM_TYPE;
  private $PATCH_REQ ;
  private $PROBLEM_CATEGORY;
  private $PARTNER_COMPANY;
  private $CUSTOMER_COMPANY;
  private $DATE_CREATED;
  private $DATE_CLOSED;
  private $TIMESTAMP;
  private $RESPONSE_TIME;
  private $DATE_RESOLVED;
  private $PLATFORM;
  private $ORIGINATOR;
  protected $SEVERITY_LEVEL;
  protected $CUSTOMER_PRIORITY;
  protected $STATUS;
  private $SHORT_DESCR;
  private $PROJECT;
  private $ID;
  protected $PRODUCT_MAIN;
  private $PRODUCT_CATEGORY;
  private $COMPONENT;
  private $REL_FOUND;
  private $AttachmentArr;
  private $ChangeHistoryArr;
  private $PatchRecordArr;
  protected $emailList;
  protected $IN_COUNTRY_SUPPORT;
  protected $TRANSITIONS;

  function addChangeHistory($changeHistoryEntry) { $this->ChangeHistoryArr[] = $changeHistoryEntry; }
  function getChangeHistory() { return $this->ChangeHistoryArr; }
  function addPatchRecord($PatchRecordEntry) { $this->PatchRecordArr[] = $PatchRecordEntry; }
  function getPatchRecordArr() { return $this->PatchRecordArr; }
  function setLastModifiedDate($val)  { $this->TIMESTAMP = $val; }
  function getLastModifiedDate()      { return $this->TIMESTAMP; }
  function setFirstResponseDate($val)  { $this->RESPONSE_TIME = $val; }
  function getFirstResponseDate()      { return $this->RESPONSE_TIME; }
  function setResolvedDate($val)  { $this->DATE_RESOLVED = $val; }
  function getResolvedDate()      { return $this->DATE_RESOLVED; }
  function setClosedDate($val)  { $this->DATE_CLOSED = $val; }
  function getClosedDate()      { return $this->DATE_CLOSED; }
  public static function getTransitionsTag()     { return 'transitions'; }
  function setCreationDate($val) { $this->DATE_CREATED = $val; }
  function getCreationDate()     { return $this->DATE_CREATED; }
  function setContactName($val) { $this->CONTACT_NAME = $val; }
  function getContactName()     { return $this->CONTACT_NAME; }
  function setAssignee($val) { $this->ASSIGNEE = $val; }
  function getAssignee()     { return $this->ASSIGNEE; }
  function setUpdatedBy($val) { $this->UPDATED_BY   = $val; }
  function getUpdatedBy()     { return $this->UPDATED_BY  ; }
  public static function getContactNameTag()  { return TICKET_FIELD__CONTACT_NAME; }
  function setContactPhoneNumber($val) { $this->CONTACT_NUMBER = $val; }
  function getContactPhoneNumber()     { return $this->CONTACT_NUMBER; }
  public static function getContactPhoneNumberTag()  { return 'CONTACT_NUMBER'; }
  function setCustomerNote($val) { $this->ALT_ID = $val; }
  function getCustomerNote()     { return $this->ALT_ID; }
  public static function getCustomerNoteTag()  { return 'ALT_ID'; }
  function setInCountry($val) { $this->IN_COUNTRY_SUPPORT = $val; }
  function getInCountry()     { return 'n.a. until on Jira'; }
  public static function getInCountryTag()  { return 'IN_COUNTRY_SUPPORT'; }
  function setEmailNotificationList($val) { $this->emailList = $val; }
  public static function getEmailNotificationListTag()  { return TICKET_FIELD__EMAIL_LIST; }
  function getEmailNotificationList()     { return $this->emailList; }
  public static function getIssueUpdateTag()  { return 'CUSTOMER_COMMENTS'; }
  public static function timestamp2DateStr($timestamp)  { return date("Y-m-d H:i:s", $timestamp); }
  function setOwner($val) { $this->OWNER = $val; }
  function getOwner()     { return $this->OWNER; }
  function setSubmitter($val) { $this->SUBMITTED_BY = $val; }
  function getSubmitter()     { return $this->SUBMITTED_BY; }
  function setPartnerCompany($val) { $this->PARTNER_COMPANY = $val; }
  function getPartnerCompany()     { return $this->PARTNER_COMPANY; }
  function setCustomerCompany($val) { $this->CUSTOMER_COMPANY = $val; }
  function getCustomerCompany()     { return $this->CUSTOMER_COMPANY; }
  function setOriginator($val) { $this->ORIGINATOR = $val; }
  function getOriginator()     { return $this->ORIGINATOR; }
  function setTitle($val) { $this->SHORT_DESCR = $val; }
  function getTitle()     { return $this->SHORT_DESCR; }
  public static function getTitleTag()  { return TICKET_FIELD__SHORT_DESCR; }
  function setDescription($val) { $this->DESCRIPTION = $val; }
  function getDescription()     { return $this->DESCRIPTION; }
  public static function getDescriptionTag()  { return 'DESCRIPTION'; }
  function setID($val) { $this->ID = $val; }
  function getID()     { return $this->ID; }
  function setStatus($val) { $this->STATUS = $val; }
  function getStatus()     { return $this->STATUS; } // i.e. real, internal, status

  function setSeverity($val) { $this->SEVERITY_LEVEL = $val; }
  function getSeverity()     { return $this->SEVERITY_LEVEL; }
  public static function getSeverityTag()  { return TICKET_FIELD__SEVERITY_LEVEL; }
  function getSeverityID()   { return $this->Val2Id($this->SEVERITY_LEVEL, TICKET_FIELD__SEVERITY_LEVEL); }

  function setCustomerPriority($val) { $this->CUSTOMER_PRIORITY = $val; }
  function getCustomerPriority()     { return $this->CUSTOMER_PRIORITY; }
  public static function getCustomerPriorityTag()  { return TICKET_FIELD__CUSTOMER_PRIORITY; }
  public static function getDefaultCustomerPriority()  { return '3 - Medium'; }

  function setProductCategory($val) { $this->PRODUCT_CATEGORY = $val; }

  function getProductCategory()     { return $this->PRODUCT_CATEGORY; }
  public static function getProductCategoryTag()  { return 'PRODUCT_CATEGORY'; }
  function getProductCategoryID()   { return $this->Val2Id($this->PRODUCT_CATEGORY, 'PRODUCT_CATEGORY'); }

  function setComponent($val) { $this->COMPONENT = $val; }
  function getComponent()     { return $this->COMPONENT; }
  public static function getComponentTag()  { return 'COMPONENT'; }
  function getComponentID()   { return $this->Val2Id($this->COMPONENT, 'COMPONENT'); }

  function setRelFound($val) { $this->REL_FOUND = $val; }
  function getRelFound()     { return $this->REL_FOUND; }
  public static function getRelFoundTag()  { return 'REL_FOUND'; }
  function getRelFoundID()   { return $this->Val2Id($this->REL_FOUND, 'REL_FOUND'); }

  function setPlatform($val) { $this->PLATFORM = $val; }
  function getPlatform()     { return $this->PLATFORM; }
  public static function getPlatformTag()     { return 'PLATFORM'; }
  function getPlatformID()   { return $this->Val2Id($this->PLATFORM, 'PLATFORM'); }

  function setProblemCategory($val) { $this->PROBLEM_CATEGORY = $val; }
  function getProblemCategory()     { return $this->PROBLEM_CATEGORY; }
  public static function getProblemCategoryTag()     { return 'PROBLEM_CATEGORY'; }
  function getProblemCategoryID()   { return $this->Val2Id($this->PROBLEM_CATEGORY, 'PROBLEM_CATEGORY'); }

  function setSystemType($val) { $this->SYSTEM_TYPE = $val; }
  function getSystemType()     { return $this->SYSTEM_TYPE; }
  public static function getSystemTypeTag()     { return 'SYSTEM_TYPE'; }
  function getSystemTypeID()   { return $this->Val2Id($this->SYSTEM_TYPE, 'SYSTEM_TYPE'); }

  function setPatchRequested($val) { $this->PATCH_REQ = $val; }
  function getPatchRequested()     { return $this->PATCH_REQ; }
  public static function getPatchRequestedTag()     { return 'PATCH_REQ'; }

  function isOpened($str=NULL) { return ($str?($str=='Open'):($this->STATUS=='Open')); }
  function isPendingCustomer($str=NULL) { return ($str?($str=='Pending Customer'):($this->STATUS=='Pending Customer')); }
  function isPendingClosure($str=NULL) { return ($str?($str=='Pending Closure'):($this->STATUS=='Pending Closure')); }
  // bypass any other internal state: if not in one of those 3, considered closed as far as end user is concerned
  function isClosed($str=NULL) { return !($this->isOpened($str) || $this->isPendingCustomer($str) || $this->isPendingClosure($str)); }
  function isNew($str=NULL){return ($str?($str=='new'):(strtolower($this->STATUS)=='new')); }

  function addAttachment($fileName, $addedDate, $fileSize, $fileid, $mime, $desc) {
    $this->AttachmentArr[] = array(
      'fileName' => $fileName,
      'addedDate' => $addedDate,
      'fileSize' => $fileSize,
      'fileid' => $fileid,
      'mime' => $mime,
      'desc' => $desc
    );
  }
  function getAttachmentArr() { return $this->AttachmentArr; }

  /*-----------------------------------------------------------------------------
  Existing categories:
    PRODUCT_CATEGORY
    SEVERITY_LEVEL
    COMPONENT
    REL_FOUND
    PLATFORM
    PROBLEM_CATEGORY
    CUSTOMER_PRIORITY_LEVEL
    SYSTEM_TYPE
   */
  static function Val2Id($valueStr, $category) {
    if(isD6()) {
      $query = "SELECT oid FROM {ticketing_options} WHERE name = '".$valueStr."' AND category = '".$category."'";
      $result = db_query($query);
      if($row=db_fetch_object($result))
        return $row->oid;

      return NULL;
    } else {
      return db_query('SELECT oid FROM {ticketing_options} WHERE name=:name AND category=:category', array(':name'=>$valueStr, ':category'=>$category))->fetchField();
    }
  }
}

?>