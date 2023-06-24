<?php

namespace Drupal\egypt_front\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\State\State;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Configuration Form for Front Page Settings.
 */
class EgyptHeaderForm extends ConfigFormBase {
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
  protected $language_manager;

  /**
   * The active domain.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $activeDomain;

  /**
   * Constructor method.
   *
   * @var Drupal\Core\State\State
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
   * The FileUsageInterface service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\State\State $state
   *   The State service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger services.
   * @param Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface service.
   */
  public function __construct(LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, State $state, MessengerInterface $messenger, FileUsageInterface $file_usage, EntityTypeManagerInterface $entity_type_manager) {
    $this->settings = 'egypt_front.egypt_header_form';
    $this->languageManager = $language_manager;
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->state = $state;
    $this->messenger = $messenger;
    $this->fileUsage = $file_usage;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('language_manager'),
    $container->get('domain.negotiator'),
    $container->get('state'),
    $container->get('messenger'),
    $container->get('file.usage'),
    $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [$this->settings];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'egypt_header_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $languages = $this->languageManager->getStandardLanguageList();
    $option = [];
    foreach ($languages as $key => $value) {
      $option[$key] = $value[0];
    }
    $domain_id = '';
    $domain_id = $this->activeDomain;
    $language_name = $this->languageManager
      ->getCurrentLanguage()
      ->getId();

    $form['#attributes']['enctype'] = "multipart/form-data";
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Set the Language'),
      '#options' => $option,
      '#ajax' => [
        'callback' => 'egypt_front_ajax_eg_header_config',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'egypt-header-section',
      ],
      '#default_value' => $this->state->get("language_{$language_name}_{$domain_id}"),
    ];

    $default_eg_title = $this->state->get("eg_title_{$language_name}_{$domain_id}");
    $default_eg_subtitle = $this->state->get("eg_subtitle_{$language_name}_{$domain_id}");
    $eg_desktop_image_fid = $this->state->get("eg_desktop_image_{$language_name}_{$domain_id}");
    $eg_mobile_image_fid = $this->state->get("eg_mobile_image_{$language_name}_{$domain_id}");
    $form['eg_header_config'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt Domain Header Configuration'),
      '#prefix' => '<div id="egypt-header-section">',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['eg_header_config']['#tree'] = TRUE;
    $form['eg_header_config']['eg_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $default_eg_title,
    ];

    $form['eg_header_config']['eg_subtitle'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Sub Title'),
      '#default_value' => $default_eg_subtitle,
    ];

    $form['eg_header_config']['eg_desktop_image'] = [
      '#title' => 'Desktop image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed on home page. Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>960*613</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['960x613', '960x613'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/front/desktop/',
      '#default_value' => $eg_desktop_image_fid,
    ];

    $form['eg_header_config']['eg_mobile_image'] = [
      '#title' => 'Mobile image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded mobile image will be displayed on The home page. Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>320*530</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['320x530', '320x530'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/front/mobile/',
      '#default_value' => $eg_mobile_image_fid,
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
    $language_name = $this->languageManager
      ->getCurrentLanguage()
      ->getId();
    $form_values = $form_state->getValues();
    $this->state->set("language_{$language_name}_{$domain_id}", $form_values['language']);
    if (!empty($domain_id)) {
      $this->state->set("eg_title_{$language_name}_{$domain_id}", $form_values['eg_header_config']['eg_title']);
      $this->state->set("eg_subtitle_{$language_name}_{$domain_id}", $form_values['eg_header_config']['eg_subtitle']);
      $this->state->set("eg_mobile_image_{$language_name}_{$domain_id}", $form_values['eg_header_config']['eg_mobile_image']);
      if (isset($form_values['eg_header_config']['eg_mobile_image']) && !empty($form_values['eg_header_config']['eg_mobile_image'])) {
        $eg_mobile_image = $this->entityTypeManager->getStorage('file')->load($form_values['eg_header_config']['eg_mobile_image'][0]);
        $eg_mobile_image->setPermanent();
        $eg_mobile_image->save();
        $this->fileUsage->add($eg_mobile_image, 'egypt_front', 'eg_mobile_image', $domain_id);
      }

      $this->state->set("eg_desktop_image_{$language_name}_{$domain_id}", $form_values["eg_header_config"]["eg_desktop_image"]);
      if (isset($form_values["eg_header_config"]["eg_desktop_image"]) && !empty($form_values["eg_header_config"]["eg_desktop_image"])) {
        $eg_desktop_img = $this->entityTypeManager->getStorage('file')->load($form_values["eg_header_config"]["eg_desktop_image"][0]);
        $eg_desktop_img->setPermanent();
        $eg_desktop_img->save();
        $this->fileUsage->add($eg_desktop_img, 'egypt_front', 'eg_desktop_image', $domain_id);
      }
      $this->messenger->addMessage($this->t('Header settings have been saved for'));
    }
    else {
      $this->messenger->addMessage($this->t("Header settings have not been saved"));
    }
    Cache::invalidateTags(["egypt_{$language_name}_{$domain_id}"]);
  }

}
