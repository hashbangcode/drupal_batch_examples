<?php

namespace Drupal\Tests\batch_csv_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch CSV form example form.
 *
 * @group drupal_batch_examples
 */
class BatchFormTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'user',
    'file',
    'batch_csv_example',
  ];

  /**
   * The theme to install as the default for testing.
   *
   * @var string
   */
  public $defaultTheme = 'stark';

  /**
   * Test that the batch operation runs.
   */
  public function testBatchOperationRuns() {
    $user = $this->createUser(['access content']);
    $this->drupalLogin($user);

    $this->drupalGet('drupal-batch-examples/batch-csv-example');

    $modulePath = \Drupal::service('extension.list.module')->getPath('batch_csv_example');

    /** @var \Drupal\Core\File\FileSystemInterface $fileSystem */
    $fileSystem = \Drupal::service('file_system');
    $parameters = [
      'files[csv_file]' => $fileSystem->realpath($modulePath . '/tests/data/batch_csv_example.csv'),
    ];
    $this->submitForm($parameters, 'Run batch');
    $this->assertSession()->pageTextContains('Process CSV processed');
  }

}
