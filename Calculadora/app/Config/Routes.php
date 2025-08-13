<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Calculadora::formulario');
$routes->get('usuario/registro', 'Usuario::registro');
$routes->post('usuario/registrar', 'Usuario::registrar');
$routes->get('usuario/login', 'Usuario::login');
$routes->post('usuario/iniciarSesion', 'Usuario::iniciarSesion');
$routes->get('usuario/logout', 'Usuario::logout');
$routes->get('formulario', 'Calculadora::formulario'); // Falta esta ruta
$routes->post('calcular', 'Calculadora::calcular');    // Falta esta ruta