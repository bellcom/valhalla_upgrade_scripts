<?php
$type = array(
  'type' => 'roles',
  'name' => 'Roles of volunteers',
  'base' => 'node_content',
  'custom' => 1,
  'modified' => 1,
  'locked' => 0,
  'title_label' => 'Role'
);

$type = node_type_set_defaults($type);
node_type_save($type);






$field = array(
    'active' => 1,
    'cardinality' => -1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_volunteers_pr_party_1',
    'foreign keys' => array(),
    'indexes' => array(),
    'locked' => 0,
    'module' => 'field_collection',
    'settings' => array(
      'hide_blank_items' => 1,
      'path' => '',
    ),
    'translatable' => 0,
    'type' => 'field_collection',
  );

field_create_field($field);


$field =  array(
    'active' => 1,
    'cardinality' => 1,
    'deleted' => 0,
    'entity_types' => array(),
    'field_name' => 'field_party_list',
    'foreign keys' => array(),
    'indexes' => array(),
    'locked' => 0,
    'module' => 'valhalla_field_party_volunteers_v3',
    'settings' => array(),
    'translatable' => 0,
    'type' => 'party_volunteers_field_v3',
  );

field_create_field($field);

$instance = array(
    'bundle' => 'polling_station',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'above',
        'module' => 'field_collection',
        'settings' => array(
          'add' => 'Add',
          'delete' => 'Delete',
          'description' => TRUE,
          'edit' => 'Edit',
          'view_mode' => 'full',
        ),
        'type' => 'field_collection_view',
        'weight' => 24,
      ),
      'teaser' => array(
        'label' => 'above',
        'settings' => array(),
        'type' => 'hidden',
        'weight' => 0,
      ),
    ),
    'entity_type' => 'node',
    'field_name' => 'field_volunteers_pr_party_1',
    'label' => 'Frivillige pr. parti_v2',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'field_collection',
      'settings' => array(),
      'type' => 'field_collection_embed',
      'weight' => 18,
    ),
  );

field_create_instance($instance);

  // Exported field_instance: 'field_collection_item-field_volunteers_pr_party_1-field_party_list'
  $instance = array(
    'bundle' => 'field_volunteers_pr_party_1',
    'default_value' => NULL,
    'deleted' => 0,
    'description' => '',
    'display' => array(
      'default' => array(
        'label' => 'inline',
        'module' => 'valhalla_field_party_volunteers_v3',
        'settings' => array(),
        'type' => 'party_volunteers_default_v3',
        'weight' => 4,
      ),
      'token' => array(
        'label' => 'inline',
        'module' => 'valhalla_field_party_volunteers_v3',
        'settings' => array(),
        'type' => 'party_volunteers_default_v3',
        'weight' => 4,
      ),
    ),
    'entity_type' => 'field_collection_item',
    'field_name' => 'field_party_list',
    'label' => 'Party',
    'required' => 0,
    'settings' => array(
      'user_register_form' => FALSE,
    ),
    'widget' => array(
      'active' => 0,
      'module' => 'valhalla_field_party_volunteers_v3',
      'settings' => array(
        'available_parties' => array(),
      ),
      'type' => 'party_volunteers_standard_v3',
      'weight' => 0,
    ),
  );

field_create_instance($instance);
