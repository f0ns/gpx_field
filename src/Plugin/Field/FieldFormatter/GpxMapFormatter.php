<?php

namespace Drupal\gpx_field\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\highcharts\Highchart;
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

      $features = [
        [
          'type' => 'point',
          'lat' => $item->points[0]['lat'],
          'lon' => $item->points[0]['lon'],
          'icon' => [
            'iconUrl' => '/' . drupal_get_path('module', 'gpx_field') . '/images/start.png',
            'iconSize' => [17, 17],
          ]
        ],
        [
          'type' => 'linestring',
          'points' => $item->points,
          'popup' => $items->getEntity()->label(),
          'options' => [
            'color' => '#2d5be3'
          ],
        ],
        [
          'type' => 'point',
          'lat' => $item->points[$amount_of_points - 1]['lat'],
          'lon' => $item->points[$amount_of_points - 1]['lon'],
          'icon' => [
            'iconUrl' => '/' . drupal_get_path('module', 'gpx_field') . '/images/finish.png',
            'iconSize' => [17, 17],
          ],
        ],
      ];

      if (!empty($icon_url)) {
        foreach ($features as $key => $feature) {
          $features[$key]['icon'] = $settings['icon'];
        }
      }

      $elevation_profile_chart_options = $this->getChartOptions($item);
      $elevation_profile_chart_series = $this->getChartSeries($item);

      $elevation_profile_chart = new Highchart($elevation_profile_chart_options, $elevation_profile_chart_series);

      $gpx_file = \Drupal\file\Entity\File::load($item->target_id);
      $gpx_file_url = file_create_url($gpx_file->getFileUri());

      $elements[$delta] = [
        'gpx_download' => [
          '#theme' => 'gpx_download',
          '#gpx_file_url' => $gpx_file_url,
        ],
        'gpx_stats' => [
          '#theme' => 'gpx_stats',
          '#elevation' => $item->elevation,
          '#demotion' => $item->demotion,
          '#lowest_point' => $item->lowest_point,
          '#highest_point' => $item->highest_point,
          '#distance' => round($item->distance / 1000, 2),
          '#points' => count($item->points),
        ],
        'map' => leaflet_render_map($map, $features, $settings['height'] . 'px'),
        'elevation_profile' => $elevation_profile_chart->render(),
      ];
    }

    return $elements;
  }

  protected function getChartOptions($item) {
    $options = [];

    $options = [
      'title' => [
        'text' => '',
      ],
      'xAxis' => [
        'title' => [
          'text' => $this->t('Distance')
        ],
        'labels' => [
          'formatter' => 'function () { return this.value + \'km\'; }'
        ]
      ],
      'yAxis' => [
        'title' => [
          'text' => $this->t('Elevation')
        ],
        'labels' => [
          'formatter' => 'function () { return this.value + \'m\'; }'
        ]
      ],
      'chart' => [
        'backgroundColor' => '#efeff4'
      ],
      'colors' => ['#204dcc'],
      'tooltip' => [
        'headerFormat' => '',
        'pointFormat' => $this->t('Elevation').': {point.y:,.0f}m<br/>'.$this->t('Distance').': {point.x:.1f}km'
      ],
      'legend' => [
        'enabled' => FALSE
      ]
    ];

    return $options;
  }

  protected function getChartSeries($item) {
    $series = [];

    $series = [
      [
        'type' => 'area',
        'name' => 'Elevation',
        'data' => $item->elevation_profile
      ]
    ];

    return $series;
  }

}

