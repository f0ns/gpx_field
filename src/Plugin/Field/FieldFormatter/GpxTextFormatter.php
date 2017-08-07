<?php
namespace Drupal\gpx_field\Plugin\Field\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter;
/**
 * Plugin implementation of the 'GpxTextFormatter' formatter.
 *
 * @FieldFormatter(
 *   id = "gpx_text",
 *   label = @Translation("Show gpx data as text"),
 *   field_types = {
 *     "gpx_file"
 *   }
 * )
 */
class GpxTextFormatter extends FormatterBase {
  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $default_settings = parent::defaultSettings();

    $default_settings['visible_gpx_data'] = [];

    return $default_settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['visible_gpx_data'] = array(
      '#title' => t('What GPX data should be visible?'),
      '#type' => 'checkboxes',
      '#options' => array('elevation' => $this->t('Elevation'), 'demotion' => $this->t('Demotion'), 'lowest_point' => $this->t('Lowest point'), 'highest_point' => $this->t('Highest point'), 'distance' => $this->t('Distance'), 'points' => $this->t('Points')),
      '#default_value' => $this->getSetting('visible_gpx_data'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    if($this->getSetting('visible_gpx_data')) {

      $visible_gpx_data = [];

      foreach($this->getSetting(visible_gpx_data) as $key => $value) {
        if($value) {
          $visible_gpx_data[] = $value;
        }
      }

      $summary[] = t('Visible GPX data: @visible_gpx_data', array('@visible_gpx_data' => implode(', ', $visible_gpx_data)));
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    foreach ($items as $delta => &$item) {

      $visible_gpx_data = [];
      $visible = $this->getSetting('visible_gpx_data');

      foreach($visible as $key => $value) {
        if($value) {
          $visible_gpx_data[] = $value;
        }
      }

      if(in_array('elevation', $visible_gpx_data)) {
        $elements[$delta] = [
          'elevation' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Elevation') . '</label><div>' . $item->elevation . 'm</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }

      if(in_array('demotion', $visible_gpx_data)) {
        $elements[$delta] = [
          'demotion' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Demotion') . '</label><div>' . $item->demotion . 'm</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }

      if(in_array('lowest_point', $visible_gpx_data)) {
        $elements[$delta] = [
          'lowest_point' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Lowest point') . '</label><div>' . $item->lowest_point . 'm</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }

      if(in_array('highest_point', $visible_gpx_data)) {
        $elements[$delta] = [
          'highest_point' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Highest point') . '</label><div>' . $item->highest_point . 'm</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }

      if(in_array('distance', $visible_gpx_data)) {
        $elements[$delta] = [
          'demotion' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Distance') . '</label><div>' . round($item->distance/1000,2) . 'km</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }

      if(in_array('points', $visible_gpx_data)) {
        $elements[$delta] = [
          'demotion' => [
            '#type' => 'inline_template',
            '#template' => '<label>' . $this->t('Points') . '</label><div>' . count($item->points) . '</div>',
            '#prefix' => '<div>',
            '#suffix' => '</div>',
          ]
        ];
      }
    }
    return $elements;
  }
}