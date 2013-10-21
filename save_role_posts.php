<?php
$tree = taxonomy_get_tree(1);

foreach($tree as $term){

  $user_party_id = $term->tid;

  echo 'Update for termid ' . $term->tid ."\n";

  $query = new EntityFieldQuery();
  $query->entityCondition('entity_type', 'node')
    ->entityCondition('bundle', 'polling_station');
  $result = reset($query->execute());

  foreach($result as $key => $value){
    echo 'Update for polling station ' . $key ."\n";

    $polling_station = node_load($key);

    $station_id = $polling_station->nid;
    $posts_to_fill = array();

    $res = db_select('node', 'n')
    ->fields('n', array('nid', 'title'))   
    ->condition('n.type', 'roles')
    ->execute();
    while($rec=$res->fetchAssoc()){
      $nids[$rec['nid']]= $rec['title'];
    }

    if ($volunteers_pr_party=field_get_items('node',$polling_station, 'field_volunteers_pr_party_1')) {
      foreach ($volunteers_pr_party as $item) {
        $field_collection_item=entity_load('field_collection_item', array($item['value']));   
        $party_id=field_get_items('field_collection_item',$field_collection_item[$item['value']],'field_party_list');

        if($party_id[0]["party_list"]==$user_party_id) {
          foreach($nids as $nid=>$title){
            $field_name='field_role_n'.$nid;
            $field=field_get_items('field_collection_item',$field_collection_item[$item['value']],$field_name);

            if($field&&(int)$field[0]['number_vo']>0)
              if($title != "VAF") {
                $posts_to_fill =  array_merge($posts_to_fill, array_fill(0,$field[0]['number_vo'], strtolower ($title)));
	      }
            }
        }
      }
      if (isset($polling_station->field_party['da'][0]['tid']) &&
        ($polling_station->field_party['da'][0]['tid'] == $user_party_id)
      ) {
        $posts_to_fill[0] = 'vaf';
      }
    }

    /**
      * find existing posts
      */
    $select = db_select('field_data_field_polling_station_post', 'psp');
    $select->fields('psp', array('entity_id'));
    $select->join('field_data_field_polling_station', 'ps', 'ps.entity_id = psp.entity_id');
    $select->join('field_data_field_party', 'p', 'p.entity_id = psp.entity_id');
    $select->condition('psp.bundle', 'volunteers');
    $select->condition('ps.bundle', 'volunteers');
    $select->condition('ps.field_polling_station_nid', $polling_station->nid);

      $select->condition('p.field_party_tid', $user_party_id);

    $volunteer_node_ids = $select->execute()
        ->fetchAll(PDO::FETCH_COLUMN);

    $existing = array();
    foreach (node_load_multiple($volunteer_node_ids, array(), TRUE) as $node) {
        // var_dump($node);
      if (isset($node->field_polling_station_post['da'][0]['value'])) {
        $existing[$node->field_polling_station_post['da'][0]['value']] = array(
            'data' => $node->title,
            'nid' => $node->nid
        );
      }
    }

    // Get all roles
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'node')
      ->entityCondition('bundle', 'roles');
    $results = $query->execute();
    $nodes = node_load_multiple(array_keys($results['node']));

    $role_array = array();

    // Create an array thats easy to lookup in
    foreach($nodes as $key => $value){
      $role_array[strtolower($value->title)] = $key;
    }

    // Set the role id(node id) for the role on the appropriate volunteer
    foreach ($posts_to_fill as $i => $post) {
      $t = $i + $user_party_id * 67 + $station_id * 67 * 76;
      $posts_to_fill[$t] = $post;
      $existing[$t]['role'] = $role_array[$post];
    }

    foreach($existing as $key => $value) {
      // Create uniqe id for the post on the station.
      $station_role_id = $user_party_id . $value['role'] . $station_id;

      $existing_node = node_load($value['nid']);

      if(is_object($existing_node)){
        $existing_node->field_polling_station_post['da'][0]['value'] = $station_role_id;

        node_save($existing_node);
      }
    }
  }
}
