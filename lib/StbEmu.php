<?php

use ApiV3Exceptions\ForbiddenError;

class StbEmu
{
  public $portal;
  public $login;
  public $password;
  public $license_key;
  private $meta;

  public $user_id;
  private $access_token;
  private $refresh_token;
  private $access_token_exp;

  public function __construct($portal, IAuthData $data)
  {
    $this->portal = $portal;
    $this->login = $data->getLogin();
    $this->password = $data->getPassword();
    $this->license_key = $data->getLicenseKey();
    $this->meta = [
      'meta_1' => $data->getMeta1(),
      'meta_2' => $data->getMeta2(),
      'meta_3' => $data->getMeta3(),
    ];
  }

  public function ping()
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/users/$this->user_id/ping";
    return $this->sendGetRequest($url);
  }

  public function getModules()
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/modules/";
    return $this->sendGetRequest($url);
  }

  public function getSettings()
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/users/$this->user_id/settings";
    return $this->sendGetRequest($url);
  }

  public function getTheme($theme)
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/themes/$theme";
    return $this->sendGetRequest($url);
  }

  public function getTvGenres()
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-genres";
    return $this->sendGetRequest($url);
  }

  public function getTvChannels($limit = null)
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-channels";
    if(intval($limit) > 0)
    {
      $url .= "?limit=$limit";
    }

    return $this->sendGetRequest($url);
  }

  public function updateChannelsList($action, $from)
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-channels/$action?from=$from";
    return $this->sendGetRequest($url);
  }

  public function getChannelLink($channelId)
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-channels/$channelId/link";
    return $this->sendGetRequest($url);
  }

  public function playChannel($link)
  {
    $ch=curl_init();
    $timeout=1;

    curl_setopt($ch, CURLOPT_URL, $link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    $result=curl_exec($ch);
    curl_close($ch);
  }

  public function getChannelEpg($channelId, $from, $to)
  {
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-channels/$channelId/epg?$from=$from&to=$to";
    return $this->sendGetRequest($url);
  }

  public function getNextEpg(array $channels)
  {
    $channels = urlencode(implode(',', $channels));
    $this->checkAccessToken();
    $url = "$this->portal/api/v3/tv-channels/$channels/epg?next=1";
    return $this->sendGetRequest($url);
  }

  private function sendGetRequest($url)
  {
    $headers = $this->getHeaders(true);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $serverOutput = curl_exec($ch);
    curl_close($ch);

    $data = $this->processServerOutput($serverOutput);
    return $data;
  }

  private function getHeaders($auth = false)
  {
    $headers = [
      'Accept: application/json',
      'Content-Type: application/x-www-form-urlencoded',
      'Accept-Language: en',
    ];

    if($auth == true)
    {
      $headers[] = "Authorization: Bearer $this->access_token";
    }

    return $headers;
  }

  public function auth($refresh = false)
  {
    $data = [
      'hw_version'      => '',
      'meta_1'          => $this->meta['meta_1'],
      'meta_2'          => $this->meta['meta_2'],
      'meta_3'          => $this->meta['meta_3'],
      'activation_code' => $this->license_key,
    ];

    if($refresh == true && isset($this->refresh_token))
    {
      $headers = $this->getHeaders(true);

      $data['grant_type'] = 'refresh_token';
      $data['refresh_token'] = $this->refresh_token;
    }
    else
    {
      $headers = $this->getHeaders();

      $data['grant_type'] = 'password';
      $data['username'] = $this->login;
      $data['password'] = $this->password;
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL,"$this->portal/auth/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


    $serverOutput = curl_exec($ch);
    curl_close($ch);

    $data = $this->processServerOutput($serverOutput);
    $this->license_key = $data['activation_code'];
    $this->access_token = $data['access_token'];
    $this->refresh_token = isset($data['refresh_token']) ? $data['refresh_token'] : '';
    $this->user_id = $data['user_id'];
    $this->access_token_exp = time() + $data['expires_in'];
  }

  private function checkAccessToken()
  {
    if($this->access_token_exp - time() < 240)
    {
      $this->auth(true);
    }
  }

  private function processServerOutput($data)
  {
    $data = json_decode($data, true);
    if(isset($data['error']))
    {
      $msg = isset($data['error_description']) ? $data['error_description'] : $data['message'];

      throw new StbEmuException("Error: {$data['error']}; Description: $msg", 1);
    }

    return $data;
  }




}


 ?>
