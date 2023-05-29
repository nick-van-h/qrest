<?php
//Default include autoload
try {
    require_once 'vendor/qrest/autoload.php';
} catch (Error $e) {
    header("Location: setup.php");
}

//Test all other components
$setup = new Qrest\Models\Setup();
$basePath = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);
$headerLocation = str_replace($basePath, '', $_SERVER['REQUEST_URI']);
if (!$setup->checkRequirements() && $headerLocation != 'admin') {
    header("Location: " . BASE_URL . "/admin");
    exit();
}

//Remove trailing slash in url
$fullUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$fullUrlClean = substr_replace($fullUrl, '', -1);
if (substr($fullUrl, -1) === '/' && $fullUrlClean !== BASE_URL) {
    header("Location: " . $fullUrlClean);
}

//Display the correct page using the router
$router = new Qrest\Util\Router();
$router($routes);
