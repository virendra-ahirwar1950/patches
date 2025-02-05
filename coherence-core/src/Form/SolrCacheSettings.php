<?php

namespace Drupal\coherence_core\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SolrCacheSettings extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'coherence_core_solr_cache_settings';
  }

  protected function getEditableConfigNames() {
    return ['coherence_core.solr_cache'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('coherence_core.solr_cache');

    $form['message'] = [
      '#markup' => '
<h2>Important</h2>
<p>
  Setting a max-age for a View will affect the max-age of any page that View 
  appears on. The internal page cache will be disabled for those pages.
</p>
<p>
  You must also manually set an identical time-based cache on each of the Views
  for which a non-disabled value is provided below.
</p>
',
    ];

    $form['views'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $options = [
      -1 => '- Disabled -',
      5 => '5 minutes',
      10 => '10 minutes',
      15 => '15 minutes',
      30 => '30 minutes',
      45 => '45 minutes',
      60 => '1 hour',
      120 => '2 hours',
      240 => '4 hours',
    ];

    $views = $this->getViewOptions();
    foreach ($views as $id => $label) {
      $form['views'][$id] = [
        '#type' => 'select',
        '#title' => $label,
        '#options' => $options,
        '#default_value' => $config->get('views')[$id] ?? -1,
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $data = [];
    foreach ($form_state->getValue('views') as $view_id => $value) {
      if ($value && $value > -1) {
        $data[$view_id] = $value;
      }
    }

    $this->config('coherence_core.solr_cache')
      ->set('views', $data)
      ->save();
  }

  protected function getViewOptions() {
    $views = $this->entityTypeManager
      ->getStorage('view')
      ->loadMultiple();

    $search_api_views = [];
    foreach ($views as $view) {
      $dependencies = $view->getDependencies();

      if (!empty($dependencies['config'])) {
        foreach ($dependencies['config'] as $item) {

          if (preg_match('/^search_api\.index\.(.+)$/', $item, $matches)) {
            $search_api_views[$view->id()] = $view->label();
          }
        }
      }
    }

    return $search_api_views;
  }

}
