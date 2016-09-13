<?php

/**
 * @file
 *   Views query plugin for the Ticketing Pagination and JIRA result.
 */
/**
 * Implementation of hook_views_default_views().
 */


/**
 * Views query plugin for the Ticketing Pagination.
 */
class broadsoft_ticketing_pagination_plugin_query extends views_plugin_query {
  
  protected $user_filter= null;
  

  function query($get_count = FALSE) {  }

  /**
   * 
   * @param view $view
   */
  function execute(&$view) {
    // Setup pager
    $forExport=false;
    $view->init_pager();
    if ($this->pager->use_pager()) {
      $this->pager->set_current_page($view->current_page);
    }
    if(stripos($view->current_display,"export") !== FALSE) {
      $forExport=true;
    }
    //if export view, then export the whole query using a paging loop to jira.
    //obtain the current tab as a status: pending, open and closed
    //$tab = filter_input(INPUT_GET, 'tab', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW);
    $tab = $this->options['status'];
    //initialize the deafault load of the current tab
    $current_tab = (isset($tab) && !empty($tab)) ? $tab : 'pending';
    //set the current page from the views' GET
    $page_get = (int) filter_input(INPUT_GET, 'page', FILTER_SANITIZE_NUMBER_INT);
    if(empty($page_get)){ //seems to be the prefered way in drupal 7
      $page_get = (int) filter_input(INPUT_POST, 'page', FILTER_SANITIZE_NUMBER_INT);
    }
    $current_page = isset($page_get) ? $page_get : 0;
    $check = $view->query->pager;//$view->display_handler->get_option('pager');
    //read the view setting for the items per page or default to 50
    $items_per_page = !empty($check->options['items_per_page']) ? $check->options['items_per_page'] : 50;
    
    //get sort direction
    $sort = isset( $_GET['sort']) ? htmlspecialchars($_GET['sort']) : "desc";
    
    //get the sort field to order by
    $order_by = isset( $_GET['order']) ? htmlspecialchars($_GET['order']) : "created_on";
  
    //set the pager with the total number of tasks of the current status
    $this->pager->total_items = getJiraListResults($current_tab,0,1,null,null,true);
    $this->pager->items_per_page = $items_per_page;
    $this->pager->current_page = $current_page ;
    //obtain the results form the jira jql
    $view->result = self::getJiraResult($current_page, $current_tab , $this->pager->items_per_page, $sort, $order_by,$forExport,$this->pager->total_items);

    $this->pager->update_page_info();
    
  }
  
  function add_orderby(){
  }
  
  function getJiraResult($current_page=0, $status='open', $items_per_page=10, $sort='asc', $order_by='id',$forExport=false,$totalItems=0){
  
    $tasks[] = array();
    $startAt = ($current_page) * $items_per_page;
    $lastPage = ceil($totalItems/$items_per_page);
    //query jira api to receive the tasks
    for($i=1; $i<=$lastPage;$i++){
      $result = getJiraListResults($status,$startAt,$items_per_page,$order_by,$sort);
      foreach($result as $jira_task=>$task_row){
        //to confirm, unless we add new fields, this is status only
        $update_date_key = 'TIMESTAMP';
        $row = new stdClass;
        $row->id = $jira_task;
        $row->title = $task_row['SHORT_DESCR'];
        $row->contact = $task_row['CONTACT_NAME'];
        $row->status = $task_row['STATUS'];
        $row->created_on = !empty($task_row['DATE_CREATED'])?date("m/d/y h:i A", strtotime($task_row['DATE_CREATED'])):'';
        $row->updated_on = !empty($task_row[$update_date_key])?date("m/d/y h:i A", strtotime($task_row[$update_date_key])):'';
        $row->severity_level = $task_row['SEVERITY_LEVEL'];
        $tasks[] = $row;
      }
      if(!$forExport){
        break;
      }
      $startAt = $i*$items_per_page;
    }
    return $tasks;
  }
  
  function add_relationship($alias, $join, $base, $link_point = NULL){
  }
  
  function add_where($group, $clause) {
  }

  function option_definition() {
    $options = parent::option_definition();

    $options['status'] = array(
      'default' => 'pending',
    );

    return $options;
  }

  function options_form(&$form, &$form_state) {
    $form = parent:: options_form($form, $form_state);

    $form['status'] = array(
      '#type' => 'textfield',
      '#title' => t('Ticket status'),
      '#description' => t('The status of the ticket (pending/open/closed/cuser).'),
      '#default_value' => $this->options['status'],
    );
  }
  
}
