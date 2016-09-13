<?php
/**
 * Description of jiraIssue
 *
 * @author arochereau
 */

class jiraIssue{
  private $issueKey;
  private $fields = array(); // holds the fields setup in the current issue
  private $fieldList; // this is the list of fields available for the current project/issue type

  CONST ISSUETYPE_CHANGE_REQUEST = "Change Request";
  CONST ISSUEPRIORITY_MAJOR = "Major";
  CONST ISSUEPRIORITY_MEDIUM = "Medium";

  /**
   * Class Constructor
   */
  function __construct() {
   }

  /**
   *
   * @return type
   */
  function getTitle(){
    return $this->fields['summary'];
  }

  /**
   *
   * @param string $title
   * @return \jiraIssue
   */
  function setTitle($title){
    $this->fields['summary'] = $title;
    return $this;
  }

  /**
   *
   * @return type
   */
  function getDescription(){
    return $this->fields['description'];
  }
  /**
   *
   * @param string $description
   * @return \jiraIssue
   */
  function setDescription($description){
    $this->fields['description'] = $description;
    return $this;
  }

  /**
   *
   * @return type
   */
  function getIssueType(){
    return $this->fields['issuetype'];
  }

  /**
   *
   * @param type $issueType
   * @return \jiraIssue
   */
  function setIssueType($issueType){
    $this->fields['issuetype'] = $issueType;
    return $this;
  }

  /**
   *
   * @return type
   */
  function getIssueKey(){
    return $this->issueKey;
  }
  /**
   *
   * @param string $issueKey
   * @return \jiraIssue
   */
  function setIssueKey($issueKey){
    $this->issueKey = $issueKey;
    return $this;
  }
  /**
   *
   * @param string $projectKey
   * @return \jiraIssue
   */
  function setProjectKey($projectKey){
    $this->fields['project'] = $projectKey;
    return $this;
  }

  /**
   *
   * @return type
   */
  function getProjectKey(){
    return $this->fields['project'];
  }

  /**
   *
   * @param type $priority
   * @return \jiraIssue
   */
  function setPriority($priority){
    $this->fields['priority']=$priority;
    return $this;
  }

  /**
   *
   * @return type
   */
  function getPriority(){
    return $this->fields['priority'];
  }

  /**
   *
   * @param string $name
   * @param type $value
   * @return \jiraIssue
   */
  function setField($name, $value){
    $this->fields[$name]=$value;
    return $this;
  }

  /**
   *
   * @param string $name
   * @return boolean
   */
  function getField($name){
    if (isset($this->fields[$name])){
      return $this->fields[$name];
    }
    return false;
  }

  /**
   * allow for fields removal before an update/etc...
   *
   * @param type $name
   * @return \jiraIssue
   */
  function unsetField($name){
    unset($this->fields[$name]);
    return $this;
  }

  /**
   *
   * @param type $fieldList
   * @return \jiraIssue
   */
  function setFieldList($fieldList){
    $this->fieldList = $fieldList;
    return $this;
  }

  /**
   * Sets a field trying to match value to the options allowed for the field
   * if no match it uses the default value
   *
   * @param type $name
   * @param type $value
   * @param type $default
   * @return \jiraIssue
   */
  function setFieldWithDefault($name, $value, $default){
    $options = $this->getFieldOptions($name);
    if(!empty($options)){
      foreach($options as $option) {
        if($option['value'] === $value) {
          $this->setField($name, $value);
          return $this;
        }
      }
    }
    $this->setField($name, $default);
    return $this;
  }

  /**
   * Sets a field trying to match value to the options allowed for the field
   * if no match it does not set anything
   *
   * @param type $name
   * @param type $value
   * @param type $matchBeginsWith tries to use the provided value as a partial match on the beginning of the option value
   * @return \jiraIssue
   */
  function setFieldIfFound($name, $value ,$matchBeginsWith=false){
    $options = $this->getFieldOptions($name);
    if (!empty($options)){
      foreach($options as $option) {
        if($option['value'] === $value) {
          $this->setField($name, $value);
          return $this;
        }
        if($matchBeginsWith && $value === substr($option['value'], 0, strlen($value))){
          $this->setField($name, $option['value']);
          return $this;
        }
      }
    }
    return $this;
  }

  /**
   * Helper function, gets the list of options from the field list
   * field list has to be set before else it will return an empty array
   *
   * @param type $name
   * @return type
   */
  private function getFieldOptions($name){
    foreach($this->fieldList as $fieldId=>$field){
      if($fieldId === $name && !empty($field['options'])){
        return $field['options'];
      }
    }
    return array();
  }

  /**
   * when get an issue for update, the system will walk over the fields to only package updatable fields.
   *
   * @param jiraProjectMeta->getFieldList $fieldList
   * * @param boolean $forUpdate
   * @return type
   * @throws Exception
   */
  function getIssueObject($fieldList,$forUpdate=false){
    $fields=array();

    foreach($this->fields as $fieldName=>$fieldValue) {
      if ( $fieldName ==='component'  || $fieldName ==='Component/s'){
        $fieldName ='components';
      }
      if  (!empty($fieldValue)){
        /*  ^                          ^
         * /!\ handle multiple values /!\
         * TTT                        TTT
         *
         * CascadingSelectField: customfield_10001": {"value": "green", "child": {"value":"blue"} }
         * DatePickerField: "customfield_10002": "2011-10-03" The format is: YYYY-MM-DD
         * DateTimeField: "customfield_10003": "2011-10-19T10:29:29.908+1100" This format is ISO 8601: YYYY-MM-DDThh:mm:ss.sTZD
         * FreeTextField: "customfield_10004": "Free text goes here.  Type away!"
         * GroupPicker: "customfield_10005": { "name": "jira-developers" }
         * Labels: "customfield_10006": ["examplelabelnumber1", "examplelabelnumber2"]
         * MultiGroupPicker: "customfield_10007": [{ "name": "admins" }, { "name": "jira-developers" }, { "name": "jira-users" }]
         * MultiSelect: "customfield_10008": [ {"value": "red" }, {"value": "blue" }, {"value": "green" }]
         * MultiUserPicker: "customfield_10009": [ {"name": "jsmith" }, {"name": "bjones" }, {"name": "tdurden" }]
         * NumberField: "customfield_10010": 42.07
         * ProjectPicker: "customfield_10011": { "key": "JRADEV" } You can also specify the project by project ID { "id":"10000" }
         * RadioButtons: "customfield_10012": { "value": "red" } You can also specify the selection by id
         * SelectList: "customfield_10013": { "value": "red" } You can also specify the selection by id
         * SingleVersionPicker: "customfield_10014": { "name": "5.0" } You can also specify the version by id
         * TextField: "customfield_10015": "Is anything better than text?"
         * URLField: "customfield_10016": "http://www.atlassian.com"
         * UserPicker: "customfield_10017": { "name":"brollins" }
         * VersionPicker: "customfield_10018": [{ "name": "1.0" }, { "name": "1.1.1" }, { "name": "2.0" }]
         *
         * json_encode will add square brackets around an array for a key ordered only, so just do array(array())
         * with either array(arra( when square brackets are required or just array( when not */

        //project key or issue type should not be set during a generic update
        if(!$forUpdate || ( $forUpdate && !in_array($fieldName, array('project','issuetype')))){
          $fields[$fieldName] = $this->encodeField($fieldName,$fieldValue,$fieldList[$fieldName],$forUpdate);
        }
      }
    }
    $return['fields'] =  $fields;
    return $return;
  }

  /**
   *
   * @param type $fieldName
   * @param type $fieldValue
   * @param type $fieldMeta
   * @param boolean $forUpdate if set to true the function will decode the type of field to set the correct operation
   * @return type
   */
  private function encodeField($fieldName,$fieldValue,$fieldMeta, $forUpdate=false){
    if(isset($fieldMeta['schema']->type) && $fieldMeta['schema']->type === 'user'){
          $encodedField=array("name"=>$fieldValue);
    }else if (is_array($fieldValue)){ // case of a cascading field or multiple select
      if (!isset($fieldValue['parent'])) {//mutliple select
        $temp= array();
        foreach ($fieldValue as $type => $value){
          if(is_array($value)){ //value is already encoded as type => value
            $temp[] = $value;
          }else {
            $temp[]=array($type=>(string)$value);
          }
        }
        $encodedField = $temp;
      } else {
        $encodedField=array((is_int($fieldValue['parent'])?"id":"value")=>$fieldValue['parent'],"child"=>array((is_int($fieldValue['child'])?"id":"value")=>(string)$fieldValue['child']));
      }
    } else if(isset($fieldMeta['options']) && !in_array($fieldName,array('components','issuetype','priority','project'))){
      //if field has options then encode value as name => value
      $encodedField=array(((is_int($fieldValue))?"id":"value")=>(string)$fieldValue);
    }else{
      //we now do a switch case based on field name to detect system fields and encode them properly
      switch ($fieldName) {
        case 'components':
        case 'component':
        case 'Component/s':
          $encodedField=array(array(((is_int($fieldValue))?"id":"name")=>$fieldValue));
          break;
        case 'priority':
          $encodedField=array(((is_int($fieldValue))?"id":"name")=>$fieldValue);
          break;
        case 'project':
          $encodedField=array("key"=>$fieldValue);
          break;
        case 'issuetype':
          $encodedField=array("name"=>$fieldValue);
          break;
        case 'summary':
          $encodedField=$fieldValue;
          break;
        case 'description':
          $encodedField=$fieldValue;
          break;
        default:
          $encodedField=$fieldValue;
          break;
      }
    }
    return $encodedField;
  }

}
