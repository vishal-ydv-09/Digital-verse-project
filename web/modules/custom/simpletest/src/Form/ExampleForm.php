<?php

declare(strict_types=1);

namespace Drupal\simpletest\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Captcha\CaptchaManagerInterface;
use Drupal\core\database\Database;

/**
 * Provides a Simpletest form.
 */
final class ExampleForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'simpletest_example';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {

    $form['fullname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full name'),
      '#required' => TRUE,
    ];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];

    $form['number'] = [
      '#type' => 'number',
      '#title' => $this->t('Phone Number'),
      '#required' => TRUE,
    ];
    $form['address'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Address'),
      '#required' => TRUE,
    ];

    $form['dob'] = [
      '#type' => 'date',
      '#title' => $this->t('DOB'),
      '#required' => TRUE,
    ];

    $form['gender'] = [
      '#type' => 'date',
      '#title' => $this->t('Gender'),
      '#required' => TRUE,
    ];

    $options = [
      'male' => $this->t('MALE'),
      'female' => $this->t('Female'),
      'other' => $this->t('Other'),

    ];
    $form['gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#options' => $options,
      '#required' => TRUE,
    ];

    $main_options = [
      'bca' => $this->t('BCA'),
      'mca' => $this->t('MCA')

    ];
    $form['course'] = array(
      '#type' => 'select',
      '#options' => $main_options,
      '#title' => $this->t('Course'),
      '#required' => TRUE,
  );

  $form['university'] = [
    '#type' => 'textfield',
    '#title' => $this->t('University Name'),
    '#required' => TRUE,
  ];

  $form['passport'] = [
    '#type' => 'textfield',
    '#title' => $this->t('Passport number'),
    '#required' => TRUE,
  ];

    $form['my_captcha_element'] = array(
      '#type' => 'captcha',
      '#captcha_type' => 'captcha/Math',
    );
    
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Send'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    if (strlen($form_state->getValue('number')) != 10) {
      $form_state->setErrorByName('number', $this->t('The phone number must be 10 digits long.'));
    }

    $passport = $form_state->getValue('passport');
    if (!preg_match('/^[A-PR-WYa-pr-wy][1-9]\d{6}[A-Za-z]$/', $passport)) {
      $form_state->setErrorByName('passport', $this->t('The passport number is not valid.'));
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    $value = $form_state->getValues();
    $connection = Database::getConnection();
    $connection->insert('simpletest_data')
      ->fields([
        'fullname' => $value['fullname'],
        'email' => $value['email'],
        'number' => $value['number'],
        'address' => $value['address'],
        'dob' => $value['dob'],
        'gender' => $value['gender'],   
        'course' => $value['course'],
        'university' => $value['university'],
        'passport' => $value['passport'],
      ])
      ->execute();
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
