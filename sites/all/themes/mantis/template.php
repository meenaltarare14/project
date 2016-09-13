<?php
/*
  Preprocess
*/


function mantis_preprocess_html(&$vars) {
  // adding template name to body class
  $vars['classes_array'][] = "layout-2";
}


function mantis_preprocess_page(&$vars) {
  $path = request_path();
  $vars['path'] = $path;
  if($path == 'user/login') {
    $vars['user_login_form'] = drupal_get_form('user_login_block');
    drupal_add_css(drupal_get_path('theme', 'mantis') . "/css/login.css", array('group' => CSS_THEME));
    return;
  }
  // default forms for all pages rendered in tpl files
  $vars['search_form'] = drupal_get_form('search_form');//drupal_get_form('broadsoft_files_search_form');
  $vars['product_info'] = drupal_get_form('product_information_form');
  $vars['customer_groups'] =  drupal_get_form('customer_group_selection');
  if($vars['is_front']) {
    // Add only to front page(required) as scroll listener is intensive
    drupal_add_js("
    jQuery(document).ready(function($) {
    $(window).load(function() {
    $('.current-sub-nav').hide();
    // show menu after some scroll
    $(window).scroll(function () {
        if( $(window).scrollTop() > $('.main-banner').offset().top + 180){
            $('.current-sub-nav').slideDown('fast');
        } else if ($(window).scrollTop() < $('.main-banner').offset().top + 180){
            $('.current-sub-nav').slideUp('fast');
        }
    });
    });
}( jQuery ));", 'inline');

    $form = $vars['search_form'];
    $form['basic']['keys']['#title'] = t('Search');
    $form['basic']['keys']['#title_display'] = 'invisible';
    $form['basic']['keys']['#attributes']['class'] = array("form-control", "search-query");
    $form['basic']['keys']['#attributes']['placeholder'] = t('Ask your question...');
    $form['#attributes']['class'] = array("form-search", "form-inline");
    $form['basic']['submit']['#attributes']['class'] = array('btn', 'search-button');
    $form['basic']['submit']['#value'] = decode_entities('&#xe003;');
    $vars['broadsoft_files_search'] = $form;
    $vars['search_form'] = drupal_get_form('search_form');
    return;
  }
  if($path == 'support/ticketing/administer_users') {
    //TODO expose this is a custom pane
    $vars['page']['content']['system_main']['main']['#markup'] .= BTI_manage_account_users();
    return;
  }
  if(strpos($path, 'ticketing') !== false && count(page_manager_get_current_page())) {
    $vars['theme_hook_suggestions'][] = 'page__panels__ticketing';
  }
  else if(count(page_manager_get_current_page())){
    // common tpl file for ticketing panel pages
    $vars['theme_hook_suggestions'][] = 'page__panels';
  }
  if (isset($vars['node']->type)) {
        $nodetype = $vars['node']->type;
        $vars['theme_hook_suggestions'][] = 'page__' . $nodetype;
  }	

}

function mantis_process_html(&$vars) {
 if(isset($vars['page_top'])) {
  unset($vars['page_top']);
 }
}

function mantis_ctools_plugin_directory($module, $plugin) {
  if (($module == 'ctools') && ($plugin == 'content_types')) {
    return 'plugins/content_types';
  }
}

function mantis_theme() {
  $items['front_banner_block'] = array(
    'render element' => 'data',
    'template' => 'plugins/templates/banner'
  );
  $items['ticketing_modal_form'] = array(
    'render element' => 'form',
    'template' => 'ticketing-modal',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['ticketing_no_modal_form'] = array(
    'render element' => 'form',
    'template' => 'ticketing-create-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['ticket_submit_message'] = array(
    'render element' => 'fieldset',
    'template' => 'ticket-submit-message',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['ticketing_existing_form'] = array(
    'render element' => 'form',
    'template' => 'ticketing-existing',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['views_exposed_form__broadsoft_content_search'] = array(
    'render element' => 'form',
    'template' => 'search-results-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['user_login_block'] = array(
     'template' => 'templates/user-login-block',
     'render element' => 'form',
   );
  $items['confirm_modal_fieldset'] = array(
    'render element' => 'fieldset',
    'template' => 'confirm-modal-fieldset',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['thankyou_modal_fieldset'] = array(
    'render element' => 'fieldset',
    'template' => 'thankyou-modal-fieldset',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['feedback_modal_fieldset'] = array(
    'render element' => 'fieldset',
    'template' => 'feedback-modal-fieldset',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['feedback_radios'] = array(
    'render element' => 'radios',
    'template' => 'feedback-satisfaction-radios',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['dropzone_attachment_container'] = array(
    'render element' => 'container',
    'template' => 'dropzone-attachment-container',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['product_information_form'] = array(
    'render element' => 'form',
    'template' => 'product-information-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
   $items['ticket_search_form'] = array(
     'render element' => 'form',
     'template' => 'ticket-search-form',
     'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
   );
  $items['ticket_search_checkboxes'] = array(
    'render element' => 'checkboxes',
    'template' => 'ticket-search-checkboxes',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['ticket_search_results'] = array(
    'render element' => 'markup',
    'template' => 'ticket-search-results',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['views_exposed_form__customer_accounts'] = array(
    'render element' => 'form',
    'template' => 'customer-accounts-exposed-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['views_exposed_form__documentation_index'] = array(
    'render element' => 'form',
    'template' => 'documentation-index-exposed-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['af_feedback_modal_fieldset'] = array(
    'render element' => 'fieldset',
    'template' => 'af-feedback-modal-fieldset',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['af_feedback_modal'] = array(
    'render element' => 'form',
    'template' => 'af-feedback-modal-form',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['af_share_modal'] = array(
    'render element' => 'fieldset',
    'template' => 'af-share-modal-fieldset',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['themed_checkboxes'] = array(
    'render element' => 'checkboxes',
    'template' => 'themed-checkboxes',
    'path' => drupal_get_path('theme', 'mantis') . '/templates/forms',
  );
  $items['mantis_header_menu'] = array(
    'template' => 'mantis-header-menu',
    'path' => drupal_get_path('theme', 'mantis') . '/templates',
  );
  return $items;
}

function mantis_preprocess_node(&$vars,$hook) {
  if($vars['type'] == 'question') {
    $vars['content']['links'] = array();
    $vars['content']['schemaorg_name'] = array();
  }
  if ($vars['view_mode'] == 'live_results_search') {
    $vars['theme_hook_suggestions'][] = 'node__autocomplete';
    $vars['theme_hook_suggestion'] = 'node__autocomplete';
  }
  // attach feedback form to all nodes - full view
  if($vars && module_exists('broadsoft_ask_flow')) {
    $vars['af_feedback'] = drupal_get_form('content_feedback_form', $vars['nid']);
    $vars['af_mfeedback'] = drupal_get_form('content_feedback_form', $vars['nid'], true);
  }
  $type = $vars['type'];
  switch($type) {
    case 'documentation':
      if(isset($vars['node']->field_document['und'][0]['uri'])) {
        $fpath = $vars['node']->field_document['und'][0]['uri'];
        $vars['download_form'] = drupal_get_form('download_documentation_form', $fpath);
      }
      break;
    default:
      $vars['action_buttons_form'] = drupal_get_form('action_buttons_form', $vars['nid']);
      break;
  }
}

/**
 * Implements hook_form_alter.
 * @param $form
 * @param $form_state
 * @param $form_id
 */
function mantis_form_alter(&$form, &$form_state, $form_id) {
  $page = isset($_GET['q']) ? $_GET['q'] : '';
  if($form['#id'] == 'views-exposed-form-questions-search-page' ) {
    $form['basic']['keys']['#title'] = t('Search'); // Change the text on the label element
    $form['#info']['filter-search_api_views_fulltext']['#title'] = t('Search');
    $form['#info']['filter-search_api_views_fulltext']['#title_display'] = 'invisible'; // Toggle label visibilty
    unset($form['#info']['filter-search_api_views_fulltext']['label']);

    if ($page == 'home') {
      $form['#attributes']['class'] = array("form-search", "form-inline");
      $form['search_api_views_fulltext']['#attributes']['class'][] = "form-control search-query";
    } else {
      $form['#attributes']['class'] = array("form-ask", "form-inline");
      $form['search_api_views_fulltext']['#attributes']['class'][] = "form-control ask-query";
    }
    $form['search_api_views_fulltext']['#attributes']['placeholder'] = t('Ask your question...');
    $form['submit']['#attributes']['class'][] = 'element-invisible';
  }
  else if($form['#id'] == "broadsoft-files-search-form") {
    if ($page == 'home' && $form['#id'] == "broadsoft-files-search-form") {
      $form['#attributes']['class'] = array("form-search", "form-inline");
      $form['search_term']['#attributes']['class'] = array("form-control", "search-query");
    }
    else {
      $form['#attributes']['class'] = array("form-ask", "form-inline");
      $form['search_term']['#attributes']['class'] = array("form-control", "ask-query");
    }
    $form['search_term']['#attributes']['placeholder'] = t('Ask your question...');
    $form['search_term']['#title_display'] = 'invisible';
    $form['submit']['#attributes']['class'][] = 'element-invisible';
    $form['#submit'] = 'search_files_redirect_handler';
  }
  else if(substr($form['#id'], 0, strlen('search-form')) === 'search-form') {
    $form['basic']['keys']['#title'] = t('Search'); // Change the text on the label element
    $form['basic']['keys']['#title_display'] = 'invisible';
    $form['basic']['keys']['#attributes']['placeholder'] = t('Ask your question...');
    $form['basic']['submit']['#attributes']['class'][] = 'element-invisible';
    $form['basic']['keys']['#attributes']['class'] = array("form-control", "ask-query");
    $form['#attributes']['class'] = array("form-ask", "form-inline");
  }
  else if ($form_id=='user_login_block') {
    $form['name'] = array('#type' => 'textfield',
      '#attributes' => array(
        'class' => array(
         'form-control'
        ),
        'placeholder' => array(
          'Email'
        ),
        'required' => true,
        'autofocus' => true,
      ),
      '#title' => 'Email address',
      '#size' => 60,
      '#maxlength' => USERNAME_MAX_LENGTH,
      '#required' => true,
      //'#theme_wrappers' => array(),
    );  
    $form['pass'] = array('#type' => 'password',
      '#attributes' => array(
        'class' => array(
          'form-control'
        ),
        'placeholder' => array(
          'Password'
        ),
        'required' => true
      ),
      '#title' => 'Password',
      '#required' => true,
      //'#theme_wrappers' => array('class' => ''),
    );
  }
}

function mantis_preprocess_user_login_block(&$vars) {
  $vars['name'] = render($vars['form']['name']);
  $vars['pass'] = render($vars['form']['pass']);
  $vars['submit'] = render($vars['form']['actions']['submit']);
  $vars['rendered'] = drupal_render_children($vars['form']);
}

function mantis_form_element_label($variables) {
  $element = $variables['element'];
  // This is also used in the installer, pre-database setup.
  $t = get_t();

  // If title and required marker are both empty, output no label.
  if ((!isset($element['#title']) || $element['#title'] === '') && empty($element['#required'])) {
    return '';
  }

  // If the element is required, a required marker is appended to the label.
  $required = !empty($element['#required']) ? theme('form_required_marker', array('element' => $element)) : '';

  $title = filter_xss_admin($element['#title']);

  $attributes = array();
  // Style the label as class option to display inline with the element.
  if ($element['#title_display'] == 'after') {
    $attributes['class'] = 'option';
  }
  // Show label only to screen readers to avoid disruption in visual flows.
  elseif ($element['#title_display'] == 'invisible') {
    $attributes['class'] = 'element-invisible';
  }

  if (!empty($element['#id'])) {
    $attributes['for'] = $element['#id'];
  }

  if(isset($element['#array_parents'][0]) && $element['#array_parents'][0] == 'modal_feedback') {
    // The leading whitespace helps visually separate fields from inline labels.
    return '<div class="check"></div><label' . drupal_attributes($attributes) . '>' . $t('!title !required', array('!title' => $title, '!required' => $required)) . "</label>\n";
  }
  else {
    return '<label' . drupal_attributes($attributes) . '>' . $t('!title !required', array('!title' => $title, '!required' => $required)) . "</label>\n";
  }
}

function mantis_preprocess_broadsoft_twocol(&$vars){
  $vars['url'] = $_GET['q'];
}

function mantis_preprocess_broadsoft_twocol_fw(&$vars){
  $vars['url'] = $_GET['q'];
}

function mantis_preprocess_broadsoft_tabs(&$vars){
  $vars['url'] = $_GET['q'];
  foreach($vars['display']->panels as $key => $value) {
    $view_name = $vars['display']->content[$value[0]]->subtype;
    $display_id = $vars['display']->content[$value[0]]->configuration['display'];
    $view = views_get_view($view_name);
    $vars[$key . '_title'] = $view->display[$display_id]->display_title;
  }
}

function mantis_pager($variables) {
  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        //   'class' => array('first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
//            'class' => array('pager-item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('current'),
            'data' => l($i, '',  array('fragment' => '', 'external' => TRUE)),
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            //  'class' => array('pager-item'),
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array('next'),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
//        'class' => array('last'),
        'data' => $li_last,
      );
    }
    //we wrap this in *gasp* so
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list__views_pager', array(
      'items' => $items,
      'attributes' => array('class' => array('pager pagination') ),
      'daddy' => 'pager'
    ));
  }
}

/**
 * Implements hook_preprocess_HOOK().
 */
function mantis_preprocess_panels_pane(&$vars) {
	
}

function mantis_item_list__views_pager($variables) {
  $items = $variables['items'];
  $title = $variables['title'];
  $type = $variables['type'];
  $attributes = $variables['attributes'];

  // Only output the list container and title, if there are any list items.
  // Check to see whether the block title exists before adding a header.
  // Empty headers are not semantic and present accessibility challenges.
  $output = '';
  if (isset($title) && $title !== '') {
    $output .= '<h3>' . $title . '</h3>';
  }

  if (!empty($items)) {
    $output .= "<$type" . drupal_attributes($attributes) . '>';
    $num_items = count($items);
    $i = 0;
    foreach ($items as $item) {
      $attributes = array();
      $children = array();
      $data = '';
      $i++;
      if (is_array($item)) {
        foreach ($item as $key => $value) {
          if ($key == 'data') {
            $data = $value;
          }
          elseif ($key == 'children') {
            $children = $value;
          }
          else {
            $attributes[$key] = $value;
          }
        }
      }
      else {
        $data = $item;
      }
      if (count($children) > 0) {
        // Render nested list.
        $data .= theme_item_list(array('items' => $children, 'title' => NULL, 'type' => $type, 'attributes' => $attributes));
      }
      if ($i == 1) {
        $attributes['class'][] = 'first';
      }
      if ($i == $num_items) {
        $attributes['class'][] = 'last';
      }
      $output .= '<li' . drupal_attributes($attributes) . '>' . $data . "</li>\n";
    }
    $output .= "</$type>";
  }
  $output .= '';
  return $output;
}

function mantis_search_api_autocomplete_suggestions_alter(array &$suggestions, array $context) {
  // Adding header and footer to suggestion lists
  if($context['search']->index_id == 'broadsoft_questions') {
  $header = array(
    'render' => '<h3>'.t('We think these resources could help:').'</h3>',
  );
  $footer = array(
    'render' => '<a class="see-more" href="questions-search?search_api_views_fulltext='.$context['user_input'].'" title="">See more <i class="fa fa-angle-right" aria-hidden="true"></i></a>',
  );
  // Only show 4 results
  $suggestions = array_slice($suggestions, 0, 4);
  array_unshift($suggestions , $header);
  $suggestions[] = $footer;
  }

  if($context['search']->index_id == "ticketing_suggestions") {
    $header = array(
      'render' => '<div class="sourceHeading"><h3 class="sourceHeading__text">'.t('Here\'s some resources that helped others with the same problem.').'</h3></div>',
    );
    $flink = l(t('See more'), 'broadsoft-content-search', array('query' => array('search_api_views_fulltext' => $context['query']->getKeys()), 'attributes' => array('class' => array('ticket__submit', 'ticket__submit_size_small', 'ticket__submit-text'))));
    $footer = array(
      'render' => '<div class="ticket__submit-line">'.$flink.'</div>',
    );
    // Only show 3 results
    $suggestions = array_slice($suggestions, 0, 3);
    array_unshift($suggestions , $header);
    $suggestions[] = $footer;
  }
}

function node_last_updated($nid) {
  $query = "SELECT u.name, u.uid
            FROM node_revision nr, users u
            WHERE nr.uid = u.uid
            AND nr.nid = ".$nid."
            ORDER BY timestamp DESC
            LIMIT 1";
  return db_query($query)->fetchAssoc();
}