<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

//service('auth')->routes($routes);
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], function ($routes) {
    $routes->post('login', 'Auth::jwtLogin');      // Endpoint untuk login JWT
    $routes->post('refresh', 'Auth::refresh');    // Endpoint untuk refresh token
    $routes->post('logout', 'Auth::jwtlogout');   // Endpoint untuk logout
});
