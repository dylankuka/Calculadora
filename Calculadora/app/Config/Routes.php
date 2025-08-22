<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ✅ RUTA PRINCIPAL - REDIRIGE AL HISTORIAL
$routes->get('/', 'Historial::index');

// ✅ RUTAS DE USUARIO
$routes->get('usuario/registro', 'Usuario::registro');
$routes->post('usuario/registrar', 'Usuario::registrar');
$routes->get('usuario/login', 'Usuario::login');
$routes->post('usuario/iniciarSesion', 'Usuario::iniciarSesion');
$routes->get('usuario/logout', 'Usuario::logout');

// ✅ RUTAS DEL HISTORIAL
$routes->get('historial', 'Historial::index');
$routes->get('historial/crear', 'Historial::crear');
$routes->post('historial/guardar', 'Historial::guardar');
$routes->get('historial/ver/(:num)', 'Historial::ver/$1');
$routes->get('historial/editar/(:num)', 'Historial::editar/$1');
$routes->post('historial/actualizar/(:num)', 'Historial::actualizar/$1');
$routes->get('historial/eliminar/(:num)', 'Historial::eliminar/$1');

// ✅ RUTAS DE CALCULADORA (OPCIONAL)
$routes->get('calculadora', 'Calculadora::formulario');
$routes->post('calcular', 'Calculadora::calcular');
$routes->get('formulario', 'Calculadora::formulario');
