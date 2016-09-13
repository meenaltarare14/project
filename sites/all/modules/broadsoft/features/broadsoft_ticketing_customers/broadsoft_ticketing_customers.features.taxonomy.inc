<?php
/**
 * @file
 * broadsoft_ticketing_customers.features.taxonomy.inc
 */

/**
 * Implements hook_taxonomy_default_vocabularies().
 */
function broadsoft_ticketing_customers_taxonomy_default_vocabularies() {
  return array(
    'aoi_access' => array(
      'name' => 'AOI-Access',
      'machine_name' => 'aoi_access',
      'description' => 'Area Of Interest: this is the MAIN way of granting/denying ACCESS to content depending on the user roles.',
      'hierarchy' => 1,
      'module' => 'taxonomy',
      'weight' => 0,
      'rdf_mapping' => array(
        'rdftype' => array(
          0 => 'skos:ConceptScheme',
        ),
        'name' => array(
          'predicates' => array(
            0 => 'dc:title',
          ),
        ),
        'description' => array(
          'predicates' => array(
            0 => 'rdfs:comment',
          ),
        ),
      ),
    ),
  );
}
