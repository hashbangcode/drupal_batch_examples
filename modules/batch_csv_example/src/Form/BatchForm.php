<?php

namespace Drupal\batch_csv_example\Form;

use Drupal\batch_csv_example\Batch\BatchClass;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;

/**
 * Form that triggers a batch run.
 */
class BatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_csv_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#markup' => $this->t('This form will run a batch operation that will import a CSV to create Article content items. The CSV is processed in the batch operation so the size of the file does not matter.'),
    ];

    $form['csv_file'] = [
      '#type' => 'file',
      '#title' => $this->t('The CSV file to process'),
      '#description' => $this->t('You the CSV file from the below link.'),
    ];

    $form['csv_file_generate'] = [
      '#type' => 'link',
      '#title' => $this->t('Generate a CSV file containing 500 items.'),
      '#url' => Url::fromRoute('batch_csv_example.generate'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Run batch'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    parent::validateForm($form, $form_state);

    // Create a temporary file entity based on the file form element.
    // Note file_save_upload() might soon be deprecated.
    // https://www.drupal.org/project/drupal/issues/3375423
    $tempFileEntity = file_save_upload(
      'csv_file',
      [
        'FileExtension' => ['extensions' => 'csv'],
      ],
      FALSE,
      0,
    );

    if (!$tempFileEntity instanceof File) {
      $form_state->setErrorByName('csv_file', $this->t('Upload failed.'));
      return;
    }

    // Keep the temporary file entity available for submit handler.
    $form_state->setValue('temp_csv_file', $tempFileEntity);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $tempFile = $form_state->getValue('temp_csv_file');

    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setFinishCallback([BatchClass::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    // Process the CSV as a batch.
    $batch->addOperation([BatchClass::class, 'batchProcess'], [$tempFile->getFileUri()]);

    batch_set($batch->toArray());

    $form_state->setRedirectUrl(new Url($this->getFormId()));
  }

}
