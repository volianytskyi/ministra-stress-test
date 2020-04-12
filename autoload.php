<?php

function simpleClassRegister($class)
{
  $lib = __DIR__.'/lib/';
  $classPath = $lib.$class . ".php";
  if(file_exists($classPath))
  {
    require_once($classPath);
  }
}

function packageClassRegister($class)
{
  if(strpos($class, '\\') !== false)
  {
    $class = str_replace('\\', "/", $class);
  }
  $classPath = __DIR__.'/../lib/'.$class.".php";
  if(file_exists($classPath))
  {
    require_once($classPath);
  }
}

spl_autoload_register('simpleClassRegister');
spl_autoload_register('packageClassRegister');

 ?>
