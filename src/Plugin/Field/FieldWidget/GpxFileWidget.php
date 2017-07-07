<?php

namespace Drupal\gpx_field\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;
use Drupal\gpx_field\GpxAnalyser;

/**
 * Plugin implementation of the 'prices' widget.
 *
 * @FieldWidget(
 *   id = "gpx_file",
 *   label = @Translation("GPX file"),
 *   field_types = {
 *     "gpx_file"
 *   }
 * )
 */
class GpxFileWidget extends FileWidget {

  protected $decimalFields;

  public function __construct($plugin_id, $plugin_definition, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, \Drupal\Core\Render\ElementInfoManagerInterface $element_info) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings, $element_info);
    $this->decimalFields = [
      'elevation',
      'demotion',
      'highest_point',
      'lowest_point',
      'distance',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['elevation'] = array(
      '#title' => $this->t('Elevation'),
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->elevation) ? $items[$delta]->elevation : NULL,
      '#weight' => 100,
    );
    $element['demotion'] = array(
      '#title' => $this->t('Demotion'),
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->demotion) ? $items[$delta]->demotion : NULL,
      '#weight' => 100,
    );
    $element['highest_point'] = array(
      '#title' => $this->t('Highest point'),
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->highest_point) ? $items[$delta]->highest_point : NULL,
      '#weight' => 100,
    );
    $element['lowest_point'] = array(
      '#title' => $this->t('Lowest point'),
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->lowest_point) ? $items[$delta]->lowest_point : NULL,
      '#weight' => 100,
    );
    $element['distance'] = array(
      '#title' => $this->t('Distance'),
      '#type' => 'hidden',
      '#default_value' => isset($items[$delta]->distance) ? $items[$delta]->distance : NULL,
      '#weight' => 100,
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);

    foreach ($values as &$value) {
      $value['elevation'] = 0;
      $value['demotion'] = 0;
      $value['highest_point'] = 0;
      $value['lowest_point'] = 0;
      $value['distance'] = 0;
      $value['points'] = [];
      if (!empty($value['target_id'])) {
        // Load the gpx file.
        if ($gpx = File::load($value['target_id'])) {
          $analyser = new GpxAnalyser($gpx);
          $value['elevation'] = $analyser->getElevation();
          $value['demotion'] = $analyser->getDemotion();
          $value['highest_point'] = $analyser->getHighestPoint();
          $value['lowest_point'] = $analyser->getLowestPoint();
          $value['distance'] = $analyser->getDistance();
          $value['points'] = $analyser->getCoordinates();
        }
      }
    }

    return $values;
  }
}