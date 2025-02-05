<?php

namespace Drupal\coherence_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveXGeneratorResponseHeader implements EventSubscriberInterface
{

  /**
   * Removes the X-Generator header from the response.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The event to process.
   */

  public function RemoveXGeneratorOptions(ResponseEvent $event)
  {
    $response = $event->getResponse();
    $response->headers->remove('X-Generator');
  }
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents()
  {
    $events[KernelEvents::RESPONSE][] = ['RemoveXGeneratorOptions', -10];
    return $events;
  }
}
