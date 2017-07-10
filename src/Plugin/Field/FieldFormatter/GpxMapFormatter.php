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

      $features = array(
        array(
          'type' => 'linestring',
          'points' => $item->points,
        )
      );
      // If only a single feature, set the popup content to the entity title.
      if ($settings['popup'] && count($items) == 1) {
        $features[0]['popup'] = $items->getEntity()->label();
      }
      if (!empty($icon_url)) {
        foreach ($features as $key => $feature) {
          $features[$key]['icon'] = $settings['icon'];
        }
      }

      $elements[$delta] = [
        'map' => leaflet_render_map($map, $features, $settings['height'] . 'px'),

        'elevation' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Elevation') . '</label><span>' . $item->elevation . 'm</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
        'demotion' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Demotion') . '</label><span>' . $item->demotion . 'm</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
        'lowest_point' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Lowest point') . '</label><span>' . $item->lowest_point . 'm</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
        'highest_point' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Highest point') . '</label><span>' . $item->highest_point . 'm</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
        'distance' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Distance') . '</label><span>' . round($item->distance / 1000, 2) . 'km</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
        'points' => [
          '#type' => 'inline_template',
          '#template' => '<label>' . $this->t('Number of coordinates') . '</label><span>' . count($item->points) . '</span>',
          '#prefix' => '<div>',
          '#suffix' => '</div>',
        ],
      ];
    }
    return $elements;
  }
}
