<?php

abstract class AuthData implements IAuthData
{
  private $login;
  private $password;
  private $license_key;
  private $meta = [
    'meta_1' => '',
    'meta_2' => '',
    'meta_3' => '',
  ];

  public function __construct($login, $password, $license_key = '', array $meta)
  {
    $this->login = $login;
    $this->password = $password;
    $this->license_key = $license_key;
    for($i=1; $i < 4; $i++)
    {
      if(isset($meta["meta_$i"]))
      {
        $this->meta["meta_$i"] = $meta["meta_$i"];
      }
    }
  }

  public function getLogin(): string
  {
    return $this->login;
  }

  public function getPassword(): string
  {
    return $this->password;
  }

  public function getLicenseKey(): string
  {
    return $this->license_key;
  }

  public function getMeta1(): string
  {
    return $this->meta['meta_1'];
  }

  public function getMeta2(): string
  {
    return $this->meta['meta_2'];
  }

  public function getMeta3(): string
  {
    return $this->meta['meta_3'];
  }
}

 ?>
