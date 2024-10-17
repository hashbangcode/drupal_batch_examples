<?php

namespace Drupal\batch_update_example\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form that triggers a batch run.
 */
class ResetForm extends FormBase {

  /**
   * KeyValue factory.
   *
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'batch_update_example';
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = new self($container);
    $instance->keyValue = $container->get('keyvalue');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#markup' => $this->t('Submit this form to reset the schema version of this module so that the update hook can be re-run.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Reset Update'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    // Reset the update hook setting.
    $this->keyValue->get('system.schema')->set('batch_update_example', (int) 10000);

    // Update the post update setting.
    $updateList = $this->keyValue->get('post_update')->get('existing_updates');
    $key = array_search('batch_update_example_post_update_process_nodes', $updateList);
    unset($updateList[$key]);
    $this->keyValue->get('post_update')->set('existing_updates', $updateList);

    $this->messenger()->addStatus('Module version updated, you can re-run the updates again.');
  }

}
