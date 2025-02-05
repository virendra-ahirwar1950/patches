<?php

namespace Drupal\coherence_core\Plugin\Filter;

use Drupal\filter\Annotation\Filter;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;

/**
 * @Filter(
 *   id = "remove_script_tags",
 *   title = "Remove script tags",
 *   description = "Remove all script tags in the body",
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_HTML_RESTRICTOR
 * )
 *
 * @package Drupal\coherence_core\Plugin\Filter
 */
class RemoveScriptTags extends FilterBase {

  public function process($text, $langcode) {
    return new FilterProcessResult($this->removeTags($text, 'script'));
  }

  /**
   * @param $html
   * @param $tag
   *
   * @return mixed
   * @see https://stackoverflow.com/a/48362353/830680
   */
  function removeTags($html, $tag) {
    $dom = new \DOMDocument();
    $dom->loadHTML('<?xml encoding="utf-8" ?>' . $html);
    foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $item) {
      $item->parentNode->removeChild($item);
    }
    return $dom->saveHTML();
  }

}