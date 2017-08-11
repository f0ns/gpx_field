<?php

namespace Drupal\gpx_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Plugin\Field\FieldType\FileItem;

/**
 * Plugin implementation of the 'gpx file' field type.
 *
 * @FieldType(
 *   id = "gpx_file",
 *   label = @Translation("GPX file"),
 *   description = @Translation("This field stores a GPX file"),
 *   category = @Translation("Reference"),
 *   default_widget = "gpx_file",
 *   default_formatter = "gpx_map",
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class GpxFile extends FileItem {
  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();
    $settings['file_extensions'] = 'gpx';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['elevation'] = DataDefinition::create('float')
      ->setLabel(t('Elevation'));

    $properties['demotion'] = DataDefinition::create('float')
      ->setLabel(t('Demotion'));

    $properties['highest_point'] = DataDefinition::create('float')
      ->setLabel(t('Highest point'));

    $properties['lowest_point'] = DataDefinition::create('float')
      ->setLabel(t('Lowest point'));

    $properties['distance'] = DataDefinition::create('float')
      ->setLabel(t('Distance'));

    $properties['points'] = DataDefinition::create('any')
      ->setLabel(t('Points'));

    $properties['elevation_profile'] = DataDefinition::create('any')
      ->setLabel(t('Elevation profile'));

    $properties['start_lat'] = DataDefinition::create('float')
      ->setLabel(t('Start point latitude'));

    $properties['start_lon'] = DataDefinition::create('float')
      ->setLabel(t('Start point longitude'));

    $properties['start_lat_sin'] = DataDefinition::create('float')
      ->setLabel(t('Start point latitude sine'));

    $properties['start_lat_cos'] = DataDefinition::create('float')
      ->setLabel(t('Start point latitude cosine'));

    $properties['start_lon_rad'] = DataDefinition::create('float')
      ->setLabel(t('Start point longitude radian'));

    $properties['end_lat'] = DataDefinition::create('float')
      ->setLabel(t('End point latitude'));

    $properties['end_lon'] = DataDefinition::create('float')
      ->setLabel(t('End point longitude'));

    $properties['end_lat_sin'] = DataDefinition::create('float')
      ->setLabel(t('End point latitude sine'));

    $properties['end_lat_cos'] = DataDefinition::create('float')
      ->setLabel(t('End point latitude cosine'));

    $properties['end_lon_rad'] = DataDefinition::create('float')
      ->setLabel(t('End point longitude radian'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema_extra['columns'] = [
      'elevation' => [
        'description' => 'The elevation of the route.',
        'type' => 'numeric',
        'precision' => 10,
        'scale' => 2,
        'not null' => TRUE,
        'default' => 0
      ],
      'demotion' => [
        'description' => 'The demotion of the route.',
        'type' => 'numeric',
        'precision' => 10,
        'scale' => 2,
        'not null' => TRUE,
        'default' => 0
      ],
      'highest_point' => [
        'description' => 'The highest point of the route.',
        'type' => 'numeric',
        'precision' => 10,
        'scale' => 2,
        'not null' => TRUE,
        'default' => 0
      ],
      'lowest_point' => [
        'description' => 'The lowest point of the route.',
        'type' => 'numeric',
        'precision' => 10,
        'scale' => 2,
        'not null' => TRUE,
        'default' => 0
      ],
      'distance' => [
        'description' => 'The distance of the route.',
        'type' => 'numeric',
        'unsigned' => TRUE,
        'precision' => 10,
        'scale' => 2,
        'not null' => TRUE,
        'default' => 0
      ],
      'points' => [
        'description' => 'All the coordinates of the route.',
        'type' => 'blob',
        'size' => 'big',
        'not null' => FALSE,
        'serialize' => TRUE,
      ],
      'elevation_profile' => [
        'description' => 'The elevation profile of the route.',
        'type' => 'blob',
        'size' => 'big',
        'not null' => FALSE,
        'serialize' => TRUE,
      ],
      'start_lat' => [
        'description' => 'Latitude of start point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'start_lon' => [
        'description' => 'Longitude of start point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'start_lat_sin' => [
        'description' => 'Latitude sine of start point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'start_lat_cos' => [
        'description' => 'Latitude cosine of start point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'start_lon_rad' => [
        'description' => 'Longitude radian of start point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'end_lat' => [
        'description' => 'Latitude of end point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'end_lon' => [
        'description' => 'Longitude of end point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'end_lat_sin' => [
        'description' => 'Latitude sine of end point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'end_lat_cos' => [
        'description' => 'Latitude cosine of end point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
      'end_lon_rad' => [
        'description' => 'Longitude radian of end point',
        'type' => 'float',
        'size' => 'big',
        'not null' => TRUE,
      ],
    ];

    $schema = array_merge_recursive($schema, $schema_extra);

    return $schema;
  }
  
  /**
   * {@inheritdoc}
   */
  public function preSave() {
    parent::preSave();
    $points = $this->get('points')->getValue();
    $start_point = reset($points);
    $end_point = end($points);

    // Set and calculate all starting point values.
    $this->get('start_lat')->setValue($start_point['lat']);
    $this->get('start_lon')->setValue($start_point['lon']);
    $this->get('start_lat_sin')->setValue(sin(deg2rad($start_point['lat'])));
    $this->get('start_lat_cos')->setValue(cos(deg2rad($start_point['lat'])));
    $this->get('start_lon_rad')->setValue(deg2rad($start_point['lon']));

    // Set and calculate all end point values.
    $this->get('end_lat')->setValue($end_point['lat']);
    $this->get('end_lon')->setValue($end_point['lon']);
    $this->get('end_lat_sin')->setValue(sin(deg2rad($end_point['lat'])));
    $this->get('end_lat_cos')->setValue(cos(deg2rad($end_point['lat'])));
    $this->get('end_lon_rad')->setValue(deg2rad($end_point['lon']));
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Disable allowed file extensions.
    $element = parent::fieldSettingsForm($form, $form_state);
    $element['file_extensions']['#disabled'] = TRUE;

    return $element;
  }
}

