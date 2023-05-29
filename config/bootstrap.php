<?php

//Start the session if it is not yet started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * App-wide constants
 */
const BASE_PATH = __DIR__ . '/..';
const SHOW_ERROR_MESSAGE = true; //Disable in production
const VALIDATION_MESSAGE = "Hello, world!";
define('BASE_URL', sprintf(
    "%s://%s",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
    $_SERVER['SERVER_NAME'] == 'localhost' ? 'localhost/qrest/public' : $_SERVER['SERVER_NAME']
));

//Require other config files
require_once BASE_PATH . '/config/routes.php';
