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
});/* 
 $routes->options('(:any)', function () {
    return $this->response
            ->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Headers', 'Origin, X-API-KEY, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Allow-Headers, Authorization, observe, enctype, Content-Length, X-Csrf-Token')
            ->setHeader('Access-Control-Allow-Methods', 'GET, PUT, POST, DELETE, PATCH, OPTIONS');
}); */

$routes->options('(:any)', static function () {
    // Implement processing for normal non-preflight OPTIONS requests,
    // if necessary.
    $response = response();
    $response->setStatusCode(204);
    $response->setHeader('Allow:', 'OPTIONS, GET, POST, PUT, PATCH, DELETE');

    return $response;
});