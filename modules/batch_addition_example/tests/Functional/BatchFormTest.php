<?php

namespace Drupal\Tests\batch_addition_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch addition form example form.
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
    'batch_addition_example',
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

    $this->drupalGet('drupal-batch-examples/batch-addition-example');

    $this->submitForm([], 'Run batch');
    $this->assertSession()->pageTextContains('Addition batch completed processed');
  }

}
