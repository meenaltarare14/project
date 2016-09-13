<?php

/**
 * @file
 * Contains SearchApiAutocompleteLiveResultsSuggester.
 */

/**
 * Provides a suggester plugin that retrieves Live Results.
 *
 * The server needs to support the "search_api_autocomplete" feature for this to
 * work.
 */
class SearchApiAutocompleteLiveResultsSuggester extends SearchApiAutocompleteSuggesterPluginBase {
  
  /**
   * {@inheritdoc}
   */
  public static function supportsIndex(SearchApiIndex $index) {
    try {
      return $index->server() && $index->server()->supportsFeature('search_api_autocomplete');
    }
    catch (SearchApiException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'fields' => array(),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    // Add a list of fields to include for autocomplete searches.
    $search = $this->getSearch();
    $fields = $search->index()->getFields();
    $fulltext_fields = $search->index()->getFulltextFields();
    $options = array();
    
    foreach ($fulltext_fields as $field) {
      $options[$field] = check_plain($fields[$field]['name']);
    }
    
    $form['display'] = array(
      '#type' => 'radios',
      '#title' => t('Display method'),
      '#description' => t('The way the results should be displayed.'),
      '#default_value' => !empty($this->configuration['display']),
      '#options' => array(
        'view_mode' => t("Use view mode: 'Live result search' to display results"),
        'title' => t("Only show title (linked to node)"),
      ),
      '#default_value' => (!empty($this->configuration['display'])) ? $this->configuration['display'] : 'title',
    );
    
    $form['fields'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Override used fields'),
      '#description' => t('Select the fields which should be searched for matches when looking for autocompletion suggestions. Leave blank to use the same fields as the underlying search.'),
      '#options' => $options,
      '#default_value' => drupal_map_assoc($this->configuration['fields']),
      '#attributes' => array('class' => array('search-api-checkboxes-list')),
    );
    $form['#attached']['css'][] = drupal_get_path('module', 'search_api') . '/search_api.admin.css';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array $form, array &$form_state) {
    $values = $form_state['values'];
    $values['fields'] = array_keys(array_filter($values['fields']));
    $this->setConfiguration($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getAutocompleteSuggestions(SearchApiQueryInterface $query, $incomplete_key, $user_input) {
    $search = $this->getSearch();
    
    if ($this->configuration['fields']) {
      $query->fields($this->configuration['fields']);
    }
    
    $results = $query->execute();
    $ret = array();
    $ids = array();
    foreach ((array) $results['results'] as $result) {
      $ids[] = $result['id'];
    }  
    $render = NULL;
    if(!empty($ids)) {
      // Load all searched suggested entities.
      $entity_type = $search->index()->item_type;
      $entities = entity_load($entity_type, $ids);
      
      if ($this->configuration['display'] == 'view_mode') {
        $entity_view = entity_view($entity_type, $entities, 'live_results_search');
      }

      foreach($entities as $id => $entity) {
        if ($entity_view) {
          $render = drupal_render($entity_view[$entity_type][$id]);
        }
        else {
          $url = entity_uri($entity_type, $entity);
          $render = l($entity->title, $url['path']);
        }
        $ret[] = array(
          'entity' => $entity,
          'render' => $render,
        );
      }
    }
    
    return $ret;
  }

}
