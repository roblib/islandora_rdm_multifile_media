<?php

namespace Drupal\islandora_rdm_multifile_media\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\islandora\IslandoraUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\media\Entity\Media;

/**
 * Class MediaSourceController.
 *
 * @package Drupal\islandora\Controller
 */
class MediaSourceController extends ControllerBase {

  /**
   * Islandora utils.
   *
   * @var \Drupal\islandora\IslandoraUtils
   */
  protected $utils;

  /**
   * MediaSourceController constructor.
   *
   * @param \Drupal\islandora\IslandoraUtils $utils
   *   Islandora utils.
   */
  public function __construct(
    IslandoraUtils $utils
  ) {
    $this->utils = $utils;
  }

  /**
   * Controller's create method for dependency injection.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The App Container.
   *
   * @return \Drupal\islandora_rdm_multifile_media\Controller\MediaSourceController
   *   Controller instance.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('islandora.utils')
    );
  }

  /**
   * Adds file to existing media.
   *
   * @param Drupal\media\Entity\Media\Media $media
   *   The media to which file is added.
   * @param string $destination_field
   *   The name of the media field to add file reference.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   201 on success with a Location link header.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function attachToMedia(
    Media $media,
    string $destination_field,
    Request $request) {
    $content_location = $request->headers->get('Content-Location', "");
    $contents = $request->getContent();
    if ($contents) {
      $file = file_save_data($contents, $content_location, FILE_EXISTS_REPLACE);
      $media->{$destination_field}->setValue([
        'target_id' => $file->id(),
      ]);
      $media->save();
    }
    // Should only see this with a GET request for testing.
    return new Response("<h1>Complete</h1>");
  }

  /**
   * Adds file to existing media.
   *
   * @param Drupal\media\Entity\Media $media
   *   The media to which file is added.
   * @param string $destination_field
   *   The name of the media field to add file reference.
   * @param string $destination_text_field
   *   The name of the media field to add file reference.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   201 on success with a Location link header.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\ReadOnlyException
   */
  public function attachTextToMedia(
    Media $media,
    string $destination_field,
    string $destination_text_field,
    Request $request) {
    $content_location = $request->headers->get('Content-Location', "");
    $contents = $request->getContent();

    if ($contents) {
      \Drupal::logger('Alan_dev')->warning("Content location is $content_location");
      $file = file_save_data($contents, $content_location, FILE_EXISTS_REPLACE);
      $media->{$destination_field}->setValue([
        'target_id' => $file->id(),
      ]);
      $media->{$destination_text_field}->setValue(nl2br($contents));
      $media->save();

      return new Response("<h1>Complete</h1>");
    }
  }

  /**
   * Checks for permissions to update a node and update media.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Account for user making the request.
   * @param \Drupal\Core\Routing\RouteMatch $route_match
   *   Route match to get Node from url params.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function attachToMediaAccess(AccountInterface $account, RouteMatch $route_match) {
    $media = $route_match->getParameter('media');
    $node = $this->utils->getParentNode($media);
    return AccessResult::allowedIf($node->access('update', $account) && $account->hasPermission('create media'));
  }

}
