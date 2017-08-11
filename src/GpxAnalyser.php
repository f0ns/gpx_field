<?php

namespace Drupal\gpx_field;

use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\file\Entity\File;
use phpGPX\phpGPX;

/**
 * Class GpxAnalyser
 * @package Drupal\gpx_field
 */
class GpxAnalyser {

  protected $streamWrapper;
  protected $elevation;
  protected $demotion;
  protected $highestPoint;
  protected $lowestPoint;
  protected $distance;
  protected $coordinates;
  protected $heightProfile;

  /**
   * GpxAnalyser constructor.
   * @param \Drupal\file\Entity\File $gpx_xml
   */
  public function __construct(File $gpx_xml, StreamWrapperManagerInterface $stream_wrapper_manager) {
    $this->streamWrapper = $stream_wrapper_manager->getViaUri($gpx_xml->getFileUri());
    $this->elevation = 0;
    $this->demotion = 0;
    $this->lowestPoint = 0;
    $this->highestPoint = 0;
    $this->distance = 0;
    $this->coordinates = [];
    $this->elevationProfile = [];
    // Set all stats.
    $this->setStats($gpx_xml);
  }

  /**
   * Creates new instance of this class.
   * @param \Drupal\file\Entity\File $gpx_xml
   * @return static
   */
  public static function create(File $gpx_xml) {
    /** @var StreamWrapperManagerInterface $manager */
    $container = \Drupal::getContainer();
    $manager = $container->get('stream_wrapper_manager');
    return new static($gpx_xml, $manager);
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
   * @return array
   */
  public function getElevationProfile() {
    return $this->elevationProfile;
  }

  /**
   * Calculates all stats from gpx file.
   * @param \Drupal\file\Entity\File $gpx_xml
   */
  protected function setStats(File $gpx_xml) {
    $gpx = new phpGPX();
    $file_path = $this->streamWrapper->realpath();
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

        // Add coordinate to coordinates array.
        array_push($this->coordinates, [
          'lat' => $point->latitude,
          'lon' => $point->longitude
        ]);

        // Add the distance and elevation the the elevation profile.
        array_push($this->elevationProfile, [
          $point->distance / 1000,
          $point->elevation
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