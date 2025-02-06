<?php

namespace Drupal\pwa\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Respond to event processes.
 */
class ResponseSubscriber implements EventSubscriberInterface {

  /**
   * Additional headers to set on user change.
   *
   * @var array
   */
  const HEADERS = [
    'Clear-Site-Data' => 'storage',
  ];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::RESPONSE => [
        ['processResponse', 40],
      ],
    ];
  }

  /**
   * Clear serviceworker cache on user change.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   *
   * @see pwa_user_login()
   */
  public function processResponse(ResponseEvent $event) {
    $response = $event->getResponse();

    if ($event->getRequest()->get('_route') === 'user.logout') {
      $response->headers->add(static::HEADERS);
      return;
    }

    if (stripos($response->headers->get('Content-Type', ''), 'text/html') === FALSE) {
      return;
    }

    $session = $event->getRequest()->getSession();

    if ($session && $session->get('pwa_reset', FALSE)) {
      $response->headers->add(static::HEADERS);

      // Only once.
      $session->remove('pwa_reset');
    }
  }

}
