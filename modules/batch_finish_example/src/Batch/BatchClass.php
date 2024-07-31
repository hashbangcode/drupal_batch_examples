<?php

namespace Drupal\batch_finish_example\Batch;

/**
 * Batch class to show the use of the 'finish' property.
 */
class BatchClass {

  /**
   * Process a batch operation.
   *
   * @param array $array
   *   The array to process.
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess(array $array, array &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($array);
    }
    if (!isset($context['results']['updated'])) {
      $context['results']['updated'] = 0;
      $context['results']['skipped'] = 0;
      $context['results']['failed'] = 0;
      $context['results']['progress'] = 0;
    }

    $context['results']['process'] = 'Finish batch completed';

    // Message above progress bar.
    $context['message'] = t('Processing batch @progress of total @count items.', [
      '@progress' => number_format($context['sandbox']['progress']),
      '@count' => number_format($context['sandbox']['max']),
    ]);

    $batchSize = 100;

    for ($i = $context['sandbox']['progress']; $i < $context['sandbox']['progress'] + $batchSize; $i++) {
      $context['results']['progress']++;

      // Sleep for a bit to simulate work being done.
      usleep(4000 + $array[$i]);
      // Decide on the result of the batch.
      $result = rand(1, 4);
      switch ($result) {
        case '1':
        case '2':
          $context['results']['updated']++;
          break;

        case '3':
          $context['results']['skipped']++;
          break;

        case '4':
          $context['results']['failed']++;
          break;
      }
    }

    // Keep track of progress.
    $context['sandbox']['progress'] += $batchSize;

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
  public static function batchFinished(bool $success, array $results, array $operations, string $elapsed): void {
    $messenger = \Drupal::messenger();
    if ($success) {
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
      \Drupal::logger('delete_orphan')->info(
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
