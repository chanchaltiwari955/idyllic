<?php

namespace Drupal\egypt_front\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\State\State;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Cache\Cache;

/**
 * Configuration Form for Front Page Settings.
 */
class MapListForm extends ConfigFormBase {
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
   */
  public function __construct(LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, State $state, MessengerInterface $messenger) {
    $this->settings = 'egypt_front.map_list_form';
    $this->languageManager = $language_manager;
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->state = $state;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('language_manager'), $container->get('domain.negotiator'), $container->get('state'), $container->get('messenger'));
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
    return 'map_list_form';
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
        'callback' => 'egypt_front_ajax_map_country_list_callback',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'egypt-header-section',
      ],
      '#default_value' => $this->state->get("language_{$language_name}_{$domain_id}"),
    ];
    $default_country_list_title = $this->state->get("country_list_title_{$language_name}_{$domain_id}");
    $default_country_list_description = $this->state->get("country_list_description_{$language_name}_{$domain_id}");

    $form['egypt_map'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt Map country list data'),
      '#prefix' => '<div id="egypt-map-country-form">',
      '#suffix' => '</div>',
    ];
    $form['egypt_map']['#tree'] = TRUE;

    $form['egypt_map']['country_list_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country List Title'),
      '#size' => 40,
      '#default_value' => $default_country_list_title,
    ];
    $form['egypt_map']['country_list_description'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Description about country map data'),
      '#size' => 40,
      '#default_value' => $default_country_list_description,
      '#format' => 'full_html',
    ];
    $map_country_list = egypt_front_map_country_list();
    foreach ($map_country_list as $key => $val) {
      $default_eg_country_description = $this->state->get("eg_country_description_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_title = $this->state->get("eg_country_title_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_url = $this->state->get("eg_country_url_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_enable = $this->state->get("eg_country_enable_{$language_name}_{$domain_id}_{$key}");
      $form['egypt_map'][$key] = [
        '#type' => 'details',
        '#title' => $val . ' ' . $this->t('Regions'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      $form['egypt_map'][$key]['eg_country_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country title'),
        '#size' => 40,
        '#default_value' => $default_eg_country_title,
      ];
      $form['egypt_map'][$key]['eg_country_description'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Please enter description for ') . $val,
        '#size' => 40,
        '#default_value' => $default_eg_country_description,
        '#format' => 'full_html',
      ];
      $form['egypt_map'][$key]['eg_country_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#size' => 40,
        '#default_value' => $default_eg_country_url,
      ];
      $form['egypt_map'][$key]['eg_country_enable'] = [
        '#type' => 'select',
        '#title' => $this->t('Select the country to be enabled'),
        '#options' => [
          0 => $this->t('No'),
          1 => $this->t('Yes'),
        ],
        '#default_value' => $default_eg_country_enable,
        '#description' => $this->t('Set this to <em>Yes</em> if you would like this country to be enabled on the map.'),
      ];
    }

    $form_state->setCached(FALSE);
    return parent::buildForm($form, $form_state);
  }

  /**
   * Implements hook_form_submit() for header config data.
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
      $map_country_list = egypt_front_map_country_list();
      foreach ($map_country_list as $key => $val) {
        $this->state->set("eg_country_description_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_description']['value']);
        $this->state->set("eg_country_title_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_title']);
        $this->state->set("eg_country_url_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_url']);
        $this->state->set("eg_country_enable_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_enable']);
      }
      $this->state->set("country_list_title_{$language_name}_{$domain_id}", $form_values['egypt_map']['country_list_title']);
      $this->state->set("country_list_description_{$language_name}_{$domain_id}", $form_values['egypt_map']['country_list_description']['value']);

      $this->messenger->addMessage($this->t("The settings have been saved for"));
    }
    else {
      $this->messenger->addMessage($this->t("The settings have not been saved"));
    }

    Cache::invalidateTags(["egypt_{$language_name}_{$domain_id}"]);
  }

}
