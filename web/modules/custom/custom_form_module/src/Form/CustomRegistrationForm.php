<?php

namespace Drupal\custom_form_module\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Mail\MailManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;

class CustomRegistrationForm extends FormBase {

  protected $mailManager;
  protected $messenger;

  public function __construct(MailManagerInterface $mail_manager, MessengerInterface $messenger) {
    $this->mailManager = $mail_manager;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.mail'),
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'custom_registration_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['full_name'] = ['#type' => 'textfield', '#title' => $this->t('Full Name'), '#required' => TRUE];
    $form['email'] = ['#type' => 'email', '#title' => $this->t('Email Address'), '#required' => TRUE];
    $form['phone'] = ['#type' => 'textfield', '#title' => $this->t('Phone Number'), '#required' => TRUE, '#maxlength' => 10];
    $form['address'] = ['#type' => 'textarea', '#title' => $this->t('Address'), '#required' => TRUE];
    $form['gender'] = [
      '#type' => 'radios',
      '#title' => $this->t('Gender'),
      '#required' => TRUE,
      '#options' => ['Male' => $this->t('Male'), 'Female' => $this->t('Female'), 'Other' => $this->t('Other')],
    ];
    $form['dob'] = ['#type' => 'date', '#title' => $this->t('Date of Birth'), '#required' => TRUE];
    $form['course'] = [
      '#type' => 'select',
      '#title' => $this->t('Course Name'),
      '#options' => ['' => '- Select -', 'B.Tech' => 'B.Tech', 'MBA' => 'MBA', 'M.Tech' => 'M.Tech', 'Other' => 'Other'],
      '#required' => TRUE,
    ];
    $form['university'] = ['#type' => 'textfield', '#title' => $this->t('University / Institution Name'), '#required' => TRUE];
    $form['passport'] = ['#type' => 'textfield', '#title' => $this->t('Passport Number'), '#required' => TRUE, '#maxlength' => 10];

    $form['captcha'] = ['#type' => 'captcha', '#captcha_type' => 'default'];

    $form['actions']['submit'] = ['#type' => 'submit', '#value' => $this->t('Submit')];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if (!preg_match('/^[0-9]{10}$/', $form_state->getValue('phone'))) {
      $form_state->setErrorByName('phone', $this->t('Phone number must be 10 digits.'));
    }
    if (!preg_match('/^[A-Za-z0-9]{8,10}$/', $form_state->getValue('passport'))) {
      $form_state->setErrorByName('passport', $this->t('Passport number must be alphanumeric, 8–10 characters.'));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $data = $form_state->getValues();
    Database::getConnection()->insert('custom_form_submissions')->fields([
      'full_name' => $data['full_name'],
      'email' => $data['email'],
      'phone' => $data['phone'],
      'address' => $data['address'],
      'gender' => $data['gender'],
      'dob' => $data['dob'],
      'course' => $data['course'],
      'university' => $data['university'],
      'passport' => $data['passport'],
      'created' => time(),
    ])->execute();

    $params = ['subject' => 'Form Submission Received', 'message' => 'Dear ' . $data['full_name'] . ', your form was submitted successfully.'];
    $this->mailManager->mail('custom_form_module', 'user_confirmation', $data['email'], 'en', $params);
    $admin_mail = \Drupal::config('system.site')->get('mail');
    $this->mailManager->mail('custom_form_module', 'admin_alert', $admin_mail, 'en', $params);

    $this->messenger->addMessage($this->t('Form submitted successfully!'));
  }
}
