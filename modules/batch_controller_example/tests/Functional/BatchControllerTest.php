<?php

namespace Drupal\Tests\batch_controller_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch controller example.
 *
 * @group drupal_batch_examples
 */
class BatchControllerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'drupal_batch_examples',
    'batch_controller_example',
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

    $this->drupalGet('drupal-batch-examples/batch-controller-example');

    $this->assertSession()->pageTextContains('Chunk batch completed processed');
  }

}
