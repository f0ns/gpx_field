<?php

namespace Drupal\gpx_views\Plugin\views\style;

use Drupal\Component\Utility\NestedArray;
use Drupal\leaflet_views\Plugin\views\style\Leaflet;

/**
 * Style plugin to render a View output as a Leaflet map.
 *
 * @ingroup views_style_plugins
 *
 * Attributes set below end up in the $this->definition[] array.
 *
 * @ViewsStyle(
 *   id = "gpxleaflet",
 *   title = @Translation("GPX Leaflet"),
 *   help = @Translation("Displays a View as a Leaflet map with GPX data."),
 *   display_types = {"normal"},
 *   theme = "leaflet-map"
 * )
 */
class GpxLeaflet extends Leaflet {

  /**
   * {@inheritdoc}
   */
  public function render() {

    dsm('hallo');

    $features = array();
    foreach ($this->view->attachment_before as $id => $attachment) {
      dsm($id);
      if (!empty($attachment['#leaflet-attachment'])) {
        dsm($attachment);
        $features = array_merge($features, $attachment['rows']);
        $this->view->element['#attached'] = NestedArray::mergeDeep($this->view->element['#attached'], $attachment['#attached']);
        unset($this->view->attachment_before[$id]);
      }
    }

    $map_info = leaflet_map_get_info($this->options['map']);
    // Enable layer control by default, if we have more than one feature group.
    if (self::hasFeatureGroups($features)) {
      $map_info['settings'] += array('layerControl' => TRUE);
    }
    $element = leaflet_render_map($map_info, $features, $this->options['height'] . 'px');

    // Merge #attached libraries.
    $this->view->element['#attached'] = NestedArray::mergeDeep($this->view->element['#attached'], $element['#attached']);
    $element['#attached'] =& $this->view->element['#attached'];

    return $element;
  }
}

