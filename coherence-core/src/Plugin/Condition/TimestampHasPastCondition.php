<?php

namespace Drupal\coherence_core\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Node has timestamp field in the past' condition.
 *
 * @Condition(
 *   id = "node_timestamp_past",
 *   label = @Translation("Node has timestamp in past")
 * )
 */
class TimestampHasPastCondition extends ConditionPluginBase implements ContainerFactoryPluginInterface
{

  /**
   * @var RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var EntityFieldManagerInterface
   */
  protected $fieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityFieldManagerInterface $field_manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->fieldManager = $field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state)
  {
    $field_map = $this->fieldManager->getFieldMapByFieldType('timestamp');
    if (isset($field_map['node']) && count($field_map['node'])) {
      $keys = array_keys($field_map['node']);

      $form['field'] = [
        '#title' => $this->t('Field'),
        '#type' => 'select',
        '#options' => ['' => '-- None --'] + array_combine($keys, $keys),
        '#default_value' => $this->configuration['field'] ?? '',
      ];
    } else {
      $form['error'] = [
        '#markup' => '<p>There are no node-attached timestamp fields in the system. This condition will return TRUE for all evaluations.</p>',
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    $this->configuration['field'] = $form_state->getValue('field');

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate()
  {
    // The condition should pass unless we're explicitly evaluating.
    if (!mb_strlen($this->configuration['field'])) {
      return TRUE;
    }

    // This should work on node view and node preview.
    $route_name = $this->routeMatch->getRouteName();

    $node = NULL;
    if ($route_name == 'entity.node.canonical') {
      $node = $this->routeMatch->getParameter('node');
    }
    elseif ($route_name == 'entity.node.preview') {
      $node = $this->routeMatch->getParameter('node_preview');
    }

    if ($node instanceof NodeInterface && $node->hasField($this->configuration['field'])) {
        $target_timestamp = $node->{$this->configuration['field']}->getValue();
        return !empty($target_timestamp[0]['value']) && strtotime($target_timestamp[0]['value']) < time();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function summary()
  {
    return $this->t('Condition to determine whether the value for a selected date field is in the past.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts()
  {
    return parent::getCacheContexts(); // TODO: Change the autogenerated stub
  }

}