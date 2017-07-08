<?php

namespace Drupal\gpx_field;

use Drupal\file\Entity\File;
use phpGPX\phpGPX;

/**
 * Class GpxAnalyser
 * @package Drupal\gpx_field
 */
class GpxAnalyser {

  protected $elevation;
  protected $demotion;
  protected $highestPoint;
  protected $lowestPoint;
  protected $distance;
  protected $coordinates;

  /**
   * GpxAnalyser constructor.
   * @param \Drupal\file\Entity\File $gpx_xml
   */
  public function __construct(File $gpx_xml) {
    $this->elevation = 0;
    $this->demotion = 0;
    $this->lowestPoint = 0;
    $this->highestPoint = 0;
    $this->distance = 0;
    $this->coordinates = [];
    // Set all stats.
    $this->setStats($gpx_xml);
  }

  /**
   * @return float
   */
  public function getElevation() {
    return $this->elevation;
  }

  /**
   * @return float
   */
  public function getDemotion() {
    return $this->demotion;
  }

  /**
   * @return float
   */
  public function getHighestPoint() {
    return $this->highestPoint;
  }

  /**
   * @return float
   */
  public function getLowestPoint() {
    return $this->lowestPoint;
  }

  /**
   * @return float
   */
  public function getDistance() {
    return $this->distance;
  }

  /**
   * @return array
   */
  public function getCoordinates() {
    return $this->coordinates;
  }

  /**
   * Calculates all stats from gpx file.
   * @param \Drupal\file\Entity\File $gpx_xml
   */
  protected function setStats(File $gpx_xml) {
    $gpx = new phpGPX();
    $stream_wrapper_manager = \Drupal::service('stream_wrapper_manager')->getViaUri($gpx_xml->getFileUri());
    $file_path = $stream_wrapper_manager->realpath();
    $file = $gpx->load($file_path);

    $previous_elevation = 0;

    /** @var \phpGPX\Models\Track $track */
    foreach ($file->tracks as $track) {
      /** @var \phpGPX\Models\Point $point */
      foreach ($track->getPoints() as $point) {
        // Calculate elevation.
        if (($point->elevation - $previous_elevation) > 0) {
          $this->elevation += ($point->elevation - $previous_elevation);
        }

        // Calculate demotion.
        if (($point->elevation - $previous_elevation) < 0) {
          $this->demotion += ($point->elevation - $previous_elevation);
        }

        // Add coordinate to array.
        array_push($this->coordinates, [
          'lat' => $point->latitude,
          'lon' => $point->longitude
        ]);

        $previous_elevation = $point->elevation;
      }
      // Statistics for whole track
      $this->lowestPoint = ($track->stats->minAltitude < $this->lowestPoint) ? $track->stats->minAltitude : $this->lowestPoint;
      $this->highestPoint = ($track->stats->maxAltitude > $this->highestPoint) ? $track->stats->maxAltitude : $this->highestPoint;
      $this->distance += $track->stats->distance;
    }
  }

}