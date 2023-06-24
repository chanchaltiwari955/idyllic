<?php

namespace Drupal\unfpa_offices_arabstate\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\State;
use Drupal\Core\Cache\Cache;

/**
 * Configuration Form for Front Page Settings.
 */
class ArabstateConfigSettingsForm extends ConfigFormBase {

  /**
   * The form settings.
   *
   * @var \Drupal\Core\Form\ConfigFormBase
   */
  protected $settings;
  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $activeDomain;

  /**
   * The entityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The FileUsageInterface service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  protected $state;

  /**
   * Constructor method.
   *
   * @param Drupal\Core\State\State $state
   *   The object State.
   */

  /**
   * Constructs for front page configuration.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\domain\DomainNegotiator $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface service.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The FileUsageInterface service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger services.
   */
  public function __construct(LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage, MessengerInterface $messenger, State $state) {
    $this->settings = 'unfpa_offices_arabstate.arabstate_config_settings_form';
    $this->languageManager = $language_manager;
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
    $this->messenger = $messenger;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('domain.negotiator'),
      $container->get('entity_type.manager'),
      $container->get('file.usage'),
      $container->get('messenger'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->settings,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'arabstate_config_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $domain_id = '';
    $domain_id = $this->activeDomain;
    $language_name = $this->languageManager->getCurrentLanguage()->getId();
    $langcodes = $this->languageManager->getLanguages();
    $lan = array_keys($langcodes);
    $form['#tree'] = TRUE;
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Set the Language'),
      '#options' => $lan,
      '#ajax' => [
        'callback' => 'arabstate_configuration_page_callback',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'arabstate-configuration',
      ],
      '#default_value' => $language_name,
    ];
    $default_page_title = $this->state->get("page_title_{$language_name}_{$domain_id}");
    $default_page_description = $this->state->get("page_description_{$language_name}_{$domain_id}");
    $default_overview_title = $this->state->get("overview_title_{$language_name}_{$domain_id}");
    $default_overview_description = $this->state->get("overview_description_{$language_name}_{$domain_id}");
    $default_page_banner_video = $this->state->get("page_banner_video_{$language_name}_{$domain_id}");
    $default_share_title = $this->state->get("facebook_share_title_{$language_name}_{$domain_id}");
    $facebook_share_description = $this->state->get("facebook_share_description_{$language_name}_{$domain_id}");
    $twitter_share_txt = $this->state->get("twitter_share_txt_{$language_name}_{$domain_id}");
    $facebook_share_image = $this->state->get("facebook_share_image_{$language_name}_{$domain_id}");
    $form['arabstate_form'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Arabstate Configuration'),
      '#prefix' => '<div id="arabstate-configuration">',
      '#suffix' => '</div>',
    ];

    $form['arabstate_form']['page_banner_video'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload banner video'),
      '#name' => 'video[]',
      '#upload_validators' => [
        'file_validate_extensions' => ['mp4'],
      ],
      '#description' => $this->t('The uploaded video will be displayed on listing page.'),
      '#upload_location' => 'public://arabstate_video',
      '#default_value' => !empty($default_page_banner_video) ? $default_page_banner_video : '',
    ];

    $form['arabstate_form']['page_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Add page title'),
      '#default_value' => !empty($default_page_title) ? $default_page_title : '',
    ];

    $form['arabstate_form']['page_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description about page'),
      '#size' => 40,
      '#default_value' => $default_page_description,
      '#format' => 'full_html',
    ];

    $form['arabstate_form']['overview_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Overview title'),
      '#default_value' => $default_overview_title,
    ];

    $form['arabstate_form']['overview_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Overview Description'),
      '#size' => 40,
      '#default_value' => $default_overview_description,
      '#format' => 'full_html',
    ];

    for ($i = 1; $i <= 6; $i++) {
      $block_title = !empty($this->state->get("page_block{$i}_title_{$language_name}_{$domain_id}")) ? $this->state->get("page_block{$i}_title_{$language_name}_{$domain_id}") : '';
      $block_description = !empty($this->state->get("page_block{$i}_description_{$language_name}_{$domain_id}")) ? $this->state->get("page_block{$i}_description_{$language_name}_{$domain_id}") : '';
      $block_image = !empty($this->state->get("page_block_image{$i}_{$language_name}_{$domain_id}", [0])) ? $this->state->get("page_block_image{$i}_{$language_name}_{$domain_id}") : '';
      $form['arabstate_form']['page_block_image' . $i] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Upload block' . $i . ' image'),
        '#description' => $this->t('Allowed file extention: jpg png jpeg gif'),
        '#upload_validators' => [
          'file_validate_extensions' => ['jpg png jpeg gif'],
        ],
        '#upload_location' => 'public://images',
        '#default_value' => $block_image,
      ];
      $form['arabstate_form']['page_block_title' . $i] = [
        '#type' => 'textfield',
        '#title' => $this->t('Add block' . $i . ' title'),
        '#default_value' => $block_title,
      ];

      $form['arabstate_form']['page_block_des' . $i] = [
        '#type' => 'text_format',
        '#title' => $this->t('Add block' . $i . ' content'),
        '#size' => 20,
        '#default_value' => $block_description,
        '#format' => 'full_html',
      ];
    }
    $form['arabstate_form']['facebook_share_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Facebook share title'),
      '#default_value' => $default_share_title,
    ];
    $form['arabstate_form']['facebook_share_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Facebook share Description'),
      '#size' => 40,
      '#default_value' => $facebook_share_description,
      '#format' => 'full_html',
    ];

    $form['arabstate_form']['facebook_share_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload Facebook image'),
      '#description' => $this->t('Allowed file extention: jpg png jpeg gif'),
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg png jpeg'],
      ],
      '#upload_location' => 'public://images',
      '#default_value' => $facebook_share_image,
    ];

    $form['arabstate_form']['twitter_share_txt'] = [
      '#type' => 'textfield',
      '#title' => $this->t('twitter share text'),
      '#default_value' => $twitter_share_txt,
    ];

    $form_state->setCached(FALSE);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $domain_id = '';
    $domain_id = $this->activeDomain;
    $language_name = $this->languageManager->getCurrentLanguage()->getId();
    if (!empty($domain_id)) {
      $form_values = $form_state->getValues();
      $this->state->set("page_title_{$language_name}_{$domain_id}", $form_values['arabstate_form']['page_title']);
      $this->state->set("page_description_{$language_name}_{$domain_id}", $form_values['arabstate_form']['page_description']['value']);
      $this->state->set("overview_title_{$language_name}_{$domain_id}", $form_values['arabstate_form']['overview_title']);
      $this->state->set("overview_description_{$language_name}_{$domain_id}", $form_values['arabstate_form']['overview_description']['value']);
      $this->state->set("facebook_share_title_{$language_name}_{$domain_id}", $form_values['arabstate_form']['facebook_share_title']);
      $this->state->set("facebook_share_description_{$language_name}_{$domain_id}", $form_values['arabstate_form']['facebook_share_description']['value']);
      $this->state->set("twitter_share_txt_{$language_name}_{$domain_id}", $form_values['arabstate_form']['twitter_share_txt']);
      for ($i = 1; $i <= 6; $i++) {
        $form_values = $form_state->getValues();
        $this->state->set("page_block{$i}_title_{$language_name}_{$domain_id}", $form_values['arabstate_form']['page_block_title' . $i]);
        $this->state->set("page_block{$i}_description_{$language_name}_{$domain_id}", $form_values['arabstate_form']['page_block_des' . $i]['value']);
        $page_block_image = $this->state->get("page_block_image{$i}_{$language_name}_{$domain_id}");
        $this->save_image_file($form_values, 'page_block_image' . $i, $page_block_image[0], $domain_id);
      }
      $form_values = $form_state->getValues();
      $page_banner_video = $this->state->get("page_banner_video_{$language_name}_{$domain_id}");
      $this->save_image_file($form_values, 'page_banner_video', $page_banner_video, $domain_id);

      $form_values = $form_state->getValues();
      $facebook_share_image = $this->state->get("facebook_share_image_{$language_name}_{$domain_id}", [0]);
      $this->save_image_file($form_values, 'facebook_share_image', $facebook_share_image[0], $domain_id);
      Cache::invalidateTags(["arabstate_{$language_name}_{$domain_id}"]);
      $this->messenger->addMessage($this->t('The settings have been saved for'), 'status', TRUE);
    }
    else {
      $this->messenger->addMessage($this->t('The settings have not been saved'), 'status', FALSE);
    }
  }

  /**
   *
   */
  public function save_image_file($form_values, $element_name, $saved_file, $domain_id) {
    $language_name = $this->languageManager->getCurrentLanguage()->getId();
    if ($saved_file != $form_values['arabstate_form'][$element_name]) {
      if ($saved_file) {
        $old_file = $this->entityTypeManager->getStorage('file')->load($saved_file[0]);
        if ($old_file) {
          $old_file->delete();
        }
      }
      $this->state->set("{$element_name}_{$language_name}_{$domain_id}", $form_values['arabstate_form'][$element_name]);
      if (!empty($form_values['arabstate_form'][$element_name])) {
        $file = $this->entityTypeManager->getStorage('file')->load($form_values['arabstate_form'][$element_name][0]);
        $file->setPermanent();
        $file->save();
        $this->fileUsage->add($file, 'unfpa_offices_arabstate', $element_name, $domain_id);
      }
    }
  }

}
