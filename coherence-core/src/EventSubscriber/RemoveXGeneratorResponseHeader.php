<?php
namespace Drupal\coherence_core\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RemoveXGeneratorResponseHeader implements EventSubscriberInterface {

  public function RemoveXGeneratorOptions(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $response->headers->remove('X-Generator');
  }

  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['RemoveXGeneratorOptions', -10];
    return $events;
  }
}
