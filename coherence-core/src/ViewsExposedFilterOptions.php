<?php

namespace Drupal\coherence_core;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\views\ViewExecutable;
use Drupal\views\Views;
use Symfony\Component\HttpFoundation\RequestStack;

class ViewsExposedFilterOptions {

  /**
   * @var null|\Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  public function __construct(RequestStack $request_stack, RouteMatchInterface $route_match, ModuleHandlerInterface $module_handler) {
    $this->request = $request_stack->getCurrentRequest();
    $this->routeMatch = $route_match;
    $this->moduleHandler = $module_handler;
  }

  public function getUrls(array $links, string $filter_id) {
    $urls = [];
    foreach ($links as $filter_value => $link) {
      $active =  $this->filterIsActive($filter_id, $filter_value);
      $urls[$filter_value] = $this->buildUrl($filter_id, $filter_value, $active);
    }

    return $urls;
  }

  function getRemoveLinksFromExposedForm(array $form) {
    $current_query = $this->request->query->all();
    $remove_links = [];

    foreach ($current_query as $filter_id => $filter_values) {
      if (is_array($filter_values)) {
        foreach ($filter_values as $filter_value) {
          if (isset($form[$filter_id]['#options'][$filter_value])) {
            $remove_links[] = [
              '#type' => 'link',
              '#title' => $form[$filter_id]['#options'][$filter_value],
              '#url' => $this->buildUrl($filter_id, $filter_value, TRUE),
              '#attributes' => [
                'rel' => 'nofollow',
              ],
            ];
          }
        }
      }
    }

    return $remove_links;
  }

  protected function buildUrl($filter_id, $filter_value, $active) {
    // Filter the current query args with this option either added or removed.
    $new_query_args = $this->getLinkQueryArgs($filter_id, $filter_value, $active);

    $url = Url::createFromRequest($this->request);
    $url->setOption('query', $new_query_args);

    return $url;
  }

  protected function getLinkQueryArgs($filter_id, $filter_value, $active) {
    $query_args = $this->request->query->all();

    if (isset($query_args['page'])) {
      unset($query_args['page']);
    }

    if (isset($query_args[$filter_id]) && is_array($query_args[$filter_id])) {
      if ($active) {
        $arg_index = array_search($filter_value, $query_args[$filter_id]);
        if ($arg_index !== FALSE) {
          unset($query_args[$filter_id][$arg_index]);
        }

        if (empty($query_args[$filter_id])) {
          unset($query_args[$filter_id]);
        }
      }
      elseif (!in_array($filter_value, $query_args[$filter_id])) {
        $query_args[$filter_id][] = $filter_value;
      }
    }
    else {
      $query_args[$filter_id] = [$filter_value];
    }

    ksort($query_args);

    return $query_args;
  }

  protected function filterIsActive($filter_id, $value) {
    $query = $this->request->query->all();
    return !empty($query[$filter_id]) && in_array($value, $query[$filter_id]);
  }

  protected function getCurrentViewExecutable() : ?ViewExecutable {
    static $current_executable = NULL;

    if (!$current_executable) {
      $route = $this->routeMatch->getRouteObject();
      if ($route) {
        // Get view id and display id from route.
        $view_id = $route->getDefault('view_id');
        $display_id = $route->getDefault('display_id');
        // If you just want the page title, you could get it directly from the
        // route object. Unfortunately, it will be untranslated, so if we want
        // to get the translated title, we still need to load the view object.
        // $route->getDefault('_title');
        if (!empty($view_id) && !empty($display_id)) {
          // Get the view by id.
          $current_executable = Views::getView($view_id);
        }
      }
    }

    return $current_executable;
  }

  public function isEnabled($view_id, $display_id) {
    $statuses = \Drupal::moduleHandler()->invokeAll('coherence_core_bef_links_enabled_for_view', [$view_id, $display_id]);
    foreach ($statuses as $status) {
      if ($status === TRUE) {
        return TRUE;
      }
    }

    // Default to disabled.
    return FALSE;
  }

}