<?php
$tree = taxonomy_get_tree(2);

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
      /**
    * figure out how many posts to fill
       */
    $station_id = $polling_station->nid;
    $posts_to_fill = array();

    if (isset($polling_station->field_volunteers_pr_party['da'][0])) {
      foreach ($polling_station->field_volunteers_pr_party['da'] as $item) {
        if ($item['party'] == $user_party_id) {
          if (isset($item['number_va']) && $item['number_va'] > 0) {
            $posts_to_fill = array_fill(0, $item['number_va'], 'va');
          }

          if (isset($item['number_ti']) && $item['number_ti'] > 0) {
            $posts_to_fill = array_merge($posts_to_fill, array_fill(count($posts_to_fill) - 1, $item['number_ti'], 'ti'));
          }
          break;
        }
      }
    }

    if (isset($polling_station->field_party['da'][0]['tid']) &&
        ($polling_station->field_party['da'][0]['tid'] == $user_party_id)
    ) {
      $posts_to_fill[0] = 'vaf';
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
      if (isset($node->field_polling_station_post['da'][0]['value'])) {
        $existing[$node->field_polling_station_post['da'][0]['value']] = array(
            'data' => $node->title,
            'nid' => $node->nid
        );
      }
    }
    
    // Offset by party and pollingstation id's into post number. - jm@bellcom.dk
    foreach ($posts_to_fill as $i => $post) {
      $t = $i + $user_party_id * 67 + $station_id * 67 * 76;
      $posts_to_fill[$t] = $post;
      unset($posts_to_fill[$i]);
      if (isset($existing[$i])) { // Handle existing post id during migration.
        $existing[$t] = $existing[$i];
        unset($existing[$i]);
      }
    }
    ksort($existing);

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

    foreach($existing as $item){
      $role = array_shift($posts_to_fill);

      $station_role_id = $user_party_id . $role_array[$role] . $station_id;

      $node = node_load($item['nid']);

      if(is_object($node)){
        $node->field_polling_station_post['da'][0]['value'] = $station_role_id;

        node_save($node);
      }
    }
  }
}
