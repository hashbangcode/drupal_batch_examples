<?php

namespace Drupal\batch_csv_example\Batch;

use Drupal\node\Entity\Node;

/**
 * Batch class to show how to consume a CSV file in a batch process.
 */
class BatchClass {

  /**
   * Process a batch operation.
   *
   * @param string $fileName
   *   The file name.
   * @param array $context
   *   Batch context.
   */
  public static function batchProcess(string $fileName, array &$context): void {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['seek'] = 0;
    }
    if (!isset($context['results']['updated'])) {
      $context['results']['updated'] = 0;
      $context['results']['skipped'] = 0;
      $context['results']['failed'] = 0;
      $context['results']['progress'] = 0;
      $context['results']['process'] = 'CSV batch completed';
    }

    $filesize = filesize($fileName);

    // Message above progress bar.
    $percent = round(($context['sandbox']['seek'] / $filesize) * 100);
    $context['message'] = t('Processing file, @seek of @filesize complete (@percentage%).', [
      '@seek' => number_format($context['sandbox']['seek']) . 'kb',
      '@filesize' => number_format($filesize) . 'kb',
      '@percentage' => $percent,
    ]);

    // How many lines to process at once?
    $limit = 1;

    // Keep track of how many lines we have processed in this batch.
    $count = 0;

    if ($handle = fopen($fileName, 'r')) {
      fseek($handle, $context['sandbox']['seek']);
      while ($line = fgetcsv($handle, 4096)) {

        $context['results']['progress']++;

        // Validate the CSV.
        if (count($line) !== 2) {
          // The line in the CSV file won't import correctly. So skip this line.
          $context['results']['skipped']++;
          continue;
        }

        // Process the CSV item.
        $node = Node::create([
          'type' => 'article',
          'title' => $line[0],
          'body' => [
            'value' => '<p>' . $line[1] . '</p>',
            'format' => filter_default_format(),
          ],
          'uid' => 1,
          'status' => 1,
        ]);
        $node->save();

        $context['results']['updated']++;

        $count++;
        if ($count >= $limit) {
          // We have reached the limit for this run, break out of the loop.
          // If we have more file to process then we will run the batch
          // function again.
          break;
        }
      }
      // Update the position of the pointer.
      $context['sandbox']['seek'] = ftell($handle);

      // Close the file handle.
      fclose($handle);
    }

    // Update the finished parameter.
    $context['finished'] = $context['sandbox']['seek'] / $filesize;
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
      \Drupal::logger('batch_csv_example')->info(
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
