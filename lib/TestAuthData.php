<?php

class TestAuthData extends AuthData
{
  public function __construct($login, $password, $license_key = '136C61C0CD0C', array $meta = ['meta1' => 'Test', 'meta2' => 'Test', 'meta3' => 'Test'])
  {
    parent::__construct($login, $password, $license_key, $meta);
  }
}

 ?>
