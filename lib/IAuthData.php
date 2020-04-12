<?php

interface IAuthData
{
  public function getLogin(): string;
  public function getPassword(): string;
  public function getLicenseKey(): string;
  public function getMeta1();
  public function getMeta2();
  public function getMeta3();
}

 ?>
