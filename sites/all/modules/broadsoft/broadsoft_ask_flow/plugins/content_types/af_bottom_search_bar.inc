<?php

$plugin = array(
  'single' => TRUE,
  'title' => t('Bottom search bar'),
  'description' => t('Broadsoft Ask Flow bottom search bar'),
  'category' => t('Broadsoft panes'),
  'edit form' => 'af_bottom_bar_edit_render',
  'render callback' => 'af_bottom_bar_render',
  'defaults' => array(
  ),
  'all contexts' => TRUE,
);

function af_bottom_bar_edit_render($form, $form_state) {
  $form['title'] = array(
    '#markup' => t('Ticket Search form'),
  );
  return $form;
}

function af_bottom_bar_render($subtype, array $conf = array(), $args, $context) {
  // Render as a block.
  $block = new stdClass();
  $block->module = 'broadsoft_ask_flow';
  $block->content = af_bottom_bar_markup($context['argument_entity_id:node_1']->data->nid);
  return $block;
}

function af_bottom_bar_markup($cur_nid) {
  if(isset($_SESSION['ask_flow']['search_term'])) {
    $results = $_SESSION['ask_flow']['top_results'];
    $skip = 0;
    $next_nid = 0;
    $prev_nid = 0;
    foreach($results as $k => $result) {
      if($skip == 1) {
        $next_nid = $k;
        break;
      }
      if($k == $cur_nid) {
        $skip++;
      }
      else {
        $prev_nid = $k;
      }
    }
    $prev_link = '';
    if($prev_nid > 0) {
      $prev_title = db_query("SELECT title from node WHERE nid = :nid", array(':nid' => $prev_nid))->fetchField();
      $prev_link = '<a href="'.drupal_get_path_alias(base_path().'node/'.$prev_nid).'" class="prev-article">
                    <span><i class="fa fa-angle-left" aria-hidden="true"></i>Prev Article</span>
                    <span>'.$prev_title.'</span>
                    </a>';
    }

    $next_link = '';
    if($next_nid > 0) {
      $next_title = db_query("SELECT title from node WHERE nid = :nid", array(':nid' => $next_nid))->fetchField();
      $next_link = '<a href="'.drupal_get_path_alias(base_path().'node/'.$next_nid).'" class="prev-article">
                    <span><i class="fa fa-angle-right" aria-hidden="true"></i>Next Article</span>
                    <span>'.$next_title.'</span>
                    </a>';
    }
    $ret = '<div class="arrow-dropdown" data-toggle="collapse" data-target="#collapseItem"></div>
            <div id="collapseItem" class="load-control collapse in">
          <div class="col-sm-3 col-md-3">
            <a href="#" class="return-search"><i class="fa fa-angle-left" aria-hidden="true"></i>Return to Search Results</a>
          </div>
          <div class="col-sm-3 col-md-3">
            '.$prev_link.'
          </div>
          <div class="col-sm-3 col-md-3">
            '.$next_link.'
          </div>
          <div class="col-sm-3 col-md-3">
            <a href="#" title="Ask Community" class="btn type-none-background color-green pull-left"><span class="community-icon-green"></span>Ask Community</a>
          </div>
        </div>';
  }
  else
    $ret = '';
  return $ret;
}