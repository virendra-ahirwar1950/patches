<?php

function coh_profile_install_tasks(&$install_state) {
  return [
    'coh_profile_cohesion_import_batch' => [
      'display_name' => t('Import cohesion assets'),
      'display' => TRUE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
    'coh_profile_cohesion_rebuild_batch' => [
      'display_name' => t('Rebuild Cohesion config'),
      'display' => TRUE,
      'type' => 'batch',
      'run' => INSTALL_TASK_RUN_IF_NOT_COMPLETED,
    ],
  ];
}

function coh_profile_form_install_configure_form_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state, $form_id) {
  $form['cohesion'] = [
    '#type' => 'fieldgroup',
    '#title' => t('Cohesion configuration'),
    '#weight' => -100,
  ];

  $config = \Drupal::config('cohesion.settings');

  $form['cohesion']['cohesion_api_key'] = [
    '#type' => 'textfield',
    '#title' => t('API key'),
    '#required' => TRUE,
    '#default_value' => $config ? ($config->get('api_key') ?? '') : '',
  ];

  $form['cohesion']['cohesion_organization_key'] = [
    '#type' => 'textfield',
    '#title' => t('Agency key'),
    '#required' => TRUE,
    '#default_value' => $config ? ($config->get('organization_key') ?? '') : '',
  ];

  $form['#submit'][] = 'coh_profile_install_configure_form_submit';
}

function coh_profile_install_configure_form_submit(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  \Drupal::configFactory()->getEditable('cohesion.settings')
    ->set('api_key', $form_state->getValue('cohesion_api_key'))
    ->set('organization_key', $form_state->getValue('cohesion_organization_key'))
    ->set('site_id', \Drupal::config('system.site')->get('uuid'))
    ->set('api_url', \Drupal::service('cohesion.api.utils')->getAPIServerURL())
    ->save();
}

function coh_profile_cohesion_import_batch() {
  $batch = \Drupal\cohesion\Controller\AdministrationController::batchAction(TRUE);
  if (isset($batch[0]['error'])) {

    \Drupal::messenger()->addStatus($batch[0]['error']);

    return [
      'operations' => [],
    ];
  }
  return $batch;
}

function coh_profile_cohesion_rebuild_batch() {
  $controller = \Drupal::service('class_resolver')
    ->getInstanceFromDefinition(Drupal\cohesion_website_settings\Controller\WebsiteSettingsController::class);

  $batch = $controller->batch(TRUE);
  if (isset($batch[0]['error'])) {
    \Drupal::messenger()->addStatus($batch[0]['error']);

    return [
      'operations' => [],
    ];
  }
  return $batch;
}