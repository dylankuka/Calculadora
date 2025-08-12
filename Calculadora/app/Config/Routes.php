<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Calculadora::formulario');
$routes->get('formulario', 'Calculadora::formulario');
$routes->post('calcular', 'Calculadora::calcular'); // Para procesar el POST
