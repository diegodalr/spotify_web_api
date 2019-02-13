<?php

namespace Drupal\spotify_web_api;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Drupal\Component\Utility\UrlHelper;

/**
 * Class DefaultService.
 */
class SpotifyService {

  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * Constructs a new DefaultService object.
   */
  public function __construct(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  /**
   * Get the Spotify access token using client credentials.
   *
   * @return string|bool
   *    The token value or false.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAccessToken() {
    $spotify_config = \Drupal::config('spotify.settings');
    $url = 'https://accounts.spotify.com/api/token';
    $query = [
      'grant_type' => 'client_credentials',
      'client_id' => $spotify_config->get('client_id') ? $spotify_config->get('client_id') : '83c0f9f106b1493590e4d45cd5f0a979',
      'client_secret' => $spotify_config->get('client_secret') ? $spotify_config->get('client_secret') : 'c8411ff0b00646c88a373e21a445acca',
    ];
    $options = [
      'headers' => [
        'Content-Type' => 'application/x-www-form-urlencoded',
      ],
      'form_params' => $query,
    ];
    try {
      $request = $this->httpClient->request('post', $url, $options);
      if ($response = json_decode($request->getBody(), TRUE)) {
        return $response['access_token'];
      }
    } catch (RequestException $requestException) {
      if (function_exists('dpm')) {
        dpm($requestException->getMessage());
      }
    }
    return FALSE;
  }

  /**
   * Get Spotify New releases.
   *
   * @return array
   *    Array of items.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getNewReleases() {
    $url = 'https://api.spotify.com/v1/browse/new-releases?' . UrlHelper::buildQuery([
        'country' => 'CO',
        'limit' => 10,
      ]);
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $this->getAccessToken(),
      ],
    ];
    try {
      $request = $this->httpClient->request('get', $url, $options);
      if ($response = json_decode($request->getBody(), TRUE)) {
        return $response['albums']['items'];
      }
    } catch (RequestException $requestException) {
      if (function_exists('dpm')) {
        dpm($requestException->getMessage());
      }
    }
    return [];
  }

  /**
   * @param $id
   *   The artist ID.
   *
   * @return array
   *   The array of artist information.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getArtist($id) {
    $url = 'https://api.spotify.com/v1/artists/' . $id;
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $this->getAccessToken(),
      ],
    ];
    try {
      $request = $this->httpClient->request('get', $url, $options);
      if ($response = json_decode($request->getBody(), TRUE)) {
        return $response;
      }
    } catch (RequestException $requestException) {
      if (function_exists('dpm')) {
        dpm($requestException->getMessage());
      }
    }
    return [];
  }

  /**
   * @param $id
   *   The artist ID.
   *
   * @return array
   *   Artist's albums.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getArtistAlbums($id) {
    $url = 'https://api.spotify.com/v1/artists/' . $id . '/albums?' . UrlHelper::buildQuery([
        'limit' => 10,
      ]);
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $this->getAccessToken(),
      ],
    ];
    try {
      $request = $this->httpClient->request('get', $url, $options);
      if ($response = json_decode($request->getBody(), TRUE)) {
        return $response['items'];
      }
    } catch (RequestException $requestException) {
      if (function_exists('dpm')) {
        dpm($requestException->getMessage());
      }
    }
    return [];
  }

  /**
   * @param $id
   *   The album ID.
   * @return array
   *   Album's tracks.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAlbumTracks($id) {
    $url = 'https://api.spotify.com/v1/albums/' . $id . '/tracks?' . UrlHelper::buildQuery([
        'limit' => 10,
      ]);
    $options = [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => 'Bearer ' . $this->getAccessToken(),
      ],
    ];
    try {
      $request = $this->httpClient->request('get', $url, $options);
      if ($response = json_decode($request->getBody(), TRUE)) {
        return $response['items'];
      }
    } catch (RequestException $requestException) {
      if (function_exists('dpm')) {
        dpm($requestException->getMessage());
      }
    }
    return [];
  }
}
