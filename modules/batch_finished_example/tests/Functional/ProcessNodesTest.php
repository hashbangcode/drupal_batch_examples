<?php

namespace Drupal\Tests\batch_finished_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch finished form example form.
 *
 * @group drupal_batch_examples
 */
class ProcessNodesTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'batch_finished_example',
    'node'
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

    $this->drupalGet('drupal-batch-examples/batch-process-nodes');

    $this->submitForm([], 'Run batch');
    $this->assertSession()->pageTextContains('Finished batch completed processed');
  }

}
