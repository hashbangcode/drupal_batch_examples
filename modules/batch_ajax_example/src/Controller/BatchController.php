<?php

namespace Drupal\batch_ajax_example\Controller;

use Drupal\batch_ajax_example\Batch\BatchClass;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\BaseCommand;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * A controller action that triggers a batch run.
 */
class BatchController extends ControllerBase {

  /**
   * Callback for the route batch_controller_example.
   *
   * @return array
   *   The rendered loading page.
   */
  public function __invoke():array {
    return [
      '#theme' => 'my_loading',
      '#attached' => [
        'library' => [
          'batch_ajax_example/loader',
        ],
      ],
    ];
  }

  /**
   * Callback for the route batch_ajax_example_callback.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response object.
   */
  public function ajaxBatchProcess() {
    // Setup batch process, this time without a finish step.
    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setProgressive(FALSE)
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

    // Create the batch_process(), and feed it a URL that it will go to.
    $url = Url::fromRoute('drupal_batch_examples');
    $response = batch_process($url);

    // Create a message to sent to the user on batch completion. We do this here
    // because the finish callback isn't called in this context. This will show
    // on the next page after the redirect happens.
    $this->messenger()->addMessage('Batch AJAX run complete');

    // Return the response to the ajax output.
    $ajaxResponse = new AjaxResponse();
    return $ajaxResponse->addCommand(new BaseCommand('batchcustomer', $response->getTargetUrl()));
  }

}
