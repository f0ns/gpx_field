<?php

namespace Drupal\gpx_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\leaflet\Plugin\Field\FieldFormatter\LeafletDefaultFormatter;

/**
 * Plugin implementation of the 'GpxMapFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "gpx_map",
 *   label = @Translation("Show gpx data on a map"),
 *   field_types = {
 *     "gpx_file"
 *   }
 * )
 */
class GpxMapFormatter extends LeafletDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [

      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $settings = $this->getSettings();
    $icon_url = $settings['icon']['icon_url'];

    $map = leaflet_map_get_info($settings['leaflet_map']);
    $map['settings']['zoom'] = isset($settings['zoom']) ? $settings['zoom'] : NULL;
    $map['settings']['minZoom'] = isset($settings['minZoom']) ? $settings['minZoom'] : NULL;
    $map['settings']['maxZoom'] = isset($settings['zoom']) ? $settings['maxZoom'] : NULL;

    $elements = [];

    foreach ($items as $delta => $item) {

      $amount_of_points = count($item->points);

      $features = array(
        array(
          'type' => 'point',
          'lat' => $item->points[0]['lat'],
          'lon' => $item->points[0]['lon'],
        ),
        array(
          'type' => 'linestring',
          'points' => $item->points,
          'popup' => $items->getEntity()->label(),
          'options' => array(
            'color' => '#2d5be3'
          ),
        ),
        array(
          'type' => 'point',
          'lat' => $item->points[$amount_of_points-1]['lat'],
          'lon' => $item->points[$amount_of_points-1]['lon'],
        ),
      );

      if (!empty($icon_url)) {
        foreach ($features as $key => $feature) {
          $features[$key]['icon'] = $settings['icon'];
        }
      }

      $elements[$delta] = [
        'map' => leaflet_render_map($map, $features, $settings['height'] . 'px'),
        'metadata' => [
          '#theme' => 'gpx_metadata',
          '#elevation' => $item->elevation,
          '#demotion' => $item->demotion,
          '#lowest_point' => $item->lowest_point,
          '#highest_point' => $item->higehst_point,
          '#distance' => round($item->distance / 1000, 2),
          '#points' => count($item->points),
        ]
      ];
    }
    return $elements;
  }
}
