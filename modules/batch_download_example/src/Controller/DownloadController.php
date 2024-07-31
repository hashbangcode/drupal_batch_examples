<?php

namespace Drupal\batch_download_example\Controller;

use Drupal\batch_download_example\Batch\BatchClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * A controller action that triggers a file download.
 */
class DownloadController extends ControllerBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new static();
    $instance->fileSystem = $container->get('file_system');
    return $instance;
  }

  /**
   * Callback for the route batch_controller_example.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse|string[]
   *   The redirect to the batch worker.
   */
  public function __invoke() {
    $batchFilesDirectory = "private://batch_download_directory";
    if (in_array('private', stream_get_wrappers()) === FALSE) {
      // If the private stream wrapper doesn't exist then use public instead.
      $batchFilesDirectory = "public://batch_download_directory";
    }

    $filePath = $this->fileSystem->realpath($batchFilesDirectory) . "/batch.csv";
    if (file_exists($filePath)) {
      $binaryFileResponse = new BinaryFileResponse($filePath);
      $binaryFileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($filePath));
      $binaryFileResponse->deleteFileAfterSend();
      return $binaryFileResponse;
    }

    if ($this->fileSystem->prepareDirectory($batchFilesDirectory, FileSystemInterface::CREATE_DIRECTORY) === FALSE) {
      // File system can't be prepared return an error.
      $this->messenger()->addError('file system kaput');

      return [
        '#markup' => '<p>A problem occurred.</p>',
      ];
    }

    $batch = new BatchBuilder();
    $batch->setTitle('Running batch download process.')
      ->setFinishCallback([BatchClass::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    // Process the file download.
    $args = [
      $filePath,
    ];
    $batch->addOperation([BatchClass::class, 'batchProcess'], $args);

    batch_set($batch->toArray());
    return batch_process();
  }

}
