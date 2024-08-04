<?php

namespace Drupal\Tests\batch_ajax_example\FunctionalJavaScript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Test the functionality of the batch ajax form example form.
 *
 * @group content_links
 */
class BatchFormTest extends WebDriverTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'drupal_batch_examples',
    'batch_ajax_example',
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

    $this->drupalGet('drupal-batch-examples/batch-ajax-example');

    $this->assertSession()->pageTextContains('Batch AJAX Example');

    // Wait for the ajax response to complete and for the batch to finish and
    // redirect.
    $this->assertSession()->assertWaitOnAjaxRequest();
    $this->getSession()->wait(9000);

    $this->assertSession()->pageTextContains('Drupal Batch Examples');
  }

}
