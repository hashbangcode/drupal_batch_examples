<?php

namespace Drupal\batch_ajax_example\Batch;

/**
 * Defines a process and finish method for a batch.
 */
class BatchClass {

  /**
   * Process a batch operation.
   *
   * @param int $batchId
   *   The batch ID.
   * @param array $chunk
   *   The chunk to process.
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess(int $batchId, array $chunk, array &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = 1000;
    }
    if (!isset($context['results']['updated'])) {
      $context['results']['updated'] = 0;
      $context['results']['skipped'] = 0;
      $context['results']['failed'] = 0;
      $context['results']['progress'] = 0;
    }

    // Keep track of progress.
    $context['results']['progress'] += count($chunk);
    $context['results']['process'] = 'Chunk batch completed';

    // Message above progress bar.
    $context['message'] = t('Processing batch #@batch_id batch size @batch_size for total @count items.', [
      '@batch_id' => number_format($batchId),
      '@batch_size' => number_format(count($chunk)),
      '@count' => number_format($context['sandbox']['max']),
    ]);

    foreach ($chunk as $number) {
      // Sleep for a bit to simulate work being done.
      usleep(4000 + $number);
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
  }

}
