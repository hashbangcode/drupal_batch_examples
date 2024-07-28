<?php

namespace Drupal\batch_finish_example\Form;

use Drupal\batch_finish_example\Batch\BatchClass;
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
    return 'batch_finish_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#markup' => $this->t('Submit this form to run a batch operation that will process 1000 items in batches of 100. Only one batch operation is given to the batch API, and this is called until a finish state is reached.'),
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
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $batch = new BatchBuilder();
    $batch->setTitle('Running batch process.')
      ->setFinishCallback([BatchClass::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    $array = range(1, 1000);
    $batch->addOperation([BatchClass::class, 'batchProcess'], [$array]);

    batch_set($batch->toArray());

    $form_state->setRedirectUrl(new Url($this->getFormId()));
  }

}
