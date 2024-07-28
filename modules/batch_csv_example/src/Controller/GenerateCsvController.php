<?php

namespace Drupal\batch_csv_example\Controller;

use Drupal\Component\Utility\Random;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates a CSV for use in the CSV bach example form.
 */
final class GenerateCsvController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function __invoke() {
    $csvData = [];

    // Initialise the random class for later use.
    $random = new Random();

    // Generate 500 rows of a CSV file.
    for ($i = 0; $i < 500; $i++) {
      $row = [
        $random->name(15),
        $random->sentences(2),
      ];
      $csvData[] = implode(',', $row);
    }

    // Return the output with the appropriate headers to download the file
    // as a CSV.
    $content = implode("\n", $csvData);
    $response = new Response($content);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="batch_csv_example.csv"');

    return $response;
  }

}
