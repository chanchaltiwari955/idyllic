<?php

namespace Drupal\egypt_front\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\State\State;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Cache\Cache;

/**
 * Configuration Form for Front Page Settings.
 */
class MainInfoForm extends ConfigFormBase {
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
   * The entity type manager.
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface service.
   * @param Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The file usage service.
   */
  public function __construct(LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, State $state, MessengerInterface $messenger, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage) {
    $this->settings = 'egypt_front.main_info_form';
    $this->languageManager = $language_manager;
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->state = $state;
    $this->messenger = $messenger;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('language_manager'), $container->get('domain.negotiator'), $container->get('state'), $container->get('messenger'), $container->get('entity_type.manager'), $container->get('file.usage'),);
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
    return 'main_info_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $domain_id = '';
    $domain_id = $this->activeDomain;
    $language_name = $this->languageManager
      ->getCurrentLanguage()
      ->getId();
    $languages = $this->languageManager->getStandardLanguageList();
    $option = [];
    foreach ($languages as $key => $value) {
      $option[$key] = $value[0];
    }
    $form['#attributes']['enctype'] = "multipart/form-data";
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' => $this->t('Set the Language'),
      '#options' => $option,
      '#ajax' => [
        'callback' => 'egypt_front_ajax_main_info',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'egypt-main-info-section',
      ],
      '#default_value' => $this->state->get("language_{$language_name}_{$domain_id}"),
    ];

    $default_eg_mi_title = $this->state->get("eg_mi_title_{$language_name}_{$domain_id}");
    $eg_mi_desktop_image_fid = $this->state->get("eg_mi_desktop_image_{$language_name}_{$domain_id}");
    $eg_mi_mobile_image_fid = $this->state->get("eg_mi_mobile_image_{$language_name}_{$domain_id}");
    $default_eg_mi_descr = $this->state->get("eg_mi_descr_{$language_name}_{$domain_id}");

    $form['eg_main_info']['#tree'] = TRUE;
    $form['eg_main_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt main info configuration'),
      '#prefix' => '<div id="egypt-main-info-section">',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['eg_main_info']['#tree'] = TRUE;

    $form['eg_main_info']['eg_mi_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $default_eg_mi_title,
    ];

    $form['eg_main_info']['eg_mi_desktop_image'] = [
      '#title' => 'Desktop image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed on home page.Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>1440*501</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['1440x501', '1440x501'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/mi/desktop/',
      '#default_value' => $eg_mi_desktop_image_fid,
    ];

    $form['eg_main_info']['eg_mi_mobile_image'] = [
      '#title' => 'Mobile image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded mobile image will be displayed on home page.Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>320*530</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['320x530', '320x530'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/mi/mobile/',
      '#default_value' => $eg_mi_mobile_image_fid,
    ];

    $form['eg_main_info']['eg_mi_descr'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Please enter description about egypt'),
      '#size' => 40,
      '#default_value' => $default_eg_mi_descr,
      '#format' => 'full_html',
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
      $this->state->set("eg_mi_title_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_title']);
      $this->state->set("eg_mi_descr_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_descr']['value']);
      $this->state->set("eg_mi_mobile_image_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_mobile_image']);
      if (!empty($form_values['eg_main_info']['eg_mi_mobile_image'])) {
        $mi_mobile_image_fid = $this->entityTypeManager->getStorage('file')->load($form_values['eg_main_info']['eg_mi_mobile_image'][0]);
        $mi_mobile_image_fid->setPermanent();
        $mi_mobile_image_fid->save();
        $this->fileUsage->add($mi_mobile_image_fid, 'egypt_front', 'eg_mi_mobile_image', $domain_id);
      }
      $this->state->set("eg_mi_desktop_image_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_desktop_image']);
      if (!empty($form_values['eg_main_info']['eg_mi_desktop_image'])) {
        $eg_mi_desktop_fid = $this->entityTypeManager->getStorage('file')->load($form_values['eg_main_info']['eg_mi_desktop_image'][0]);
        $eg_mi_desktop_fid->setPermanent();
        $eg_mi_desktop_fid->save();
        $this->fileUsage->add($eg_mi_desktop_fid, 'egypt_front', 'eg_mi_desktop_image', $domain_id);
      }
      $this->messenger->addMessage($this->t("Main info settings have been saved for"));
    }
    else {
      $this->messenger->addMessage($this->t("Main info settings have not been saved"));
    }
    Cache::invalidateTags(["egypt_{$language_name}_{$domain_id}"]);
  }

}
