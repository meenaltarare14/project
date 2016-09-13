<?php

function broadsoft_ask_flow_admin() {
  $form = array();
  /* SOLR server settings */
  $form['broadsoft_af_index'] = array(
    '#type' => 'textfield',
    '#title' => t('Master Search Index'),
    '#description' => t("The machine name of the main search index to be used."),
    '#default_value' => variable_get('broadsoft_af_index', MASTER_SEARCH_API_INDEX),
  );
  $form['broadsoft_af_terms'] = array(
    '#type' => 'fieldset',
    '#description' => t('Search term settings')
  );
  $form['broadsoft_af_terms']['broadsoft_af_stopwords'] = array(
    '#type' => 'textarea',
    '#title' => t('Stop words'),
    '#description' => t("These words will be removed from user search when saving them for rating (comma or newline seperated)."),
    '#default_value' => variable_get('broadsoft_af_stopwords', AF_STOP_WORDS),
  );
  $form['broadsoft_af_scores'] = array(
    '#type' => 'fieldset',
    '#description' => t('Score settings')
  );
  $form['broadsoft_af_scores']['broadsoft_af_view_score'] = array(
    '#type' => 'textfield',
    '#title' => t('Content View'),
    '#description' => t("The score to be incremented/decremented when viewing a content."),
    '#default_value' => variable_get('broadsoft_af_view_score', VIEW_CONTENT_SCORE),
  );
  $form['broadsoft_af_scores']['broadsoft_af_upvote_score'] = array(
    '#type' => 'textfield',
    '#title' => t('Upvote Content'),
    '#description' => t("The score to be added when a content is upvoted."),
    '#default_value' => variable_get('broadsoft_af_upvote_score', DIRECT_UPVOTE_SCORE),
  );
  $form['broadsoft_af_scores']['broadsoft_af_downvote_score'] = array(
    '#type' => 'textfield',
    '#title' => t('Downvote Content'),
    '#description' => t("The score to be added when a content is downvoted."),
    '#default_value' => variable_get('broadsoft_af_downvote_score', DIRECT_DOWNVOTE_SCORE),
  );
  $form['broadsoft_af_scores']['broadsoft_af_upvote_ind_score'] = array(
    '#type' => 'textfield',
    '#title' => t('Upvote Content (indirect)'),
    '#description' => t("The score to be added when a content is assumend to be upvoted(indirect)."),
    '#default_value' => variable_get('broadsoft_af_upvote_ind_score', INDIRECT_UPVOTE_SCORE),
  );
  $form['broadsoft_af_scores']['broadsoft_af_downvote_ind_score'] = array(
    '#type' => 'textfield',
    '#title' => t('Downvote Content (indirect)'),
    '#description' => t("The score to be added when a content is assumend to be downvoted(indirect)."),
    '#default_value' => variable_get('broadsoft_af_downvote_ind_score', INDIRECT_DOWNVOTE_SCORE),
  );
  $form['broadsoft_af_debug'] = array(
    '#type' => 'checkbox',
    '#title' => t('Debug Ratings'),
    '#options' => array('1' => 'Debug'),
    '#default_value' => variable_get('broadsoft_af_debug', 0),
  );
  return system_settings_form($form);
}