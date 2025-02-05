<?php

namespace Drupal\coherence_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ExtensionList;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class VersionController extends ControllerBase {

  /**
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * @var \Drupal\Core\Extension\ExtensionList
   */
  protected $moduleList;

  public function __construct(KillSwitch $kill_switch, ExtensionList $module_list) {
    $this->killSwitch = $kill_switch;
    $this->moduleList = $module_list;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('page_cache_kill_switch'),
      $container->get('extension.list.module')
    );
  }

  public function versions() {
    $versions = [
      $this->moduleList->getExtensionInfo('system')['version'],
      $this->moduleList->getExtensionInfo('cohesion')['version'],
      phpversion(),
    ];

    $this->killSwitch->trigger();
    return new JsonResponse($versions);
  }

}
