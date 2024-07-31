<?php

namespace Drupal\Tests\batch_class_example\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test the functionality of the batch download example.
 *
 * @group content_links
 */
class DownloadControllerTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'node',
    'batch_download_example',
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

    $this->drupalGet('drupal-batch-examples/batch-download-example');
    $this->assertSession()->statusCodeEquals(200);
  }

}
