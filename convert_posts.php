<?php
$roles = node_load_multiple(array(), array('type' => 'roles'));

if(empty($roles)){
  $node = new StdClass();
  $node->type = 'roles';
  node_object_prepare($node);
  $node->language = LANGUAGE_NONE;
  $node->title = 'TI';
  node_save($node);

  $node = new StdClass();
  $node->type = 'roles';
  node_object_prepare($node);
  $node->language = LANGUAGE_NONE;
  $node->title = 'VA';
  node_save($node);

  $node = new StdClass();
  $node->type = 'roles';
  node_object_prepare($node);
  $node->language = LANGUAGE_NONE;
  $node->title = 'VAF';
  node_save($node);

  $roles = node_load_multiple(array(), array('type' => 'roles'));

  foreach($roles as $role){
    // Create the field base.
    $field = array(
    'field_name' => 'field_role_n'.$role->nid,
    'type' => 'volunteers_roles_field',
    );
    field_create_field($field);
    // Create the field instance on the bundle.
    $instance = array(
      'field_name' => 'field_role_n'.$role->nid,
      'entity_type' => 'field_collection_item',
      'label' => 'Role',
      'bundle' => 'field_volunteers_pr_party_1',
      // If you don't set the "required" property then the field wont be required by default.
      'required' => TRUE,
      'widget' => array(
        'type' => 'custom_type',
      ),
    );
    field_create_instance($instance);
  }
}
else {
  echo "\nThere are some roles present in the installation, check that there is correlation between role nodes and fieldcollection fields, otherwise stuff will break\n\nPress [ENTER] to continue\n";
  $line = fgets(STDIN);
}

$roles = node_load_multiple(array(), array('type' => 'roles'));

foreach($roles as $role){
  $roles_array[$role->title] = 'field_role_n'.$role->nid;
}

// Load all polling stations for update
$polling_stations = node_load_multiple(array(), array('type' => 'polling_station'));
foreach($polling_stations as $node){
  echo "update for " . $node->title ."\n";
  foreach($node->field_volunteers_pr_party['da'] as $volunteers){

    $field_collection_item = entity_create('field_collection_item', array('field_name' => 'field_volunteers_pr_party_1'));
    $field_collection_item->setHostEntity('node', $node);

    if($node->field_party['da'][0]['tid'] == $volunteers['party']){
      if($volunteers['number_va'] > 0){
        $volunteers['number_va'] = $volunteers['number_va'] - 1;
      }

      $field_collection_item->{$roles_array['VAF']}[LANGUAGE_NONE][1] = array('number_vo' => 1, 'meeting_time' => 2);
    }

    // Set volunteers on role TI
    $field_collection_item->{$roles_array['TI']}[LANGUAGE_NONE][1] = array('number_vo' => $volunteers['number_ti'], 'meeting_time' => 2);

    // Set volunteers on role TI
    $field_collection_item->{$roles_array['VA']}[LANGUAGE_NONE][1] = array('number_vo' => $volunteers['number_va'], 'meeting_time' => 2);


    // Set volunteers party id
    $field_collection_item->field_party_list[LANGUAGE_NONE][]['party_list'] = $volunteers['party'];

    $field_collection_item->save();
  }
}
