<?php

namespace Drupal\batch_class_example\Commands;

use Drupal\batch_class_example\Batch\BatchClass;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the batch_class_example module.
 */
class BatchCommands extends DrushCommands {

  use StringTranslationTrait;

  /**
   * Logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;

  /**
   * Constructs a new BatchCommands object.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   *   Logger service.
   */
  public function __construct(LoggerChannelFactoryInterface $loggerChannelFactory) {
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Run a batch operation via a Drush command.
   *
   * @command batch_class_example:run
   *
   * @validate-module-enabled batch_class_example
   *
   * @usage batch_class_example:run
   */
  public function runBatchclassExample() {
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

    drush_backend_batch_process();

    // Finish.
    $this->logger()->notice("Batch operations end.");
    $this->loggerChannelFactory->get('link_audit')->info('Redirect import batch operations end.');
  }

}
