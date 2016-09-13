<?php

include_once dirname(__FILE__) . "/drupalJiraConnector.php";
include_once dirname(__FILE__) . "/fileCachedTicketList.class.php";

class drupalCachedJiraConnector extends drupalJiraConnector {
  public function __construct($useHTTPRequest = false)
  {
    parent::__construct($useHTTPRequest);
  }

  // function getTicket($issueKey) : overriding getTicket() is not a priority; not be an issue with overloading Jira
  // eventually: if in closed ticket list, take from cache

  /*********************************************************************************
  Possible filter values:
    $filterArr['status']['open']    = TRUE|FALSE
    $filterArr['status']['closed']  = TRUE|FALSE
    $filterArr['status']['pending'] = TRUE|FALSE
    
    $filterArr['severity']['informational'] = TRUE|FALSE
    $filterArr['severity']['minor']         = TRUE|FALSE
    $filterArr['severity']['major']         = TRUE|FALSE
    $filterArr['severity']['critical']      = TRUE|FALSE
  */
  public function getTicketList($filterArr, $originator, $customer_company, $includeDescription=FALSE, $includeComments=FALSE, $startAt=0, $pagedBatchSize=50, $order_by = 'created_on', $sort = 'DESC') {
    $resArr = array();
    $getClosed = FALSE;

    // create filter elements if not provided - to simplify processing below
    if(!$filterArr) $filterArr = array();
    if(!array_element('status', $filterArr)) $filterArr['status'] = array();
    if(!array_element('severity', $filterArr)) $filterArr['severity'] = array();

    $status_none_selected = (array_element('open', $filterArr['status'])==NULL && array_element('closed', $filterArr['status'])==NULL && array_element('pending', $filterArr['status'])==NULL);
    if($status_none_selected)
      return $return;

    $severity_none_selected = (array_element('informational', $filterArr['severity'])==NULL && array_element('minor', $filterArr['severity'])==NULL && array_element('major', $filterArr['severity'])==NULL && array_element('critical', $filterArr['severity'])==NULL);
    if($severity_none_selected)
      return $return;
    
    if(array_element('closed', $filterArr['status'])) 
      $getClosed = TRUE;
    
    // 1- make Jira query for all status BUT closed
    unset($filterArr['status']['closed']);
    $resArr = parent::getTicketList($filterArr, $originator, $customer_company, $includeDescription, $includeComments, $startAt, $pagedBatchSize,$order_by,$sort);

    // 2- process closed tickets through cache
    if($getClosed) {
      $group_nid = get_cached_group_selection($originatorID, $customerID);
      $closedTicketList = $this->getALLClosed_and_updateCache($group_nid, $includeComments);
      foreach($closedTicketList as $id => $ticket) {
        // filter based on severity. Filtering post-getAll to make sure all closed tickets get into cache
        if($filterArr['severity'][strtolower($ticket['SEVERITY_LEVEL'])])
          $resArr[$id] = $ticket;
      }
    }    
    
    // @@@ manage paging through both lists!
    
    return $resArr;
  }
  
  /**********************************************************************************/
  protected function getLatestCacheTimestamp($group_nid, $inFile=TRUE) {
    global $user;
    if($inFile) {      
      // read from file
      $fileCache = new fileCachedTicketList($group_nid);
      if(file_exists($fileCache->getCacheFilepath('extra'))) 
        $grp_arr = unserialize(file_get_contents($fileCache->getCacheFilepath('extra')));
    } else {
      // read from DB
      $arr = unserialize(variable_get('TICKETING_CACHE_LATEST_TIMESTAMP'));
      $grp_arr = array_element($group_nid, $arr);
    }
    $JiraTimeStr = array_element('latestCachedTicketJiraTimeStr', $grp_arr);
    get_cached_group_selection($originatorID, $customerID);

    return $JiraTimeStr;
  }

  /**********************************************************************************/
  protected function updateLatestCacheTimestamp($group_nid, $JiraTimeStr, $inFile=TRUE) {
    global $user;
    if($inFile) {
      // write to file
      $fileCache = new fileCachedTicketList($group_nid);
      $fileData = array();
      if(file_exists($fileCache->getCacheFilepath('extra'))) 
        $fileData = unserialize(file_get_contents($fileCache->getCacheFilepath('extra')));
      $fileData['latestCachedTicketJiraTimeStr'] = $JiraTimeStr;
      if($f = @fopen($fileCache->getCacheFilepath('extra'), "w"))
        if(@fwrite($f, serialize($fileData)))
            @fclose($f); 
    } else {
      // write to DB
      $arr = unserialize(variable_get('TICKETING_CACHE_LATEST_TIMESTAMP'));
      if(!$arr)
        $arr = array();
      $arr[$group_nid]['latestCachedTicketJiraTimeStr'] = $JiraTimeStr;
      
      variable_set('TICKETING_CACHE_LATEST_TIMESTAMP', serialize($arr));
    }
    
    get_cached_group_selection($originatorID, $customerID);
  }

  /**********************************************************************************/
  protected function JiraTimeStrToJiraTimeFilterStr($JiraTimeStr, $format='Y-m-d H:i') {    
    return $this->UTCTimestampToJiraTimeStr($this->dateIsoToTimestamp($JiraTimeStr), $format);
  }

  /**********************************************************************************/
  protected function UTCTimestampToJiraTimeStr($timestamp, $format='Y-m-d H:i') {    
    return date($format, $timestamp);
  }

  /*********************************************************************************
    May be used externally to artificially update cache for some group
    Example:
      $cache = new drupalCachedJiraConnector(isD6());
      $cache->artificiallyUpdateCache(472108);  
  */
  public function artificiallyUpdateCache($group_nid) {
    // set registered group selection
    BTI_setUserPreference('selectedCustomerGroupNid', $group_nid);
    $this->getALLClosed_and_updateCache($group_nid);
  }

  /*********************************************************************************
  Returns list of ALL closed tickets, latest & cached
  */
  public function getALLClosed_and_updateCache($group_nid, $include_comments=FALSE) {    
    global $user;
    $fileCache = new fileCachedTicketList($group_nid);
    get_cached_group_selection($originatorID, $customerID);
    $cacheIsOutdated = FALSE;    
    $JiraTimeStr_beg = $this->getServerTime(); // remember time at beg of the cache update procedure (things can happen in the meantime on Jira)

    $latestCachedTicketJiraTimeStr = NULL;
    $cachedTicketList = array();
    if($fileCache->cacheExists()) {
      // 1-read cached tickets    
      $cachedData = $fileCache->read();
      $cachedTicketList = array_element('tickets', $cachedData);
      $latestCachedTicketJiraTimeStr = $this->getLatestCacheTimestamp($group_nid);
    }
    
    // 2- get latest closed tickets from Jira, in range since $latestCachedTicketJiraTimeStr
    if($this->projectMeta) {
      $fieldListLookup = $this->projectMeta->fieldListLookup;
      $fields = array('created',$fieldListLookup['Extraview Ticket ID'],'key',$fieldListLookup['Severity'],'status','summary','customfield_11100','updated','resolutiondate','description','comment',$fieldListLookup['Customer Update']); // get all potential fields

      $jql = 'jql=%20project%20=%20TAC%20AND%20issuetype%20%3D%20"Problem%20Report"';
      $jql .= '%20AND%20"Customer%20Visible"%20=%20Yes';
      if($latestCachedTicketJiraTimeStr) 
        $jql .= '%20AND%20updated%20%3E%20"'.urlencode($this->JiraTimeStrToJiraTimeFilterStr($latestCachedTicketJiraTimeStr)).'"'; // > $latestCachedTicketJiraTimeStr

      $jql .= urlencode(" AND \"Customer Name\" in cascadeOption('{$customerID}','{$originatorID}') ");
  DebugPrintComplex('User ['.$user->mail.'] Ticketing Cache ['.$originatorID.', '.$customerID.']: JQL is: '.$jql);
      // query and process results    
      $curPos = 0;
      $pageSize = 50;
      $done = FALSE;
      while(!$done) {
        $results = $this->getPagedReport($jql, $curPos, $pageSize, $fields);
        if(isset($results->issues) && count($results->issues)) {
  DebugPrintComplex('User ['.$user->mail.'] Ticketing Cache ['.$originatorID.', '.$customerID.']: Jira query fetched ('.count($results->issues).') issues');
          foreach($results->issues as $issue) {
            if(preg_grep("/".$issue->fields->status->name."/i", $this->statusValues['closed'])) {            
              // CLOSED vv
              if(!array_key_exists($issue->key, $cachedTicketList)) {
  DebugPrintComplex('User ['.$user->mail.'] Ticketing Cache ['.$group_nid.', '.$originatorID.', '.$customerID.']: adding to cache - status = ('.$issue->fields->status->name.') - issue key = '.$issue->key);
                // Closed: add to cache
                $cacheIsOutdated = TRUE;
                $cachedTicketList[$issue->key] = array(
                                          'DATE_CREATED'=>$this->dateIsoToHumanReadable($issue->fields->created),
                                          'ID'=>$issue->key,
                                          'EV_ID'=>!empty($issue->fields->$fieldListLookup['Extraview Ticket ID'])?$issue->fields->$fieldListLookup['Extraview Ticket ID']:"",
                                          'STATUS'=>$this->remapStatuses(!empty($issue->fields->status->name)?$issue->fields->status->name:''),
                                          'SEVERITY_LEVEL'=>!empty($issue->fields->$fieldListLookup['Severity'])?substr($issue->fields->$fieldListLookup['Severity']->value,4):'',
                                          'SHORT_DESCR'=>!empty($issue->fields->summary)?$issue->fields->summary:'',
                                          'CONTACT_NAME'=>!empty($issue->fields->customfield_11100)?$issue->fields->customfield_11100:'',
                                          'TIMESTAMP'=>!empty($issue->fields->$fieldListLookup['Customer Update'])?$this->dateIsoToHumanReadable($issue->fields->$fieldListLookup['Customer Update']):'',
                                          'DESCRIPTION'=>!empty($issue->fields->description)?$issue->fields->description:'',
                                          'DATE_CLOSED'=>$this->dateIsoToHumanReadable($issue->fields->resolutiondate));
                // add closed comments
                foreach($issue->fields->comment->comments as $comment) {
                  // filter out if visibility is set
                  if(!isset($comment->visibility)) 
                    $cachedTicketList[$issue->key]['COMMENTS'][] = $this->parseCommentObj($comment);
                }
              }
            } else {
              // NOT CLOSED vv : if were closed previously, remove
              if(array_key_exists($issue->key, $cachedTicketList)) {
  DebugPrintComplex('User ['.$user->mail.'] Ticketing Cache ['.$group_nid.', '.$originatorID.', '.$customerID.']: removing from cache - status = ('.$issue->fields->status->name.') - issue key = '.$issue->key);
                unset($cachedTicketList[$issue->key]);
                $cacheIsOutdated = TRUE;
              }
            }
          }
        } else 
          $done = TRUE;
        $curPos += $pageSize;
      }
      if($cacheIsOutdated) {
        // 3-write to file with file size limit...      
        $resData = array();
        $resData['tickets'] = $cachedTicketList;
        $fileCache->write($resData);
      }
      $this->updateLatestCacheTimestamp($group_nid, $JiraTimeStr_beg);
    } else
      $this->handleConnectionFailure();
      
    return $cachedTicketList;
  }

}
