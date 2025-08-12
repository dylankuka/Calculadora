<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('formulario', 'Calculadora::formulario');
$routes->post('calcular', 'Calculadora::calcular'); // Para procesar el POST
