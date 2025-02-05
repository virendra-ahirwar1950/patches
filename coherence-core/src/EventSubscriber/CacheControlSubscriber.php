<?php

namespace Drupal\coherence_core\EventSubscriber;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

class CacheControlSubscriber implements EventSubscriberInterface {

  /**
   * Overrides cache control header if any of override methods are enabled and conditions met.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */
  public function onRespond(ResponseEvent $event) {
    if (!$event->isMainRequest()) {
      return;
    }

    $response = $event->getResponse();

    // If the current response isn't an implementation of the
    // CacheableResponseInterface, then there is nothing we can override.
    if (!$response instanceof CacheableResponseInterface) {
      return;
    }

    $max_age = \Drupal::service('coherence_core.max_age_request_store')
      ->getMaxAge();

    // We treat permanent cache max-age as default therefore we don't override
    // the max-age.
    if ($max_age !== NULL && $max_age != Cache::PERMANENT) {
      $response->headers->set('Cache-Control', 'public, max-age=' . $max_age);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
