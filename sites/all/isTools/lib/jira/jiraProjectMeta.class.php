<?php
/**
 * Description of jiraProjectMeta
 * This is an object holding a project metadata and its internal relations
 *
 * @author arochereau
 */

class jiraProjectMeta{
  private $projectKey;
  private $issueTypes = array();
  private $issueTypesList = array();
  private $meta;
  private $users;
  public $fieldListLookup = array();

  /**
  *
  */
  function __construct($meta,$users) {
    $this->meta = $meta->projects[0];
    $this->setProjectKey($this->meta->key);
    $this->setIssueTypes();
    $this->setProjectUsers($users);
    //var_dump($this->meta->issuetypes[0]->fields);
    unset($this->meta);
   }

  private function setProjectKey($projectKey){
    $this->projectKey = $projectKey;
    return $this;
  }

  private function setProjectUsers($users){
    foreach($users as $user){
      if ($user->active){
        $this->users[$user->key]=array("name"=>$user->name,
                                     "email"=>$user->emailAddress,
                                     "fullName"=>$user->displayName);
      }
    }
  }

  /**
   * returns an array of Active users keyed by the user id (for assignments etc..)
   *
   * @return array
   */
  function getProjectUsers(){
    return $this->users;
  }

  function getProjectKey(){
    return $this->projectKey;
  }

  function getFieldList($issueType){
    if(!empty($this->issueTypesList) && !empty($this->issueTypes) && isset($this->issueTypes[$this->issueTypesList[$issueType]]['fields'])){
      return $this->issueTypes[$this->issueTypesList[$issueType]]['fields'];
    }
    return false;
  }

  private function setFieldList(&$parent,$fields){
    $duplicateFields=array();//outdated not used anymore
    /**
     * if type not string or integer or datetime etc.. but user/security/blabla
     * then we get the custom type options into an array to be reused when assigning.
     *
     */
    foreach($fields as $fieldMeta=>$field){
      $this->fieldListLookup[$field->name]=$fieldMeta;
      if (!in_array($field->name,$duplicateFields)){
        $parent['fields'][$fieldMeta]=array('name'=>$field->name,
                                            "schema"=>$field->schema,
                                             "operations"=>$field->operations);
        if(!empty($field->allowedValues)){
          $this->setOptions($parent['fields'][$fieldMeta],$field->allowedValues);
        }
      }
    }
  }

  function getChildrenField(){
  }

  private function setOptions(&$parent,$options,$children=false){
    //regular add, then recursive call
    foreach($options as $id=>$option){
      if(!$children){
        $parent['options'][$option->id]=array("value"=>(isset($option->value)?$option->value:$option->name),
                                            "id"=>$option->id);
      }else {
        $parent[$option->id]=array("value"=>(isset($option->value)?$option->value:$option->name),
                                            "id"=>$option->id);
      }
      if(isset($option->children)){
        $this->setOptions($parent['options'][$option->id]['children'], $option->children,true);
      }
    }
  }

  private function setIssueTypes(){
    if(!isset($this->meta->issuetypes)){
      return false;
    }
    foreach($this->meta->issuetypes as $issueType){
      $this->issueTypesList[$issueType->name]=$issueType->id;
      $this->issueTypes[$issueType->id]=array('name'=>$issueType->name,
                                              'isSubtask'=>$issueType->subtask);
      $this->setFieldList($this->issueTypes[$issueType->id],$issueType->fields);
    }
    return true;
  }

  function getIssueTypes(){
    return array_flip($this->issueTypesList);
  }

}
