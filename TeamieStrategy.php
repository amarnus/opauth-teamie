<?php

/**
 * Teamie strategy for Opauth.
 * 
 * Teamie is a social, collaborative learning management system. The Teamie API helps developers
 * take their applications right inside the classroom allowing a richer, more 
 * meaningful learning experience that's actually fun.
 * 
 * Try https://playground.theteamie.com or
 *     https://theteamie.com
 * to read more about us.
 * 
 * Learn how you can set up your first Teamie app from here:
 *     https://playground.theteamie.com/platform/guide
 */
class TeamieStrategy extends OpauthStrategy {

  public $expects = array('client_id', 'client_secret');
  public $defaults = array(
    'response_type' => 'code',
    'redirect_uri' => '{complete_url_to_strategy}int_callback'
  );

  public function request() {
    $url = 'https://api.theteamie.com/oauth/authorize';
    $params = array(
      'client_id' => $this->strategy['client_id'],
      'redirect_uri' => $this->strategy['redirect_uri']
    );

    // We only support a subset of params now.
    if (!empty($this->strategy['scope']))
      $params['scope'] = $this->strategy['scope'];
    if (!empty($this->strategy['state']))
      $params['state'] = $this->strategy['state'];
    if (!empty($this->strategy['response_type']))
      $params['response_type'] = $this->strategy['response_type'];

    $this->clientGet($url, $params);
  }

  /**
   * Internal callback, after Teamie's OAuth authorization request.
   */
  public function int_callback() {
    if (array_key_exists('code', $_GET) && !empty($_GET['code'])) {
      $url = 'https://api.theteamie.com/oauth/token';
      $params = array(
        'grant_type' => 'authorization_code',
        'client_id' => $this->strategy['client_id'],
        'client_secret' => $this->strategy['client_secret'],
        'redirect_uri' => $this->strategy['redirect_uri'],
        'code' => trim($_GET['code'])
      );
      $response = $this->serverPost($url, $params, null, $headers);
      $results = (array) json_decode($response);

      if (!empty($results) && !empty($results['access_token'])) {
        $this->access_token = $results['access_token'];
        $me = $this->me();
        $this->auth = array(
          'provider' => 'Teamie',
          'uid' => $me->user->uid,
          // Teamie is a multi-tenant application. (like Stackoverflow) So, the client might want
          // to know which site the user belongs to.
          'site' => $results['site'],
          'info' => array(
            'name' => $me->user->real_name,
            'image' => $me->user->user_profile_image->path
          ),
          'credentials' => array(
            'token' => $results['access_token'],
            'expires' => date('c', time() + $results['expires_in'])
          ),
          'raw' => $me
        );

        $this->callback();
      }
      else {
        $error = array(
          'provider' => 'Teamie',
          'code' => 'access_token_error',
          'message' => 'Failed when attempting to obtain access token',
          'raw' => $headers
        );

        $this->errorCallback($error);
      }
    }
    else {
      // User has cancelled authorization request.
      if (!empty($_GET['denied'])) {
        $error = array(
          'provider' => 'Teamie',
          'code' => '400',
          'message' => 'User cancelled authorization request.',
          'raw' => $_GET
        );
        $this->errorCallback($error);
      }
    }
  }

  /**
   * Gets profile information about the user on whose behalf the OAuth request
   * is being made. (identified by the access token)
   */
  private function me() {
    $me = $this->serverGet('https://api.theteamie.com/app/me.json', array('access_token' => $this->access_token), null, $headers);
    if (!empty($me)) {
      return json_decode($me);
    }
    else {
      $error = array(
        'provider' => 'Teamie',
        'code' => 'me_error',
        'message' => 'Failed when attempting to query for user information',
        'raw' => array(
          'response' => $me,
          'headers' => $headers
        )
      );

      $this->errorCallback($error);
    }
  }

}