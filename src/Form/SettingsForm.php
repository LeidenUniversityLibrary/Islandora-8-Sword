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
    $form['swordimportusername'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Import user name'),
      '#default_value' => $this->config('sword.settings')->get('swordimportusername'),
      '#description' => $this->t('Run the instant importer as this user. This user is also used for basic authentication.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['swordbase'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base URL path'),
      '#default_value' => $this->config('sword.settings')->get('swordbase'),
      '#description' => $this->t('The base URL path for the SWORD api, without the leading slash, for example instant_importer/swordv1'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['swordservicepath'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Service path'),
      '#default_value' => $this->config('sword.settings')->get('swordservicepath'),
      '#description' => $this->t('The path for the service document, for example servicedocument. The full URL to the service document is {server url}/{Base URL path}/{Service path}.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['swordcollectionname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Collection name'),
      '#default_value' => $this->config('sword.settings')->get('swordcollectionname'),
      '#description' => $this->t('The name of the collection to use for ingesting via SWORD. This collection does not have to exist and is not used by the instant importer. Use the workflow to select the collection to ingest into.'),
      '#required' => TRUE,
      '#size' => 100,

    ];
    $form['swordacceptmimetype'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accepted MIME type'),
      '#default_value' => $this->config('sword.settings')->get('swordacceptmimetype'),
      '#description' => $this->t('The MIME type(s) that are accepted by this instant importer, separated by comma\'s.'),
      '#required' => TRUE,
      '#size' => 255,

    ];
    $form['swordacceptpackaging'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Accepted packaging'),
      '#default_value' => $this->config('sword.settings')->get('swordacceptpackaging'),
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
      ->set('swordimportusername', $form_state->getValue('swordimportusername'))
      ->set('swordbase', $form_state->getValue('swordbase'))
      ->set('swordservicepath', $form_state->getValue('swordservicepath'))
      ->set('swordcollectionname', $form_state->getValue('swordcollectionname'))
      ->set('swordacceptmimetype', $form_state->getValue('swordacceptmimetype'))
      ->set('swordacceptpackaging', $form_state->getValue('swordacceptpackaging'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
