<?php

/**
 * @file
 * The post update file for the batch_update_example module.
 */

/**
 * A demonstration of the hook_post_update_NAME() hook using the Batch API.
 */
function batch_update_example_post_update_process_nodes(&$sandbox) {
  if (!isset($sandbox['progress'])) {
    $query = \Drupal::entityQuery('node');
    $query->accessCheck(FALSE);
    $count = $query->count()->execute();

    if ($count === 0) {
      \Drupal::messenger()->addMessage('No nodes to process. Finishing.');
      // Don't run the update hook if no nodes are present.
      $sandbox['#finished'] = 1;
      return;
    }

    $sandbox['progress'] = 0;
    $sandbox['max'] = $count;
  }

  $batchSize = 25;

  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $query = $storage->getQuery();
  $query->accessCheck(FALSE);
  $query->range($sandbox['progress'], $batchSize);
  $ids = $query->execute();

  foreach ($storage->loadMultiple($ids) as $entity) {
    // Keep track of progress.
    $sandbox['progress']++;

    // Process the entity here. For example, we might run $entity->delete() to
    // delete the entity. Here, we are just updating the creation date of the
    // entity so that some action is performed.
    $entity->set('created', time());
    $entity->save();
  }

  \Drupal::messenger()->addMessage($sandbox['progress'] . ' nodes processed.');

  // Return the finished property, but notice the "#" prefixing the finished
  // property. This is required for update hooks.
  $sandbox['#finished'] = $sandbox['progress'] / $sandbox['max'];

  // You can also set the '#abort' property in the sandbox array to tell the
  // update hook to abort the update.
}
