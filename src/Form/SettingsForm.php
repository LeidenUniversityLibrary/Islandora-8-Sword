<?php

namespace Drupal\sword\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure sword settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sword_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['sword.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['importusername'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import user name'),
      '#default_value' => $this->config('sword.settings')->get('importusername'),
      '#description' => $this->t('Run the instant importer as this user. This user is also used for basic authentication.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['base_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL path'),
      '#default_value' => $this->config('sword.settings')->get('base_url'),
      '#description' => $this->t('The base URL path for the SWORD api, without the leading slash, for example instant_importer/swordv1'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['servicepath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service path'),
      '#default_value' => $this->config('sword.settings')->get('servicepath'),
      '#description' => $this->t('The path for the service document, for example servicedocument. The full URL to the service document is {server url}/{Base URL path}/{Service path}.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['collectionname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Collection name'),
      '#default_value' => $this->config('sword.settings')->get('collectionname'),
      '#description' => $this->t('The name of the collection to use for ingesting via SWORD. This collection does not have to exist and is not used by the instant importer. Use the workflow to select the collection to ingest into.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['acceptmimetype'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accepted MIME type'),
      '#default_value' => $this->config('sword.settings')->get('acceptmimetype'),
      '#description' => $this->t('The MIME type(s) that are accepted by this instant importer, separated by comma\'s.'),
      '#required' => TRUE,
      '#size' => 255,

    ];
    $form['acceptpackaging'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accepted packaging'),
      '#default_value' => $this->config('sword.settings')->get('acceptpackaging'),
      '#description' => $this->t('The packaging format as a URI, optionally followed by a space and a quality value (floating-point value between 0 and 1), separated by comma\'s.'),
      '#required' => TRUE,
      '#size' => 255,

    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*if ($form_state->getValue('example') != 'example') {
      $form_state->setErrorByName('example', $this->t('The value is not correct.'));
    }
    parent::validateForm($form, $form_state);*/
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('sword.settings')
      ->set('importusername', $form_state->getValue('importusername'))
      ->set('base_url', $form_state->getValue('base_url'))
      ->set('servicepath', $form_state->getValue('servicepath'))
      ->set('collectionname', $form_state->getValue('collectionname'))
      ->set('acceptmimetype', $form_state->getValue('acceptmimetype'))
      ->set('acceptpackaging', $form_state->getValue('acceptpackaging'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
