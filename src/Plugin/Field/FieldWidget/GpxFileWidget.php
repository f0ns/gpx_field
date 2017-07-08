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
          $analyser = GpxAnalyser::create($gpx);
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