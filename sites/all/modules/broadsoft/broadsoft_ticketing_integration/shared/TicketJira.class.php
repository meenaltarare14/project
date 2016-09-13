<?php
include dirname(__FILE__)."/Ticket.class.php";
/** =============================================================================================
  This class is used to patch the differences between jira and EV seemlessly by overloading calls when the JIRA ones are not matching the EV ones
*/
class TicketJira extends Ticket {
  var $openStatuses =array( 'new','open','open with t3','se review','on hold','(t3) requesting information','work as design');
  var $pendingStatuses = array('action required','(t3) pending customer','pending closure','pending customer');
  var $pendingClosureStatuses = array('pending closure');
  var $closedStatuses = array('closed');

  function getSeveritySwitch()     {
    switch ($this->SEVERITY_LEVEL) {
      case 'CRITICAL': return 'Critical';
      case 'HIGH': return 'Major';
      case 'MEDIUM': return 'Minor';
      case 'LOW': return 'Informational';
    }
  }
  function getSeverity()     { return $this->getSeveritySwitch(); }
  function setProductMain($val) { $this->PRODUCT_MAIN = $val; }
  function getProductMain()     { return $this->PRODUCT_MAIN; }
  function getProductCategoryID()   { return $this->getProductCategory(); } // returns the pair e.g. "25900|25901"
  function getProductCategoryPair()   { // returns an array with keys {Product, Solution}
    $pair = explode("|",$this->getProductCategory());
    return array('Product' => trim($pair[0]), 'Solution' => trim($pair[1])); 
  } 
  function getComponentID()   { return $this->getComponent(); }
  function getRelFoundID()   { return $this->getRelFound(); }
  function getPlatformID()   { return $this->getPlatform() ; }
  function getProblemCategoryID()   { return $this->getProblemCategory(); }
  function getSystemTypeID()   { return $this->getSystemType(); }
  function getInCountry()     { return $this->IN_COUNTRY_SUPPORT; }
  function isOpened($str=NULL) { return ($str?($str=='Open'):(strtolower($this->STATUS)==strtolower('Open')|| in_array($this->STATUS,$this->openStatuses)) ); }
  function isPendingCustomer($str=NULL) { return (in_array($this->STATUS,$this->pendingStatuses)); }
  function isPendingClosure($str=NULL) { return (in_array($this->STATUS,$this->pendingClosureStatuses)); }
  // bypass any other internal state: if not in one of those 3, considered closed as far as end user is concerned
  function isClosed($str=NULL) {
    $isOpened = $this->isOpened($str);
    $isPendingCustomer = $this->isPendingCustomer($str);
    $isPendingClosure = $this->isPendingClosure($str);
    return !($this->isOpened($str) || $this->isPendingCustomer($str) || $this->isPendingClosure($str));
  }
  function setTransitions($val) { $this->TRANSITIONS = $val; }
  function getTransitions()     { return $this->TRANSITIONS; }
  
}

?>
