<?php

namespace Drupal\gpx_field\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\MapDataDefinition;
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
 *   default_formatter = "file_default",
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

    $settings = [
      'file_directory' => '',
      'file_extensions' => 'gpx',
      'max_filesize' => '',
      'description_field' => 0,

    ];

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['elevation'] = MapDataDefinition::create()
      ->setLabel(t('Elevation'));

    $properties['demotion'] = MapDataDefinition::create()
      ->setLabel(t('Demotion'));

    $properties['highest_point'] = MapDataDefinition::create()
      ->setLabel(t('Highest point'));

    $properties['lowest_point'] = MapDataDefinition::create()
      ->setLabel(t('Lowest point'));

    $properties['distance'] = MapDataDefinition::create()
      ->setLabel(t('Distance'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['elevation'] = [
      'description' => 'The elevation of the trip.',
      'type' => 'float',
    ];

    $schema['columns']['demotion'] = [
      'description' => 'The reduction of the trip.',
      'type' => 'float',
    ];

    $schema['columns']['highest_point'] = [
      'description' => 'The highest point of the trip.',
      'type' => 'float',
    ];

    $schema['columns']['lowest_point'] = [
      'description' => 'The lowest point of the trip.',
      'type' => 'float',
    ];

    $schema['columns']['distance'] = [
      'description' => 'The distance of the trip.',
      'type' => 'float',
      'unsigned' => TRUE,
    ];

    return $schema;
  }
}

