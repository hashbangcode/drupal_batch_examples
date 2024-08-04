<?php

namespace Drupal\batch_download_example\Batch;

/**
 * Defines a process and finish method for a batch.
 */
class BatchClass {

  /**
   * Process a batch operation.
   *
   * @param string $filename
   *   The export filename to use.
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess(string $filename, array &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $query = \Drupal::entityQuery('node');
      $query->accessCheck(FALSE);
      $count = $query->count()->execute();

      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = $count;
    }
    if (!isset($context['results']['progress'])) {
      $context['results']['progress'] = 0;
    }

    if ($context['sandbox']['max'] === 0) {
      // No data to export, so we return here.
      $fp = fopen($filename, 'a');
      $csvRow = [];
      fputcsv($fp, $csvRow);
      $context['finished'] = 1;
      return;
    }

    $context['results']['process'] = 'File creation completed';

    // Message above progress bar.
    $context['message'] = t('Processing batch for total @count items.', [
      '@count' => number_format($context['sandbox']['max']),
    ]);

    $batchSize = 10;

    $storage = \Drupal::entityTypeManager()->getStorage('node');
    $query = $storage->getQuery();
    $query->accessCheck(FALSE);
    $query->range($context['sandbox']['progress'], $batchSize);
    $ids = $query->execute();

    $entities = $storage->loadMultiple($ids);

    $fp = fopen($filename, 'a');

    foreach ($entities as $entity) {
      // Keep track of progress.
      $context['sandbox']['progress']++;
      $context['results']['progress']++;

      $csvRow = [];
      $csvRow[] = $entity->id();
      $csvRow[] = $entity->getTitle();

      fputcsv($fp, $csvRow);
    }

    $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
  }

  /**
   * Handle batch completion.
   *
   * @param bool $success
   *   TRUE if all batch API tasks were completed successfully.
   * @param array $results
   *   An array of processed node IDs.
   * @param array $operations
   *   A list of the operations that had not been completed.
   * @param string $elapsed
   *   Batch.inc kindly provides the elapsed processing time in seconds.
   */
  public static function batchFinished(bool $success, array $results, array $operations, string $elapsed) {
    // Grab the messenger service, this will be needed if the batch was a
    // success or a failure.
    $messenger = \Drupal::messenger();
    if ($success) {
      // The success variable was true, which indicates that the batch process
      // was successful (i.e. no errors occurred).
      // Show success message to the user.
      $messenger->addMessage(t('exported @count entities in @elapsed.', [
        '@count' => $results['progress'],
        '@elapsed' => $elapsed,
      ]));
      // Log the batch success.
      \Drupal::logger('batch_download_example')->info(
        'exported @count entities in @elapsed.', [
          '@count' => $results['progress'],
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
