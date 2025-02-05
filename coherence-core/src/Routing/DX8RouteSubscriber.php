<?php

namespace Drupal\coherence_core\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class DX8RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    if ($route = $collection->get('cohesion_sync.import')) {
      $route->setRequirement('_permission', 'import dx8 configuration');
    }

    if ($route = $collection->get('cohesion_sync.export_multiple')) {
      $route->setRequirement('_permission', 'export dx8 configuration');
    }

    if ($route = $collection->get('cohesion_sync.export_all')) {
      $route->setRequirement('_permission', 'export dx8 configuration');
    }

    if ($route = $collection->get('cohesion_sync.operation_export_single')) {
      $route->setRequirement('_permission', 'export dx8 configuration');
    }
  }

}