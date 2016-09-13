<?php
/**
 * Description of JIRA
 *
 * @author arochereau
 */

/**
 *
 */
class JIRA{

  private $user = null;
  private $pwd = null;
  private $host = null;
  private $jiraAPI = "rest/api/latest/";
  private $error = null;
  private $issue = null;
  private $issueKey = null;
  private $projectMeta = null;
  private $projectKey = null;
  private $httpRequest = false;
  private $headers = array('Accept: application/json','Content-Type: application/json','X-Atlassian-Token: nocheck');

  const RELATES_TO_LINK_NAME = 'Relates';
  /**
   *
   * @param string $user
   * @param string $pwd
   * @param string $host must include trailing slash
   * @param boolean $httpRequest uses pear HTTP Request lib instead of curl
   * @throws Exception
   */
  function __construct($user,$pwd,$host, $httpRequest = false)
  {
    require_once dirname ( __FILE__ ) . '/jira/jiraIssue.class.php';
    if($httpRequest) {
      $this->httpRequest = true;
      require_once "HTTP/Request.php";
    }
    if( !empty($user) && !empty($pwd) && !empty($host)  ){
      $this->user=$user;
      $this->pwd=$pwd;
      $this->host=$host;
    } else {
      throw new Exception('Missing user or password to connect to JIRA');
    }
  }
  
  /**
   * 
   * @return Ambigous <string, mixed>
   */
  function getLastError() {
    return $this->error;
  }

  /**
   *
   * @param string $url
   * @param string $customRequest GET or DELETE, default GET
   * @return type
   */
  protected function jiraGet($url,$customRequest="GET"){
    if(!$this->httpRequest) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);
      curl_setopt($ch, CURLOPT_URL, $this->host.$this->jiraAPI.$url);
      curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->pwd}");
      $return['exec'] = curl_exec($ch);
      $return['error'] = curl_error($ch);
      
      // Check for the SSL error
      // error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)
      if( !empty($return['error']) && preg_match('/error\:\d*\:SSL routines\:SSL23_GET_SERVER_HELLO\:reason.*/', $return['error'])) {
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        $return['exec'] = curl_exec($ch);
        $return['error'] = curl_error($ch);
      }
      // store last response in session
      $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $_SESSION['last_jira_response']['code'] = $httpcode;
      $_SESSION['last_jira_response']['error'] = $return['error'];
      curl_close($ch);
      return $return;
    } else {
      return $this->jiraHttpGet($url,$customRequest);
    }
  }

  private function jiraHttpGet($url, $customRequest){
    $req = new HTTP_REQUEST($this->host.$this->jiraAPI.$url);
    $req->setMethod("{$customRequest}");
    $req->addHeader("Authorization", " Basic ".  base64_encode("{$this->user}:{$this->pwd}"));
    $req->addHeader("Accept", "application/json");
    $req->addHeader("Content-Type", "application/json");
    $req->_allowRedirects=true;
    $response = $req->sendRequest();
    $code = $req->getResponseCode();
    $return['error'] = null;
    if ($code !== 200 && $code !==204) {
      $return['error'] = "Request failed";
    }
    $return['exec'] = $req->getResponseBody();
    // store last response in session
    $_SESSION['last_jira_response']['code'] = $code;
    $_SESSION['last_jira_response']['error'] = $return['error'];
    return $return;

  }

  private function jiraDelete($url){
    return $this->jiraGet($url,"DELETE");
  }

  protected function jiraPost($url,$data,$file=false,$customHeaders=null,$customRequest="POST", $allowSingleValue = false){
    //if not attaching a file then data has to be json encoded
    if (!$file && !$allowSingleValue && !is_object(json_decode($data)) && !is_array(json_decode($data)) )
    {
      throw new Exception("data should be encoded as a json object/array");
    }
    if(!$this->httpRequest) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      if($file){
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      }
      curl_setopt($ch, CURLOPT_VERBOSE, 0);
      if(is_null($customRequest)){
        curl_setopt($ch, CURLOPT_POST, 1);
      }
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $customRequest);

      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,5); 
	  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
      if(is_null($customHeaders)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
      } else {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $customHeaders);
      }
      curl_setopt($ch, CURLOPT_URL, $this->host.$this->jiraAPI.$url);
      curl_setopt($ch, CURLOPT_USERPWD, "{$this->user}:{$this->pwd}");
      $return['exec'] = curl_exec($ch);
      $return['error'] = curl_error($ch);
      // Check for the SSL error
      // error:14077458:SSL routines:SSL23_GET_SERVER_HELLO:reason(1112)
      if( !empty($return['error']) && preg_match('/error\:\d*\:SSL routines\:SSL23_GET_SERVER_HELLO\:reason.*/', $return['error'])) {
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        $return['exec'] = curl_exec($ch);
        $return['error'] = curl_error($ch);
      }
      
      curl_close($ch);
      return $return;
    } else {
      return $this->jiraHttpPost($url,$data,$file,$customHeaders,$customRequest);
    }
  }

  private function jiraHttpPost($url,$data,$file,$customHeaders,$customRequest){

    $req = new HTTP_REQUEST($this->host.$this->jiraAPI.$url,array('allowRedirects'=>true));
    $req->setMethod("{$customRequest}");
    $req->setBasicAuth($this->user, $this->pwd);

    if (empty($customHeaders)){
      $req->addHeader("Accept", "application/json");
      $req->addHeader("Content-Type", "application/json");
    } else {
      foreach ($customHeaders as $customeHeader){
        $part = explode(":",$customeHeader);
        $req->addHeader($part[0],$part[1]);
      }
    }

    if ($file) {
      $result = $req->addFile('file', $data);
    } else {
      $req->addRawPostData($data, true);
    }
    $response = $req->sendRequest();
    $code = $req->getResponseCode();
    $return['error'] = null;
    if ($code !== 200 && $code !== 201 && $code !== 204) {
      $return['error'] = "Request failed";
    }
    $return['exec'] = $req->getResponseBody();
    return $return;
  }

  /**
   * This function will cache the issue on the first call, not querying it again, unless the issueKey changes.
   *
   * @param string $issueKey
   * @param boolean $refresh
   * @return jiraIssue
   */
  function getIssue($issueKey,$refresh = false){
    if (empty($this->issue) || $issueKey !== $this->issueKey || $refresh) {
      $this->issueKey = $issueKey;
      $result = $this->jiraGet("issue/{$issueKey}?expand=transitions.fields");
      $this->issue = $result['exec'];
    }
    if (!empty($result['error'])) {
        $this->error = "<ERROR>Could not query {$issueKey} : {$result['error']}</ERROR>\n";
        $this->issue = null;
        return false;
    } else if (empty($this->error)) {
      $issue=json_decode($this->issue);
      if(!empty($issue->errorMessages)){
        $this->error = "<ERROR>Could not query {$issueKey} : {$issue->errorMessages[0]}</ERROR>\n";
        $this->issue = null;
        return false;
      }
      $issue->fields->id = $issue->id;
      $issue->fields->transitions = $issue->transitions;
      return $issue->fields;
    } else {
      return false;
    }
  }

  /**
   *one call to get the attachment URL, then another to download the file
   * 
   * @param unknown $attachmentId
   * @return boolean|string
   */
  function getAttachment($attachmentId){
    $result = $this->jiraGet("attachment/{$attachmentId}");

    if (!empty($result['error'])) {
        $this->error = "<ERROR>Could not get attachment {$attachmentId} : {$result['error']}</ERROR>\n";
        return false;
    } else if (empty($this->error)) {
      $attachment=json_decode($result['exec']);
      if(!empty($attachment->errorMessages)){
        $this->error = "<ERROR>Could not get attachment {$attachmentId} : {$attachment->errorMessages[0]}</ERROR>\n";
        $this->issue = null;
        return false;
      }
      $context = stream_context_create(array(
          'http' => array(
              'header'  => "X-Atlassian-Token: nocheck \nAuthorization: Basic " . base64_encode("{$this->user}:{$this->pwd}")
          )
      ));
      $contents = file_get_contents($attachment->content, false, $context);

      return $contents;
    } else {
      return false;
    }
  }

  /**
   *
   * @param type $projectKey
   * @return \jiraProjectMeta|boolean
   */
  function getProjectMeta($projectKey){
    require_once dirname ( __FILE__ ) . '/jira/jiraProjectMeta.class.php';
    if (empty($this->projectKey) || $projectKey !== $this->projectKey ) {
      $this->projectKey = $projectKey;
      $result = $this->jiraGet("issue/createmeta?projectKeys={$projectKey}&expand=projects.issuetypes.fields");
      $this->projectMeta = $result['exec'];
    }
    if (!empty($result['error'])) {
        $this->error = "<ERROR>Could not query {$projectKey} Metadata: {$result['error']}</ERROR>\n";
        $this->projectKey = null;
        return false;
    } else if (empty($this->error)) {
      $projectMeta=json_decode($this->projectMeta);
      if(!empty($projectMeta->errorMessages)){
        $this->error = "<ERROR>Could not query {$projectKey} : {$projectMeta->errorMessages[0]}</ERROR>\n";
        $this->projectKey = null;
        return false;
      }
      $qryUsers = $this->jiraGet("user/assignable/search?project={$projectKey}");
      //assuming success as we just queried the whole project meta
      $users=  json_decode($qryUsers['exec']);
      $project = new jiraProjectMeta($projectMeta,$users);
      return $project;
    } else {
      return false;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @return boolean
   */
  function getIssueStatus($issueKey){
    $issue = $this->getIssue($issueKey);
    if (!empty($this->error) && $issue === false) {
      return false;
    } else {
      return $issue->status->name;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @return boolean
   */
  function getIssueOwnerEmail($issueKey){
    $issue = $this->getIssue($issueKey);
    if (!empty($this->error) && $issue === false) {
      return false;
    } else {
      return $issue->reporter->emailAddress;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @return boolean
   */
  function getIssueOwnerName($issueKey){
    $issue = $this->getIssue($issueKey);
    if (!empty($this->error) && $issue === false) {
      return false;
    } else {
      return $issue->reporter->displayName;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @return boolean
   */
  function getIssueType($issueKey){
    $issue = $this->getIssue($issueKey);
    if (!empty($this->error) && $issue === false) {
      return false;
    } else {
      return $issue->issuetype->name;
    }
  }

  /**
   * 
   * @return Ambigous <string, mixed>|boolean
   */
  function getError(){
    if(!empty($this->error)){
      return $this->error;
    } else {
      return false;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @param unknown $comment
   * @param string $restrictedTo
   * @param string $groupVisibility
   * @return boolean|string
   */
  function addCommentToIssue($issueKey,$comment,$restrictedTo="Developers",$groupVisibility=false){
    $data=  json_encode($this->makeCommentObject($comment, $restrictedTo, $groupVisibility));
    $result = $this->jiraPost("issue/{$issueKey}/comment", $data);
    if ($result['error']) {
      $this->error = "<ERROR> Could not post comment on {$issueKey}: {$result['error']} </ERROR>";
      return false;
    } else {
        return $result['exec'];
    }
  }

  /**
   *
   * @param string $comment
   * @param string $restrictedTo
   * @param boolean $groupVisibility
   * @return array() comment object ready to be jsoned
   */
  protected function makeCommentObject($comment,$restrictedTo,$groupVisibility,$updateAdd=false){
    $base = array("body"=>$comment,"visibility"=>array("type"=>$groupVisibility?"group":"role","value"=>$restrictedTo));
    if (empty($restrictedTo)) {
      $base = array("body"=>$comment);
    }
    if ($updateAdd){
      $base = array(array("add"=>$base));
    }
    return $base;
  }

  /**
   *
   * @param jiraIssue $issue
   * @return boolean
   */
  function createIssue(&$issue,$issueType='Problem Report'){
    $meta = $this->getProjectMeta($issue->getProjectKey());
    $checkIssueType = $issue->getIssueType();
    if (!empty($checkIssueType)){
      $issueType = $checkIssueType;
    }
    $data = json_encode( $issue->getIssueObject($meta->getFieldList($issueType),false));
    $result = $this->jiraPost("issue", $data);
    if ($result['error']) {
      $this->error = "<ERROR> Could not create issue in project {$issue->getProjectKey()}: {$result['error']} </ERROR>";
      return false;
    }
    $decoded=  json_decode($result['exec']);
    if(empty($decoded->key)) {
      if (!empty($decoded->errors)){
        $this->error = var_export($decoded->errors,true);
      }  else {
        $this->error = $decoded;
      }
      return false;
    }
    $issue->setIssueKey($decoded->key);
    return true;
  }

  /**
   *
   * @param jiraIssueKey $firstIssueKey
   * @param jiraIssueKey $secondIssueKey
   * @param jiraLinkType $issueLinkType
   * @param string $linkComment: textual comment added to the $firstIssue
   * @return boolean
   */
  function createIssueLink($firstIssueKey, $secondIssueKey, $issueLinkType, $linkComment=''){
    $data = array();
    $data["type"] = array("name" => $issueLinkType);
    $data["inwardIssue"] = array("key" => $firstIssueKey);
    $data["outwardIssue"] = array("key" => $secondIssueKey);
    if(strlen($linkComment)) {
      $data["comment"] = array("body" => $linkComment);
    }

    $result = $this->jiraPost("issueLink", json_encode($data, false));
    if ($result['error']) {
      $this->error = "<ERROR> Could not create issue link: {$result['error']} </ERROR>";
      return false;
    }

    return true;
  }

  /**
   * 
   * @param unknown $issueKey
   * @param unknown $filePath
   * @param unknown $fileName
   * @return boolean
   */
  function attachFileToIssue($issueKey,$filePath,$fileName){
    if (!file_exists($filePath)) {
      $this->error = "<ERROR> Could not find File {$filePath} </ERROR>";
      return false;
    }

    if ((version_compare(PHP_VERSION, '5.5') >= 0)) {
      $cfile = new CURLFile($filePath);
      $cfile->setPostFilename($fileName);
      $data['file'] = $cfile;
    } else {
      $data =  array('file'=>"@{$filePath} ;filename={$fileName}");
    }
    if ($this->httpRequest) {
      $data = $filePath;
    }
    $header = array('X-Atlassian-Token: nocheck');
    $result = $this->jiraPost("issue/{$issueKey}/attachments", $data, true, $header);
    return $this->encodedResultAsBoolean($result, "Could not attach File {$fileName} comment on {$issueKey} ");
  }

  /**
   * 
   * @param unknown $issueKey
   * @return boolean
   */
  function deleteIssue($issueKey){
    $result = $this->jiraDelete("issue/{$issueKey}?deleteSubtasks=true");
    return $this->encodedResultAsBoolean($result, "Could not delete {$issueKey} ");
  }

  /**
   * 
   * @param unknown $projectkey
   * @param number $startAt
   * @param number $maxResults
   * @param string $fields
   * @param string $customJql
   * @return boolean
   */
  function getProjectIssues($projectkey,$startAt=0,$maxResults=50,$fields=null,$customJql=null){
    ///search?jql=project="{$projectkey}"
    $jql = "jql=project=\"{$projectkey}\"";
    if (!is_null($customJql)){
      $jql=$customJql;
    }
    $url = "search?{$jql}&startAt={$startAt}&maxResults={$maxResults}" . (!empty($fields)?"&fields=".implode(",",$fields):"");
    $jiraResult= $this->jiraGet($url);
    if (!empty($jiraResult['error'])) {
        $this->error = "<ERROR>Could not query {$projectkey} Issues : {$jiraResult['error']}</ERROR>\n";
        return false;
    } else if (empty($this->error)) {
      $result=json_decode($jiraResult['exec']);
      if(!empty($result->errorMessages)){
        $this->error = "<ERROR>Could not query {$projectkey} Issues: {$result->errorMessages[0]}</ERROR>\n";
        return false;
      }
      if( empty($result) ) {
        return false;
      }
      return $result->issues;
    } else {
      return false;
    }
  }

  /**
   * 
   * @param unknown $issueKey
   * @param unknown $data
   * @return boolean
   */
  function updateIssue($issueKey,$data){
    $result = $this->jiraPost("issue/{$issueKey}", $data,false,null,"PUT");
    return $this->encodedResultAsBoolean($result, "Could not update {$issueKey} ");
  }

  /**
   *  generic update of an issue using the same method as a create.
   *  can also include a transition in case the transition has mandatory fields update
   *
   * @param string $issueKey
   * @param jiraIssue $issue
   * @param boolean $transition
   */
  function updateFromGet($issueKey,$data){
    //get the fieldList
    //issue key is already known
    $this->updateIssue($issueKey, json_encode($data));
  }

  //one call to get the attachment URL, then another to download the file
  function removeAttachment($attachmentId){
    $result = $this->jiraGet("attachment/{$attachmentId}","DELETE");
    return $this->encodedResultAsBoolean($result, "Could not delete attachment {$attachmentId} ");
  }

  /**
   *
   * @param string $issueKey
   * @param type $transitionId
   * @param type $data additional object to push with transition (comment, issue fields)
   * @return boolean
   */
  function transitionIssue($issueKey,$transitionId,$data=null){
    if(empty($data)){
      $data = json_encode($this->makeTransitionObject($transitionId));
    } else {
      $data = json_encode(array_merge($data,$this->makeTransitionObject($transitionId) ));
    }
    $result = $this->jiraPost("issue/{$issueKey}/transitions", $data);
    return $this->encodedResultAsBoolean($result, "Could not transition issue in project ");
  }

  protected function makeTransitionObject($transitionId){
    return array("transition"=> array("id" => $transitionId));
  }

  /**
   * Sends a notification to a specific jira user for a specific issue
   *
   * @param type $user
   * @param type $issue
   * @param type $message
   */
  function pushNotificationToUser($user,$issueKey,$messageTitle, $messageBody){
    $message = array(
      "subject"=>"{$messageTitle}",
      "textBody"=>"{$messageBody}",
      "htmlBody"=>"{$messageBody}",
       "to"=>array(
          "reporter"=>false,
          "assignee"=>false,
          "users"=>array(array(
            "name"=>"{$user}",
            "active"=>true
          )
        )
      )
    );
    $result = $this->jiraPost("issue/{$issueKey}/notify", json_encode($message));
    return $this->encodeResult($result, "Could send notifification for issue {$issueKey}");
  }

  protected function encodeResult($result,$errorMsg){
    $error ='';
    if ($result['error']) {
      $error = $this->error = "<ERROR> {$errorMsg} : {$result['error']} </ERROR>";
    } else {
      $decoded=  json_decode($result['exec']);
      if (!empty($decoded->errorMessages)){
       $error =  $this->error ="<ERROR> {$errorMsg} : {$decoded->errorMessages[0]}</ERROR>\n";
      } else  if (!empty($decoded->errors)){
       $error =  $this->error = var_export($decoded->errors,true);
      }
    }
    return array("result"=>$decoded,"error"=>$error);
  }

  protected function encodedResultAsBoolean($result,$errorMessage){
    $temp = $this->encodeResult($result, $errorMessage);
    if(!empty($temp['error'])){
      return false;
    }
    return true;
  }

  /**
   * takes the output from the create Issue and return a subtask key based on the needle searched in the title
   *
   * @param type $issue
   * @param type $needle
   * @return string
   */
  function extractSubtaskFromCreatedIssue($issueKey,$needle){
    $foundIssueKey='';
    $issue= $this->getIssue($issueKey,true);
    if(!empty($issue->subtasks)){
      foreach($issue->subtasks as $subTask){
        if (stripos($subTask->fields->summary, $needle) !== FALSE) {
          $foundIssueKey= $subTask->key;
          break;
        }
      }
    }
    return $foundIssueKey;
  }

  /**
   *
   * @param string $issueKey
   * @param string $fieldName
   * @param string $fieldValue
   * @param jiraProjectMeta $projectMeta
   */
  function jiraUpdateSingleField($issueKey,$fieldName, $fieldValue,$projectMeta){
    $tempIssue = $this->getIssue($issueKey);//to reload data
    $fieldList = $projectMeta->getFieldList($this->getIssueType($issueKey));
    $issueToUpdate = new jiraIssue();
    $issueToUpdate->setIssueKey($issueKey)
        ->setField($projectMeta->fieldListLookup[$fieldName], $fieldValue);
    $data = json_encode($issueToUpdate->getIssueObject($fieldList));
    return $this->updateIssue($issueKey, $data);
    //create issue object using getIssue without a refresh
    //feed issueKey with encoded field to update it
  }

  function addWatcher($issueKey,$username){
    $result = $this->jiraPost("issue/{$issueKey}/watchers",  json_encode($username),false,null,'POST',true);
    return $this->encodeResult($result, "Could not add {$username} as a watcher on {$issueKey}");
  }


  function jiraSearchForUsername($userName){
    $user = '';
    $error = '';
    $result = $this->jiraGet("user/search?username={$userName}&startAt=0&maxResults=50");
    $success = $this->encodedResultAsBoolean($result, "failed to find {$userName}");
    $users= json_decode($result['exec']);
    if($success && !empty($users) ){
      $user = $users[0]->name;
    } else {
      $error = $this->error;
    }
    return array("jira_user"=>$user,"error"=>$error);
  }

  /**
   * 
   * @param unknown $userEmail
   * @return multitype:string Ambigous <string, mixed>
   */
  function jiraSearchForUserEmail($userEmail){
    return $this->jiraSearchForUsername($userEmail);
  }

  /**
   * 
   * @param unknown $jql
   * @param number $startAt
   * @param number $maxResults
   * @param string $fields
   * @return boolean|mixed
   */
  function getPagedReport($jql,$startAt=0,$maxResults=50,$fields=null){
    $url = "search?{$jql}&startAt={$startAt}&maxResults={$maxResults}" . (!empty($fields)?"&fields=".implode(",",$fields):"");
    $jiraResult= $this->jiraGet($url);
    if (!empty($jiraResult['error'])) {
        $this->error = "<ERROR>Could get Report</ERROR>\n";
        return false;
    } else if (empty($this->error)) {
      $result=json_decode($jiraResult['exec']);
      if(!empty($result->errorMessages)){
        $this->error = "<ERROR>Could not get Report</ERROR>\n";
        return false;
      }
      if( empty($result) ) {
        return false;
      }
      return $result;
    } else {
      return false;
    }
  }

  protected function setRestAPI($api){
    $this->jiraAPI="$api";
  }
  /**
   *
   * @param string $cid
   * @param string $status
   * @return array with ['exec'] and ['error'] entries
   * Changes the account status in JIRA. This drives the yellow banner in the JIRA world.
   */
  function setAccountStatus($cid, $status){
    $this->setRestAPI("rest/tacresthold/1.0/");
    $data=  array();
    $status = urlencode($status);
    $result = $this->jiraPost("status?key={$cid}&status={$status}", $data, false, null, "PUT",true);
    return $result;
  }

   /*
   * @param string $cid
   * @return string value of JIRA account status
   */
  function getAccountStatus($cid){
    $this->setRestAPI("rest/tacresthold/1.0/");
    $data=  array();
    $result = $this->jiraPost("status?key={$cid}", $data, false, null, "GET",true);
    return $result['exec'];
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
}
