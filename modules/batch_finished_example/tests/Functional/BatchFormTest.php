<?php

namespace Drupal\Tests\batch_finished_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch finished form example form.
 *
 * @group content_links
 */
class BatchFormTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'batch_finished_example',
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

    $this->drupalGet('drupal-batch-examples/batch-finished-example');

    $this->submitForm([], 'Run batch');
    $this->assertSession()->pageTextContains('Finished batch completed processed');
  }

}
