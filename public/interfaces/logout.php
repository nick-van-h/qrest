<?php
//Default include autoload
require_once 'vendor/qrest/autoload.php';

//Logout the user and redirect to home
$user = new Qrest\Controllers\User();
$user->logout();
header('Location: ' . BASE_URL);
