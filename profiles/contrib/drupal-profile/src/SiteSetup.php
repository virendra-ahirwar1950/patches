<?php

namespace Drupal\coh_profile;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\ThemeInstallerInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\editor\EditorInterface;
use Drupal\shortcut\Entity\Shortcut;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;

/**
 * Various site setup tasks for a Coherence Cohesion install.
 *
 * @package Drupal\coh_profile
 */
class SiteSetup {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routeBuilder;

  /**
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Extension\ThemeInstallerInterface
   */
  protected $themeInstaller;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  public function __construct(
    ConfigFactoryInterface $config_factory,
    RouteBuilderInterface $route_builder,
    ModuleInstallerInterface $module_installer,
    ModuleHandlerInterface $module_handler,
    EntityTypeManagerInterface $entity_type_manager,
    ThemeInstallerInterface $theme_installer,
    ThemeHandlerInterface $theme_handler) {

    $this->configFactory = $config_factory;
    $this->routeBuilder = $route_builder;
    $this->moduleInstaller = $module_installer;
    $this->moduleHandler = $module_handler;
    $this->entityTypeManager = $entity_type_manager;
    $this->themeInstaller = $theme_installer;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Configure the admin user's role.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function configureAdminUser() {
    // Allow account creation by administrators only.
    $this->configFactory
      ->getEditable('user.settings')
      ->set('register', UserInterface::REGISTER_ADMINISTRATORS_ONLY)
      ->save(TRUE);

    // Assign user 1 the "administrator" role.
    $user = User::load(1);
    $user->roles[] = 'administrator';
    $user->save();

    return $this;
  }

  /**
   * Rebuild routes as menu links have been installed.
   */
  public function rebuildRoutes() {
    $this->routeBuilder->rebuildIfNeeded();

    return $this;
  }

  /**
   * Configure global shortcuts and permissions.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function configureShortcuts() {
    // Allow authenticated users to use shortcuts.
    user_role_grant_permissions(RoleInterface::AUTHENTICATED_ID, ['access shortcuts']);

    // Populate the default shortcut set.
    Shortcut::create([
      'shortcut_set' => 'default',
      'title' => t('Add content'),
      'weight' => -20,
      'link' => ['uri' => 'internal:/node/add'],
    ])->save();

    Shortcut::create([
      'shortcut_set' => 'default',
      'title' => t('All content'),
      'weight' => -19,
      'link' => ['uri' => 'internal:/admin/content'],
    ])->save();

    return $this;
  }

  /**
   * Configure front/back end themes.
   */
  public function configureThemes() {
    // Enable the admin theme for content edit pages.
    $this->configFactory
      ->getEditable('node.settings')
      ->set('use_admin_theme', TRUE)
      ->save(TRUE);

    // Install and set the Coherence custom theme to default.
    $this->themeInstaller->install(['coherence_custom']);
    $this->configFactory
      ->getEditable('system.theme')
      ->set('default', 'coherence_custom')
      ->save();

    return $this;
  }

  /**
   * Configure global Views settings.
   */
  public function configureViews() {
    $this->configFactory
      ->getEditable('views.settings')
      ->set('ui.show.advanced_column', TRUE)
      ->set('ui.show.display_embed', TRUE)
      ->save(TRUE);

    return $this;
  }

  /**
   * Disable caches/aggregation.
   */
  public function configureSystemPerformance() {
    $this->configFactory
      ->getEditable('system.performance')
      ->set('cache.page.max_age', 0)
      ->set('css.preprocess', 0)
      ->set('js.preprocess', 0)
      ->save(TRUE);

    return $this;
  }

  /**
   * Disable the frontpage (/node) View.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function disableFrontPageView() {
    // Disable the front page View, if it exists.
    $front_page_view = \Drupal::entityTypeManager()->getStorage('view')
      ->load('frontpage');

    if ($front_page_view) {
      $front_page_view->setStatus(FALSE)->save();
    }

    return $this;
  }

  /**
   * Enable Linkit in CKEditor.
   *
   * @return $this
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function configureCKEditor() {
    $editor = $this->entityTypeManager->getStorage('editor')->load('cohesion');
    if ($editor instanceof EditorInterface) {
      $settings = $editor->getSettings();
      $settings['plugins']['drupallink']['linkit_enabled'] = 'true';
      $settings['plugins']['drupallink']['linkit_profile'] = 'default';
      $editor->setSettings($settings);
      $editor->save();
    }

    return $this;
  }

  public function configureWebform() {
    $this->configFactory
      ->getEditable('webform.settings')
      // Advanced -> UI
      ->set('ui.video_display', 'hidden')
      ->set('ui.description_help', FALSE)
      ->set('ui.details_save', TRUE)
      ->set('ui.contribute_disabled', TRUE)
      ->set('ui.promotions_disabled', TRUE)
      // Advanced -> Requirements
      ->set('requirements.cdn', FALSE)
      ->set('requirements.bootstrap', FALSE)
      ->set('requirements.spam', TRUE)
      //Libraries -> External libraries
      ->set('libraries.excluded_libraries', [
        'ckeditor.autogrow',
        'ckeditor.codemirror',
        'ckeditor.fakeobjects',
        'ckeditor.image',
        'ckeditor.link',
        'codemirror',
        'jquery.chosen',
        'jquery.icheck',
        'jquery.intl-tel-input',
        'jquery.select2',
        'jquery.timepicker',
        'jquery.word-and-character-counter',
        'progress-tracker',
      ])
      ->save(TRUE);

    return $this;
  }

  public function configureSiteStudio() {
    $this->configFactory->getEditable('cohesion.settings')
      ->set('log_dx8_error', 'enable')
      ->set('animate_on_view_mobile', 'DISABLED')
      ->set('add_animation_classes', 'DISABLED')
      ->set('image_browser', [
        'config' => [
          'type' => 'imce_imagebrowser',
          'dx8_imce_stream_wrapper' => 'public',
        ],
        'content' => [
          'type' => 'imce_imagebrowser',
          'dx8_imce_stream_wrapper' => 'public',
        ],
      ])
      ->save();

    return $this;
  }

  public function installDevModules() {
    $modules = [
      'dblog',
      'cohesion_breakpoint_indicator',
    ];
    \Drupal::service('module_installer')->install($modules);

    return $this;
  }

}
