<?php

namespace Drupal\spotify_web_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Render\FormattableMarkup;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\Core\Url;
use Drupal\spotify_web_api\SpotifyService;

/**
 * Class SpotifyPagesController.
 */
class SpotifyPagesController extends ControllerBase {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * The Spotify service.
   *
   * @var \Drupal\spotify_web_api\SpotifyService
   */
  protected $spotifyService;

  /**
   * Constructs a new SpotifyPagesController object.
   */
  public function __construct(ClientInterface $http_client, SpotifyService $spotify_service) {
    $this->httpClient = $http_client;
    $this->spotifyService = $spotify_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'),
      $container->get('spotify_web_api.spotify_service')
    );
  }

  /**
   * Get latest releases.
   *
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getLatestReleases() {
    $rows = [];
    foreach ($this->spotifyService->getNewReleases() as $item) {
      $artist_links = [];
      foreach ($item['artists'] as $artist) {
        $artist_links[] = $this->l($artist['name'], Url::fromRoute('spotify_web_api.spotify_pages_controller_getArtistPage', ['id' => $artist['id']]));
      }
      $album_image = [
        '#theme' => 'image',
        '#uri' => $item['images'][0]['url'],
        '#title' => $item['name'],
        '#alt' => $item['name'],
      ];
      $artists_links = [
        '#theme' => 'item_list',
        '#list_type' => 'ul',
        '#items' => $artist_links,
      ];
      $rows[] = [
        'data' => [
          $item['name'],
          new FormattableMarkup(\Drupal::service('renderer')->render($album_image, FALSE), []),
          new FormattableMarkup(\Drupal::service('renderer')->render($artists_links, FALSE), []),
        ],
      ];
    }
    $header = [
      'name' => t('Name'),
      'image' => t('Image'),
      'artists' => t('Artists'),
    ];
    return [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }

  /**
   * @param $id
   *   The artist ID.
   * @return array
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getArtistPage($id) {
    if (empty($id)) {
      throw new NotFoundHttpException();
    }
    $artist_info = $this->spotifyService->getArtist($id);
    $artist_albums = $this->spotifyService->getArtistAlbums($id);
    $artist_albums_info = [];
    foreach ($artist_albums as $album) {
      $artist_albums_info[$album['id']] = [
        'name' => $album['name'],
        'image' => $album['images'][0]['url'],
      ];
      $album_tracks = $this->spotifyService->getAlbumTracks($album['id']);
      foreach ($album_tracks as $track) {
        $artist_albums_info[$album['id']]['tracks'][] = $track['name'];
      }
    }
    return [
      '#theme' => 'spotify_artist',
      '#artist_info' => $artist_info,
      '#artist_albums_info' => $artist_albums_info,
    ];
  }

}
