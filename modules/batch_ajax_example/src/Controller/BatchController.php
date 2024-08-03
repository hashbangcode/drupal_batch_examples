<?php

namespace Drupal\batch_ajax_example\Controller;

use Drupal\batch_ajax_example\Batch\BatchClass;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BaseCommand;
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
    return [
      '#theme' => 'my_loading',
      '#attached' => [
        'library' => [
          'batch_ajax_example/loader',
        ],
      ],
    ];
  }

  public function ajaxBatchProcess() {
    // Setup batch process.
    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setFinishCallback([BatchClass::class, 'batchFinished'])
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
      $batch->addOperation([BatchClass::class, 'batchProcess'], $args);
    }
    batch_set($batch->toArray());

    // Get the batch that we just created.
    $batch =& batch_get();

    // Ensure that the finished response doesn't produce any messages.
    $batch['sets'][0]['finished'] = NULL;

    // Create the batch_process(), and feed it a URL that it will go to.
    $url = Url::fromRoute('drupal_batch_examples');
    $response = batch_process($url);

    // Return the response to the ajax output.
    $ajaxResponse = new AjaxResponse();
    return $ajaxResponse->addCommand(new BaseCommand('batchcustomer', $response->getTargetUrl()));
  }

}
