<?php

namespace Drupal\egypt_front\Form;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\State\State;
use Drupal\Core\Cache\Cache;

/**
 * Configuration Form for Front Page Settings.
 */
class OutcomesForm extends ConfigFormBase {
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
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructor method.
   *
   * @var Drupal\Core\State\State
   *   The object State.
   */
  protected $state;

  /**
   * Constructs for front page configuration.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\domain\DomainNegotiator $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger services.
   * @param \Drupal\Core\State\State $state
   *   The State service.
   */
  public function __construct(LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, MessengerInterface $messenger, State $state) {
    $this->settings = 'egypt_front.outcomes_form';
    $this->languageManager = $language_manager;
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->messenger = $messenger;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('language_manager'), $container->get('domain.negotiator'), $container->get('messenger'), $container->get('state'));
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
    return 'outcomes_form';
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
        'callback' => 'egypt_front_ajax_outcomes_callback',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'egypt-header-section',
      ],
      '#default_value' => $this->state->get("language_{$language_name}_{$domain_id}"),
    ];

    $count = egypt_front_get_loop_count();
    $ones = [
      1 => "first",
      2 => "second",
      3 => "third",
      4 => "fourth",
      5 => "fifth",
    ];

    $form['outcomes'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Outcomes details'),
      '#prefix' => '<div id="my-outcomes-form">',
      '#suffix' => '</div>',
    ];
    $form['outcomes']['#tree'] = TRUE;
    for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
      $default_outcomes_dec = $this->state->get("outcomes_dec_{$language_name}_{$domain_id}_{$i}");
      $default_outcomes_title = $this->state->get("outcomes_title_{$language_name}_{$domain_id}_{$i}");
      $form['outcomes'][$i] = [
        '#type' => 'details',
        '#title' => $ones[$i] . ' ' . $this->t('Outcomes data'),
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
      ];

      $form['outcomes'][$i]['outcomes_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#size' => 100,
        '#default_value' => !empty($default_outcomes_title) ? $default_outcomes_title : '',
        '#maxlength' => 255,
      ];
      $form['outcomes'][$i]['description'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Description'),
        '#size' => 40,
        '#default_value' => $default_outcomes_dec,
        '#format' => 'full_html',
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

    $count = egypt_front_get_loop_count();
    $form_values = $form_state->getValues();
    $this->state->set("language_{$language_name}_{$domain_id}", $form_values['language']);
    if (!empty($domain_id)) {
      for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
        $this->state->set("outcomes_title_{$language_name}_{$domain_id}_{$i}", $form_values['outcomes'][$i]['outcomes_title']);
        $this->state->set("outcomes_dec_{$language_name}_{$domain_id}_{$i}", $form_values['outcomes'][$i]['description']['value']);
      }
      $this->messenger->addMessage($this->t("Our outcomes settings have been saved for"));
    }
    else {
      $this->messenger->addMessage($this->t("Our outcomes settings have not been saved"));
    }
    Cache::invalidateTags(["egypt_{$language_name}_{$domain_id}"]);
  }

}
