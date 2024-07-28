<?php

namespace Drupal\Tests\batch_finish_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch finish form example form.
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
    'batch_finish_example',
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

    $this->drupalGet('drupal-batch-examples/batch-finish-example');

    $this->submitForm([], 'Run batch');
    $this->assertSession()->pageTextContains('Finish batch completed processed');
  }

}
