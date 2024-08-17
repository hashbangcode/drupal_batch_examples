<?php

namespace Drupal\batch_controller_example\Controller;

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * A controller action that triggers a batch run.
 */
class BatchController extends ControllerBase {

  /**
   * Callback for the route batch_controller_example.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|null
   *   The redirect to the batch worker.
   */
  public function __invoke() {
    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setFinishCallback([self::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    // Create 10 chunks of 100 items.
    $chunks = array_chunk(range(1, 1000), 100);

    // Process each chunk in the array.
    foreach ($chunks as $id => $chunk) {
      $args = [
        $id,
        $chunk,
      ];
      $batch->addOperation([self::class, 'batchProcess'], $args);
    }
    batch_set($batch->toArray());
    return batch_process();
  }

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
      $context['results']['process'] = 'Controller batch completed';
    }

    // Keep track of progress.
    $context['results']['progress'] += count($chunk);

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
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response.
   */
  public static function batchFinished(bool $success, array $results, array $operations, string $elapsed): RedirectResponse {
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
      \Drupal::logger('batch_controller_example')->info(
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

    // Issue a redirect.
    return new RedirectResponse(Url::fromRoute('drupal_batch_examples')->toString());
  }

}
