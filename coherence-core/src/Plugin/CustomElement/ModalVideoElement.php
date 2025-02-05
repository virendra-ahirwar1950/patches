<?php

namespace Drupal\coherence_core\Plugin\CustomElement;

use Drupal\cohesion_elements\Annotation\CustomElement;
use Drupal\cohesion_elements\CustomElementPluginBase;
use Drupal\Core\Annotation\Translation;

/**
 * Class VimeoModalElement
 *
 * @package Drupal\coherence_core\Plugin\CustomElement
 *
 * @CustomElement(
 *   id = "modal_video",
 *   label = @Translation("Modal video")
 * )
 */
class ModalVideoElement extends CustomElementPluginBase {

  public function getFields() {
    return [
      'type' => [
        'htmlClass' => 'col-xs-12',
        'type' => 'select',
        'title' => 'Video source',
        'nullOption' => false,
        'options' => [
          'youtube' => 'YouTube',
          'vimeo' => 'Vimeo',
        ]
      ],
      'title' => [
        'htmlClass' => 'col-xs-12',
        'type' => 'textfield',
        'title' => 'Title (for image alt text)',
        'placeholder' => 'e.g. About us'
      ],
      'video_id' => [
        'htmlClass' => 'col-xs-12',
        'type' => 'textfield',
        'title' => 'Video ID',
        'placeholder' => 'e.g. 48747497',
      ],
      'screenshot' => [
        'htmlClass' => 'col-xs-12',
        'type' => 'image',
        'title' => 'Screenshot',
        'buttonText' => 'Choose image',
      ],
    ];
  }

  public function render($element_settings, $element_markup, $element_class) {
    return [
      '#theme' => 'coherence_core_modal_video',
      '#title' => $element_settings['title'],
      '#type' => $element_settings['type'],
      '#video_id' => $element_settings['video_id'],
      '#screenshot' => $element_settings['screenshot'],
      '#dx8_markup' => $element_markup,
      '#dx8_class' => $element_class,
      '#attached' => ['library' => ['coherence_core/lity']],
    ];
  }

}