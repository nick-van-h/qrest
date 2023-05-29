<?php

/**
 * Routes system
 */

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

global $routes;

$routes = new RouteCollection();
$routes->add('', new Route('/', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showHome')));
$routes->add('home', new Route('/home', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showHome')));
$routes->add('admin', new Route('/admin', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showAdmin')));
$routes->add('logout', new Route('/logout', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'logout')));
$routes->add('recover', new Route('/recover', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showAccountRecovery')));

//App routes

$routes->add('app', new Route('/app', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showApp')));
$routes->add('appSettings', new Route('/app/settings', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showApp', 'page' => 'settings', 'list' => '')));
$routes->add('appList', new Route('/app/list/{list}/{item}', array('controller' => 'Qrest\Controllers\PageController', 'method' => 'showApp', 'page' => 'list', 'list' => '', 'item' => '')));
