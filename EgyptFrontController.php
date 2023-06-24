<?php

namespace Drupal\egypt_front\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\domain\DomainNegotiator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\State\State;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Defines EyptFrontController class.
 */
class EgyptFrontController extends ControllerBase {

  /**
   * The active domain.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $activeDomain;

  /**
   * The module handler interface service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * Constructor method.
   *
   * @var \Drupal\Core\State\State
   *   The object State.
   */
  protected $state;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs for front page configuration.
   *
   * @param \Drupal\domain\DomainNegotiator $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler interface service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\State\State $state
   *   The State service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface service.
   */
  public function __construct(DomainNegotiator $domain_negotiator, ModuleHandlerInterface $module_handler, LanguageManagerInterface $language_manager, State $state, EntityTypeManagerInterface $entity_type_manager) {
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->moduleHandler = $module_handler;
    $this->module_path = $this->moduleHandler->getModule('egypt_front')->getPath();
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    $container->get('domain.negotiator'),
    $container->get('module_handler'),
    $container->get('language_manager'),
    $container->get('state'),
    $container->get('entity_type.manager'));
  }

  /**
   * Egypt Front configuration Form.
   */
  public function egypt_front_configured_data() {
    $module_path = $this->module_path;
    $domain_id = '';
    $domain_id = $this->activeDomain;
    $language_name = $this->currentLanguage;
    $data = [];

    // Header data.
    $header_eg_title = $this->state->get("eg_title_{$language_name}_{$domain_id}");
    $header_eg_subtitle = $this->state->get("eg_subtitle_{$language_name}_{$domain_id}");
    $eg_desktop_image_fid = $this->state->get("eg_desktop_image_{$language_name}_{$domain_id}");

    if (!empty($eg_desktop_image_fid[0])) {
      $desktop_url_file = $this->entityTypeManager->getStorage('file')->load($eg_desktop_image_fid[0]);
      $desktop_url = $desktop_url_file->uri->value;
    }
    $eg_mobile_image_fid = $this->state->get("eg_mobile_image_{$language_name}_{$domain_id}");
    if (!empty($eg_mobile_image_fid[0])) {
      $mobile_url_file = $this->entityTypeManager->getStorage('file')->load($eg_mobile_image_fid[0]);
      $mobile_url = $mobile_url_file->uri->value;
    }
    $data['header_eg_title'] = !empty($header_eg_title) ? $header_eg_title : '';
    $data['header_eg_subtitle'] = !empty($header_eg_subtitle) ? $header_eg_subtitle : '';
    $data['desktop_url'][0] = !empty($desktop_url) ? $desktop_url : '';
    $data['mobile_url'][0] = !empty($mobile_url) ? $mobile_url : '';

    // Outcomes data.
    $count = egypt_front_get_loop_count();
    $outcome = [];
    for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
      $title = $this->state->get("outcomes_title_{$language_name}_{$domain_id}_{$i}");

      $description = $this->state->get("outcomes_dec_{$language_name}_{$domain_id}_{$i}");
      if (!empty($title) && !empty($description)) {
        $outcome[$i]['title'] = $title;
        $outcome[$i]['description'] = $description;
      }
    }

    // Main info data.
    $mi_title = $this->state->get("eg_mi_title_{$language_name}_{$domain_id}");
    $mi_descr = $this->state->get("eg_mi_descr_{$language_name}_{$domain_id}");
    $mi_desktop_image_fid = $this->state->get("eg_mi_desktop_image_{$language_name}_{$domain_id}");
    $mi_mobile_image_fid = $this->state->get("eg_mi_mobile_image_{$language_name}_{$domain_id}");
    if (!empty($mi_desktop_image_fid[0])) {
      $mi_desktop_img = $this->entityTypeManager->getStorage('file')->load($mi_desktop_image_fid[0]);
      $mi_desktop_url = $mi_desktop_img->uri[0]->value;
    }
    if (!empty($mi_mobile_image_fid[0])) {
      $mi_mobile_img = $this->entityTypeManager->getStorage('file')->load($mi_mobile_image_fid[0]);
      $mi_mobile_url = $mi_mobile_img->uri[0]->value;
    }
    $data['mi_title'] = !empty($mi_title) ? $mi_title : '';
    $data['mi_descr'] = !empty($mi_descr) ? $mi_descr : '';
    $data['mi_desktop_url'][0] = !empty($mi_desktop_url) ? $mi_desktop_url : '';
    $data['mi_mobile_url'][0] = !empty($mi_mobile_url) ? $mi_mobile_url : '';

    // Map List data.
    $map_country_list = egypt_front_map_country_list();
    $country = [];
    foreach ($map_country_list as $key => $val) {
      $eg_country_description = $this->state->get("eg_country_description_{$language_name}_{$domain_id}_{$key}");
      $eg_country_title = $this->state->get("eg_country_title_{$language_name}_{$domain_id}_{$key}");
      $eg_country_url = $this->state->get("eg_country_url_{$language_name}_{$domain_id}_{$key}");
      $eg_country_enable = $this->state->get("eg_country_enable_{$language_name}_{$domain_id}_{$key}");

      $country[$key]['eg_country_description'] = $eg_country_description;
      $country[$key]['eg_country_title'] = $eg_country_title;
      $country[$key]['eg_country_url'] = $eg_country_url;
      $country[$key]['eg_country_enable'] = $eg_country_enable;
    }

    $country_list_title = $this->state->get("country_list_title_{$language_name}_{$domain_id}");
    $country_list_description = $this->state->get("country_list_description_{$language_name}_{$domain_id}");

    $country['country_list_title'] = !empty($country_list_title) ? $country_list_title : '';
    $country['country_list_description'] = !empty($country_list_description) ? $country_list_description : '';
    return [
      '#theme' => 'page_EU_support_to_egypt',
      '#data' => $data,
      '#outcome' => $outcome,
      '#country' => $country,
      '#module_path' => $module_path,
      '#attached' => [
        'library' => ['egypt_front/egypt_front_library'],
      ],
      '#cache' => [
        'tags' => [
          "egypt_{$language_name}_{$domain_id}",
        ],
      ],
    ];
  }

}
