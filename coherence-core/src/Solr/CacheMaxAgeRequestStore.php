<?php

namespace Drupal\coherence_core\Solr;

/**
 * Used to store a max-age to be set on the response to the current request.
 *
 * @package Drupal\coherence_core\Solr
 */
class CacheMaxAgeRequestStore {

  protected $maxAge = NULL;

  public function setMaxAge($max_age) {
    $this->maxAge = $max_age;
  }

  public function getMaxAge() {
    return $this->maxAge;
  }

}
