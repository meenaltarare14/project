<?php

function broadsoft_files_search_admin() {
  $form = array();
  /* SOLR server settings */
  $form['broadsoft_solr_server'] = array(
    '#type' => 'textfield',
    '#title' => t('SOLR Server'),
    '#description' => t("SOLR server that needs to be queried (do not include leading /) "),
    '#default_value' => variable_get('broadsoft_solr_server', 'http://cshp.getventive.com:8983/solr/cshp'),
  );
  $form['broadsoft_solr_query'] = array(
    '#type' => 'textfield',
    '#title' => t('Query'),
    '#description' => t("The following query will be used for searching (start query with /). User placeholder [term] in place of search term - eg. /select?q=[term]*&wt=json"),
    '#default_value' => variable_get('broadsoft_solr_query', '/select?q=[term]&wt=php&start=0'),
  );
  return system_settings_form($form);
}