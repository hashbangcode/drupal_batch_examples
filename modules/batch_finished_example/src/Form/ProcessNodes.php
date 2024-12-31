<?php

namespace Drupal\batch_finished_example\Form;

use Drupal\batch_finished_example\Batch\BatchProcessNodes;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Form that triggers a batch run that process nodes.
 */
class ProcessNodes extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_process_nodes';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->messenger()->addWarning('Note! Submitting this form will alter all content items on the site.');

    $form['help'] = [
      '#markup' => $this->t('Submit this form to run a batch operation that will process all nodes in the site. This uses the finished state of the batch API to determine when to stop processing data.'),
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
      ->setFinishCallback([BatchProcessNodes::class, 'batchFinished'])
      ->setInitMessage('Commencing')
      ->setProgressMessage('Processing...')
      ->setErrorMessage('An error occurred during processing.');

    $batch->addOperation([BatchProcessNodes::class, 'batchProcess']);

    batch_set($batch->toArray());

    $form_state->setRedirectUrl(new Url($this->getFormId()));
  }

}
