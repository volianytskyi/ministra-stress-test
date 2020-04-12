<?php

include 'autoload.php';

$portal = "http://example.com/stalker_portal";

$users = file('users.csv');

$i = $argv[1] - 1;

$args = explode(',', $users[$i]);
$u = $args[0];
$p = $args[1];
$meta1 = $args[2];
$meta2 = $args[3];
$meta3 = $args[4];

echo "User [$u]\n";

$emu = new StbEmu($portal, (new StressTestAuthData($u, $p, '', ['meta_1' => $meta1, 'meta_2' => $meta2, 'meta_3' => $meta3])));
$userflow = new Userflow($emu);
$userflow->run();


 ?>
