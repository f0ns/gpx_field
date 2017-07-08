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

    $properties['elevation'] = DataDefinition::create('any')
      ->setLabel(t('Elevation'));

    $properties['demotion'] = DataDefinition::create('any')
      ->setLabel(t('Demotion'));

    $properties['highest_point'] = DataDefinition::create('any')
      ->setLabel(t('Highest point'));

    $properties['lowest_point'] = DataDefinition::create('any')
      ->setLabel(t('Lowest point'));

    $properties['distance'] = DataDefinition::create('any')
      ->setLabel(t('Distance'));

    $properties['points'] = DataDefinition::create('any')
      ->setLabel(t('Points'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['elevation'] = [
      'description' => 'The elevation of the trip.',
      'type' => 'numeric',
      'precision' => 10,
      'scale' => 2,
      'not null' => TRUE,
      'default' => 0
    ];

    $schema['columns']['demotion'] = [
      'description' => 'The reduction of the trip.',
      'type' => 'numeric',
      'precision' => 10,
      'scale' => 2,
      'not null' => TRUE,
      'default' => 0
    ];

    $schema['columns']['highest_point'] = [
      'description' => 'The highest point of the trip.',
      'type' => 'numeric',
      'precision' => 10,
      'scale' => 2,
      'not null' => TRUE,
      'default' => 0
    ];

    $schema['columns']['lowest_point'] = [
      'description' => 'The lowest point of the trip.',
      'type' => 'numeric',
      'precision' => 10,
      'scale' => 2,
      'not null' => TRUE,
      'default' => 0
    ];

    $schema['columns']['distance'] = [
      'description' => 'The distance of the trip.',
      'type' => 'numeric',
      'unsigned' => TRUE,
      'precision' => 10,
      'scale' => 2,
      'not null' => TRUE,
      'default' => 0
    ];

    $schema['columns']['points'] = [
      'description' => 'All the coordinates of the trip.',
      'type' => 'blob',
      'size' => 'big',
      'not null' => FALSE,
      'serialize' => TRUE,
    ];

    return $schema;
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

