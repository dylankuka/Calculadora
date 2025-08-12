<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('usuario/registro', 'Usuario::registro');
$routes->post('usuario/registrar', 'Usuario::registrar');
$routes->get('usuario/login', 'Usuario::login');
$routes->post('usuario/iniciarSesion', 'Usuario::iniciarSesion');
$routes->get('usuario/logout', 'Usuario::logout');
