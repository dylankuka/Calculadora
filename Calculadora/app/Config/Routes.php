<?php
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ✅ RUTAS PÚBLICAS
$routes->get('/', 'Usuario::login'); // Cambiar página de inicio a login
$routes->group('usuario', function($routes) {
    $routes->get('registro', 'Usuario::registro');
    $routes->post('registrar', 'Usuario::registrar');
    $routes->get('login', 'Usuario::login');
    $routes->post('iniciarSesion', 'Usuario::iniciarSesion');
    $routes->get('logout', 'Usuario::logout');
});

// ✅ RUTAS PROTEGIDAS (Solo usuarios logueados)
$routes->group('', ['filter' => 'auth'], function($routes) {
    // Historial CRUD
    $routes->get('historial', 'Historial::index');
    $routes->get('historial/crear', 'Historial::crear');
    $routes->post('historial/guardar', 'Historial::guardar');
    $routes->get('historial/ver/(:num)', 'Historial::ver/$1');
    $routes->get('historial/editar/(:num)', 'Historial::editar/$1');
    $routes->post('historial/actualizar/(:num)', 'Historial::actualizar/$1');
    $routes->get('historial/eliminar/(:num)', 'Historial::eliminar/$1');
    
    // Calculadora original (opcional, para más adelante)
    $routes->get('calculadora', 'Calculadora::formulario');
    $routes->post('calcular', 'Calculadora::calcular');
});