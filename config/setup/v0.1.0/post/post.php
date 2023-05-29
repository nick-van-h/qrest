<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

use Qrest\Controllers\Collections;
use Qrest\Controllers\Items;
use Qrest\Controllers\User;

$user = new User();
$collections = new Collections();
$items = new Items();

$username = 'JohnDoe';
$password = 'CorrectHorseBatteryStaple';

$user->logout();
$user->create($username, $password);
$user->login($username, $password, 'session');
$uid = $collections->add('Foo');
$items->add('Qux', $uid);
$items->add('Quux', $uid);
$items->add('Corge', $uid);
$items->add('Grault', $uid);
$items->add('Garply', $uid);
$items->add('Waldo', $uid);
$items->add('Fred', $uid);
$items->add('Plugh', $uid);
$items->add('Xyzzy', $uid);
$items->add('Thud', $uid);
$uid = $collections->add('Bar');
$items->add('Apple', $uid);
$items->add('Banana', $uid);
$items->add('Grape', $uid);
$items->add('Orange', $uid);
$uid = $collections->add('Baz');
$user->logout();
