<?php

namespace Drupal\batch_addition_example\Form;

use Drupal\batch_addition_example\Batch\BatchClass;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form that triggers a batch run.
 */
class BatchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_addition_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setFinishCallback([BatchClass::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    // Create 10 chunks of 100 items.
    $chunks = array_chunk(range(1, 1000), 100);

    // Process each chunk in the array.
    foreach ($chunks as $id => $chunk) {
      $args = [
        $id,
        $chunk,
        TRUE,
      ];
      $batch->addOperation([BatchClass::class, 'batchProcess'], $args);
    }
    batch_set($batch->toArray());

    $form_state->setRedirectUrl(new Url($this->getFormId()));
  }

}
