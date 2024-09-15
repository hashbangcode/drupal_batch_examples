<?php

namespace Drupal\batch_finished_example\Batch;

/**
 * Batch class to show the use of the 'finished' property.
 */
class BatchProcessNodes {

  /**
   * Process a batch operation.
   *
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess(array &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $query = \Drupal::entityQuery('node');
      $query->accessCheck(FALSE);
      $count = $query->count()->execute();

      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $count;
    }
    if (!isset($context['results']['updated'])) {
      $context['results']['updated'] = 0;
      $context['results']['skipped'] = 0;
      $context['results']['failed'] = 0;
      $context['results']['progress'] = 0;
      $context['results']['process'] = 'Finished batch completed';
    }

    // Message above progress bar.
    $context['message'] = t('Processing batch @progress of total @count items.', [
      '@progress' => number_format($context['sandbox']['progress']),
      '@count' => number_format($context['sandbox']['max']),
    ]);

    if ($context['sandbox']['max'] === 0) {
      // There is nothing to do here, so set finished to 1 and return.
      $context['finished'] = 1;
      return;
    }

    $batchSize = 10;

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery();
    $query->accessCheck(FALSE);
    $query->range($context['sandbox']['progress'], $batchSize);
    $ids = $query->execute();

    foreach ($storage->loadMultiple($ids) as $entity) {
      // Keep track of progress.
      $context['sandbox']['progress']++;
      $context['results']['progress']++;

      // Process the entity here, for example, we might run $entity->delete() to
      // delete the entity.
    }

    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }

  /**
   * Handle batch completion.
   *
   * @param bool $success
   *   TRUE if all batch API tasks were completed successfully.
   * @param array $results
   *   An results array from the batch processing operations.
   * @param array $operations
   *   A list of the operations that had not been completed.
   * @param string $elapsed
   *   Batch.inc kindly provides the elapsed processing time in seconds.
   */
  public static function batchFinished(bool $success, array $results, array $operations, string $elapsed): void {
    // Grab the messenger service, this will be needed if the batch was a
    // success or a failure.
    $messenger = \Drupal::messenger();
    if ($success) {
      // The success variable was true, which indicates that the batch process
      // was successful (i.e. no errors occurred).
      // Show success message to the user.
      $messenger->addMessage(t('@process processed @count, skipped @skipped, updated @updated, failed @failed in @elapsed.', [
        '@process' => $results['process'],
        '@count' => $results['progress'],
        '@skipped' => $results['skipped'],
        '@updated' => $results['updated'],
        '@failed' => $results['failed'],
        '@elapsed' => $elapsed,
      ]));
      // Log the batch success.
      \Drupal::logger('batch_finished_example')->info(
        '@process processed @count, skipped @skipped, updated @updated, failed @failed in @elapsed.', [
          '@process' => $results['process'],
          '@count' => $results['progress'],
          '@skipped' => $results['skipped'],
          '@updated' => $results['updated'],
          '@failed' => $results['failed'],
          '@elapsed' => $elapsed,
        ]);
    }
    else {
      // An error occurred. $operations contains the operations that remained
      // unprocessed. Pick the last operation and report on what happened.
      $error_operation = reset($operations);
      if ($error_operation) {
        $message = t('An error occurred while processing %error_operation with arguments: @arguments', [
          '%error_operation' => print_r($error_operation[0]),
          '@arguments' => print_r($error_operation[1], TRUE),
        ]);
        $messenger->addError($message);
      }
    }
  }

}
