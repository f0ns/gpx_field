<?php

namespace Drupal\gpx_views\Plugin\views\row;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\row\RowPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\views\ViewsData;

/**
 * Plugin which formats a row as a leaflet marker.
 *
 * @ViewsRow(
 *   id = "leaflet_linestring",
 *   title = @Translation("Leaflet Linestring"),
 *   help = @Translation("Display the row as a leaflet linestring."),
 *   display_types = {"leaflet"},
 * )
 */
class LeafletLinestring extends RowPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Overrides Drupal\views\Plugin\Plugin::$usesOptions.
   */
  protected $usesOptions = TRUE;

  /**
   * Does the row plugin support to add fields to it's output.
   *
   * @var bool
   */
  protected $usesFields = TRUE;

  /**
   * The main entity type id for the view base table.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * The Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityManager;

  /**
   * The Entity Field manager service property.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The Entity Display Repository service property.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplay;

  /**
   * The View Data service property.
   *
   * @var \Drupal\views\ViewsData
   */
  protected $viewsData;

  /**
   * The Renderer service property.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $renderer;

  /**
   * Constructs a LeafletMap style instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityTypeManagerInterface $entity_manager
   *   The entity manager.
   * @param EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager.
   * @param EntityDisplayRepositoryInterface $entity_display
   *   The entity display manager.
   * @param RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_manager,
    EntityFieldManagerInterface $entity_field_manager,
    EntityDisplayRepositoryInterface $entity_display,
    RendererInterface $renderer,
    ViewsData $view_data
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityManager = $entity_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->entityDisplay = $entity_display;
    $this->renderer = $renderer;
    $this->viewsData = $view_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('entity_display.repository'),
      $container->get('renderer'),
      $container->get('views.views_data')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    // First base table should correspond to main entity type.
    $base_table = key($this->view->getBaseTables());
    $views_definition = $this->viewsData->get($base_table);
    $this->entityTypeId = $views_definition['table']['entity type'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    // Get a list of fields and a sublist of geo data fields in this view.
    // @todo use $fields = $this->displayHandler->getFieldLabels();
    $fields = [];
    $fields_gpx_data = [];
    foreach ($this->displayHandler->getHandlers('field') as $field_id => $handler) {

      $label = $handler->adminLabel() ?: $field_id;
      $fields[$field_id] = $label;

      $field_storage_definitions = $this->entityFieldManager
        ->getFieldStorageDefinitions($handler->getEntityType());
      $field_storage_definition = $field_storage_definitions[$handler->definition['field_name']];

      if ($field_storage_definition->getType() == 'gpx_file') {
        $fields_gpx_data[$field_id] = $label;
      }
    }

    // Check whether we have a geo data field we can work with.
    if (!count($fields_gpx_data)) {
      $form['error'] = [
        '#markup' => $this->t('Please add at least one gpx file field to the view.'),
      ];
      return;
    }

    // Map preset.
    $form['data_source'] = [
      '#type' => 'select',
      '#title' => $this->t('Data Source'),
      '#description' => $this->t('Which field contains the gpx data?'),
      '#options' => $fields_gpx_data,
      '#default_value' => $this->options['data_source'],
      '#required' => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($row) {

    $data = [];

    $entity = $row->_entity;
    $datasource = $this->options['data_source'];

    if (isset($entity->$datasource->points)) {
      $points = $entity->$datasource->points;
      $amount_of_points = count($points);

      $data = [
        [
          'type' => 'point',
          'lat' => $points[0]['lat'],
          'lon' => $points[0]['lon'],
          'icon' => [
            'iconUrl' => '/' . drupal_get_path('module', 'gpx_field') . '/images/start.png',
            'iconSize' => [17, 17],
            'iconAnchor' => [-17, -17]
          ]
        ],
        [
          'type' => 'linestring',
          'points' => $points,
          'options' => [
            'color' => '#2d5be3'
          ],
          'popup' => $entity->getTitle(),
        ],
        [
          'type' => 'point',
          'lat' => $points[$amount_of_points - 1]['lat'],
          'lon' => $points[$amount_of_points - 1]['lon'],
          'icon' => [
            'iconUrl' => '/' . drupal_get_path('module', 'gpx_field') . '/images/finish.png',
            'iconSize' => [17, 17],
            'iconAnchor' => [-17, -17]
          ]
        ],
      ];
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function validate() {
    $errors = parent::validate();
    // @todo raise validation error if we have no geofield.
    if (empty($this->options['data_source'])) {
      $errors[] = $this->t('Row @row requires the data source to be configured.', ['@row' => $this->definition['title']]);
    }
    return $errors;
  }
}
