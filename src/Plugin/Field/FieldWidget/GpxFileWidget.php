<?php

namespace Drupal\gpx_field\Plugin\Field\FieldWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\file\Plugin\Field\FieldWidget\FileWidget;

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
    kint($values);
    foreach ($values as $delta => &$value) {
      if(isset($value['target_id']) && $value['target_id'] !== NULL) {
        $gpx_file = File::load($value['target_id']);
        $gpx_details = gpx_field_get_gpx_file_details($gpx_file);
        //dsm($gpx_details);
      }
    }
  }

  /**
   * Returns with the details of the gpx file.
   *
   * @param $gpx_file
   *   The gpx file object.
   *
   * @return
   *   An array with the following keys:
   *   - elevation
   *   - demotion
   *   - highest_point
   *   - lowest_point
   *   - distance
   *   - distance_array
   *   - ele_array
   *   - difficulty_array
   *   - points
   */
  protected function gpx_field_get_gpx_file_details($gpx_file) {
    // Always store the previous trackpoint.
    $point_before = NULL;
    // Iterator number.
    $i = 0;
    // Default divisor number.
    $divisor = 1;

    // File details.
    $highest_point = NULL;
    $lowest_point = NULL;
    $distance = 0;
    $demotion = 0;
    $elevation = 0;
    // Difficulty array.
    $difficulty = array(
      'last_distance' => 0,
      'last_high' => 0,
      'difficulties' => array(
        'descent' => 0,
        'downhill' => 0,
        'flat' => 0,
        'uphill' => 0,
        'rise' => 0,
      ),
    );

    // Trackpoint data.
    $distance_array = array();
    $ele_array = array();
    $points = array();

    //dsm($gpx_file);

    // Load the uploaded file.
    $doc = new DOMDocument();
    $doc->load($gpx_file->uri);

    // Get all trackpoint and iterate them.
    $trackpoints = $doc->getElementsByTagName('trkpt');

    // Calculate the optimum of divisor because highchart js works well with
    // maximum 100 points.
    $length = $trackpoints->length;
    if ($length > 100) {
      $divisor = ceil($length / 100);
    }

    foreach ($trackpoints as $trkpt) {
      // Current high.
      $eles = $trkpt->getElementsByTagName('ele');
      $ele = $eles->item(0)->nodeValue;

      // Calculate lowest point.
      if (is_null($lowest_point)) {
        $lowest_point = $ele;
      }
      elseif ($ele < $lowest_point) {
        $lowest_point = $ele;
      }

      // Calculate highest point.
      if (is_null($highest_point)) {
        $highest_point = $ele;
      }
      elseif ($ele > $highest_point) {
        $highest_point = $ele;
      }

      // From the second element.
      if (!is_null($point_before)) {
        // Previous high.
        $eles_previous = $point_before->getElementsByTagName('ele');
        $ele_previous = $eles_previous->item(0)->nodeValue;

        // Earth's radius and the pi.
        $r = 6378;
        $pi = atan2(1, 1) * 4;

        // Calculate distance.
        $a1 = $point_before->getAttribute('lat') * ($pi / 180);
        $a2 = $trkpt->getAttribute('lat') * ($pi / 180);
        $b1 = $point_before->getAttribute('lon') * ($pi / 180);
        $b2 = $trkpt->getAttribute('lon') * ($pi / 180);

        if (($a1 == $a2) && ($b1 == $b2)) {
          $delta = 0;
        }
        else {
          $toacos = cos($a1) * cos($b1) * cos($a2) * cos($b2);
          $toacos += cos($a1) * sin($b1) * cos($a2) * sin($b2);
          $toacos += sin($a1) * sin($a2);

          if ($toacos > 1) {
            $delta = 0;
          }
          else {
            $delta = acos($toacos) * $r;
          }
        }
        $distance += $delta;

        // Calculate demotion.
        if (($ele - $ele_previous) < 0) {
          $demotion += ($ele - $ele_previous);
        }

        // Calculate elevation.
        if (($ele - $ele_previous) > 0) {
          $elevation += ($ele - $ele_previous);
        }

        if (++$i == $divisor) {
          $distance_array[] = (int) $distance;
          $ele_array[] = (int) $ele;
          $i = 0;
        }
      }

      // Check difficulty but high difference between 100 meters.
      if (($distance * 1000) - ($difficulty['last_distance'] * 1000) >= 100) {
        $difference = $difficulty['last_high'] - $ele;
        $distance_diff = $distance - $difficulty['last_distance'];

        // The distance is flat if the difference between -2 and 2.
        if ($difference < 2 && $difference > -2) {
          $difficulty['difficulties']['flat'] += $distance_diff;
        }
        // The distance is uphill if the difference between 2 and 10.
        if ($difference >= 2 && $difference < 10) {
          $difficulty['difficulties']['uphill'] += $distance_diff;
        }
        // The distance is downhill if the difference between -2 and -10.
        if ($difference <= -2 && $difference > -10) {
          $difficulty['difficulties']['downhill'] += $distance_diff;
        }
        // The distance is rise if the difference more than 10.
        if ($difference >= 10) {
          $difficulty['difficulties']['rise'] += $distance_diff;
        }
        // The distance is descent if the difference less than -10.
        if ($difference <= -10) {
          $difficulty['difficulties']['descent'] += $distance_diff;
        }

        // Store current variables.
        $difficulty['last_distance'] = $distance;
        $difficulty['last_high'] = $ele;
      }

      // Store all points.
      $points[] = array(
        'lon' => $trkpt->getAttribute('lon'),
        'lat' => $trkpt->getAttribute('lat'),
      );
      // Store the previous point for the distance calculating.
      $point_before = $trkpt;
    }

    return array(
      'elevation' => round($elevation, 2),
      'demotion' => round($demotion, 2),
      'highest_point' => $highest_point,
      'lowest_point' => $lowest_point,
      'distance' => round($distance, 2),
      'distance_array' => $distance_array,
      'ele_array' => $ele_array,
      'difficulty_array' => $difficulty['difficulties'],
      'points' => $points,
    );
  }
}