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
    $elements = [];
    foreach ($items as $delta => &$item) {
      $elements[$delta] = [
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
          '#template' => '<label>' . $this->t('Distance') . '</label><span>' . $item->distance . 'm</span>',
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
