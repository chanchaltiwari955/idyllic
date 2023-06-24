<?php

namespace Drupal\unfpa_offices_arabstate\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\State;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;

/**
 * Defines UnfpaDesignController class.
 */
class UnfpaDesignController extends ControllerBase {
  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected $state;
  /**
   * The Module Handler.
   *
   * @var Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $activeDomain;

  /**
   * Constructor method.
   *
   * @param Drupal\Core\State\State $state
   *   The object State.
   */

  /**
   * An alias manager to find the alias for the current system path.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Constructs for categories configuration.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\State\State $state
   *   The form state.
   * @param \Drupal\domain\DomainNegotiator $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   An alias manager to find the alias for the current system path.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entity_type_manager, State $state, FileUrlGeneratorInterface $file_url_generator, ModuleHandlerInterface $module_handler, DomainNegotiator $domain_negotiator, LanguageManagerInterface $language_manager, AliasManagerInterface $alias_manager) {
    global $base_url;
    $this->connection = $connection;
    $this->entityTypeManager = $entity_type_manager;
    $this->state = $state;
    $this->fileUrlGenerator = $file_url_generator;
    $this->moduleHandler = $module_handler;
    $this->module_path = $base_url . '/' . $this->moduleHandler->getModule('unfpa_offices_arabstate')->getPath();
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->languageManager = $language_manager;
    $this->aliasManager = $alias_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('entity_type.manager'),
      $container->get('state'),
      $container->get('file_url_generator'),
      $container->get('module_handler'),
      $container->get('domain.negotiator'),
      $container->get('language_manager'),
      $container->get('path_alias.manager'),
    );
  }

  /**
   * Arabstate configuration.
   *
   * @return array
   *   Return configs.
   */
  public function arabstate_new_design_landing_page() {
    $domain_id = '';
    $domain_id = $this->activeDomain;

    $user_language = $this->languageManager->getCurrentLanguage()->getId();
    global $base_url;
    $data[] = [
      'page_title' => !empty($this->state->get("page_title_{$user_language}_{$domain_id}")) ? $this->state->get("page_title_{$user_language}_{$domain_id}") : $this->state->get("page_title_en_{$domain_id}"),
      'page_description' => !empty($this->state->get("page_description_{$user_language}_{$domain_id}")) ? $this->state->get("page_description_{$user_language}_{$domain_id}") : $this->state->get("page_description_en_{$domain_id}"),
      'page_overview_title' => !empty($this->state->get("overview_title_{$user_language}_{$domain_id}")) ? $this->state->get("overview_title_{$user_language}_{$domain_id}") : $this->state->get("overview_title_en_{$domain_id}"),
      'page_overview_description' => !empty($this->state->get("overview_description_{$user_language}_{$domain_id}")) ? $this->state->get("overview_description_{$user_language}_{$domain_id}") : $this->state->get("overview_description_en_{$domain_id}"),
      'facebook_share_title' => !empty($this->state->get("facebook_share_title_{$user_language}_{$domain_id}")) ? $this->state->get("facebook_share_title_{$user_language}_{$domain_id}") : $this->state->get("facebook_share_title_en_{$domain_id}"),
      'facebook_share_description' => !empty($this->state->get("facebook_share_description_{$user_language}_{$domain_id}")) ? $this->state->get("facebook_share_description_{$user_language}_{$domain_id}") : $this->state->get("facebook_share_description_en_{$domain_id}"),
      'twitter_share_txt' => !empty($this->state->get("twitter_share_txt_{$user_language}_{$domain_id}")) ? $this->state->get("twitter_share_txt_{$user_language}_{$domain_id}") : $this->state->get("twitter_share_txt_en_{$domain_id}"),
      'graph' => $this->module_path . '/asset/image/home/graph.png',
    ];

    $facebook_share_image = $this->state->get("facebook_share_image_{$user_language}_{$domain_id}");
    if (!empty($facebook_share_image[0])) {
      // Load the file with the specified ID.
      $file = $this->entityTypeManager->getStorage('file')->load($facebook_share_image[0]);
      if (!empty($file)) {
        $img_uri = $file->getFileUri();
        $data['facebook_share_image'] = $this->fileUrlGenerator->generateAbsoluteString($img_uri);
      }
    }

    for ($i = 1; $i <= 6; $i++) {
      $block_image = $this->state->get("page_block_image{$i}_{$user_language}_{$domain_id}");
      if (!empty($block_image[0])) {
        $file = $this->entityTypeManager->getStorage('file')->load($block_image[0]);
        $block[$i]['block_image_url'] = $file ? $file->getFileUri() : '';
      }
      $block[$i]['block_title'] = !empty($this->state->get("page_block{$i}_title_{$user_language}_{$domain_id}")) ? $this->state->get("page_block{$i}_title_{$user_language}_{$domain_id}") : $this->state->get("page_block{$i}_title_en_{$domain_id}");
      $block[$i]['block_description'] = !empty($this->state->get("page_block{$i}_description_{$user_language}_{$domain_id}")) ? $this->state->get("page_block{$i}_description_{$user_language}_{$domain_id}") : $this->state->get("page_block{$i}_description_en_{$domain_id}");
    }
    $page_banner_video = !empty($this->state->get("page_banner_video_{$user_language}_{$domain_id}")) ? $this->state->get("page_banner_video_{$user_language}_{$domain_id}") : $this->state->get("page_banner_video_en_{$domain_id}");
    if (!empty($page_banner_video[0])) {
      // Load the file with the specified ID.
      $video_file = $this->entityTypeManager->getStorage('file')->load($page_banner_video[0]);
      if (!empty($video_file)) {
        $link_array = $video_file->getFileUri();
        $youtube_vid = $this->fileUrlGenerator->generateAbsoluteString($link_array);
        $data['page_banner_video'] = $youtube_vid;
      }
    }

    $data['country_filter'] = get_filters();
    $nids = $this->connection->select('node_field_data', 'n')
      ->fields('n', ['nid'])
      ->fields('n', ['type'])
      ->condition('n.type', 'ar_country_detail_page')
      ->condition('n.status', 1)
      ->execute()
      ->fetchCol();

    $country_data = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $content_array = [];
    $i = 0;
    if (is_array($country_data) && count($country_data) > 0) {
      foreach ($country_data as $c_data) {
        $alias = $base_url . '/' . $user_language . $this->aliasManager->getAliasByPath('/node/' . $c_data->id());
        $content_array[$i] = [
          'code' => $c_data->get('field_ar_country')->value,
          'name' => $c_data->getTitle(),
          'url' => $alias,
          'average' => $c_data->get('field_avarage_value')->value,
        ];

        if ($c_data->hasField('field_ar_category_details') && !empty($c_data->get('field_ar_category_details')->getValue())) {
          foreach ($c_data->get('field_ar_category_details')->getValue() as $ar_category_value) {
            $entities = Paragraph::load($ar_category_value['target_id']);
            $field = $entities->get('field_ar_category')->getValue();
            $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($field[0]['target_id']);
            $category_value = $entities->get('field_ar_category_value')->getValue();
            $term_name = !empty($term) ? $term->getName() : '';
            $content_array[$i][str_replace(' ', '_', strtolower($term_name))] = $category_value['value'];
          }
        }
        $c_data_reports = !empty($c_data->hasField('field_country_report_details')) ? $c_data->get('field_country_report_details')->getValue() : '';
        if (!empty($c_data_reports)) {
          foreach ($c_data_reports as $report_data) {
            $report_entity = Paragraph::load($report_data['target_id']);
            $pdf = !empty($report_entity->field_ar_country_upload_pdf->entity) ? $this->fileUrlGenerator->generateAbsoluteString($report_entity->get('field_ar_country_upload_pdf')->entity->getFileUri()) : '';
            $report_name = $report_entity->get('field_ar_country_report_name')->value;
            $data['report_array_type'][$c_data->getTitle()][] = ['name' => $report_name, 'pdf' => $pdf];
          }
        }
        $data['report_array'][] = $c_data->getTitle();
        $i++;
      }
    }
    if (empty($data['report_array_type'])) {
      $data['report_array_type'] = [];
    }

    return [
      '#theme' => 'page__arabstate_essential_services',
      '#data' => $data,
      '#block' => $block,
      '#attached' => [
        'library' => ['unfpa_offices_arabstate/unfpa_offices_arabstate_libraries'],
        'drupalSettings' => [
          'map_data' => [
            'm_data' => json_encode($content_array),
          ],
          'unfpa_offices_arabstate' => [
            'report_data' => json_encode($data['report_array_type']),
          ],
          'youtube_data' => [
            'youtube_link' => json_encode($youtube_vid),
          ],
        ],
      ],
      '#cache' => [
        'tags' => [
          "arabstate_{$user_language}_{$domain_id}",
        ],
      ],
    ];
  }

}
