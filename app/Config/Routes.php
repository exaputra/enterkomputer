<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('orders', 'Orders::create');
$routes->get('orders/(:num)', 'Orders::show/$1');
