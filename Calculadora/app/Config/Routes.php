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

// ✅ RUTAS PARA COTIZACIONES
$routes->get('dolar/actualizar', 'Dolar::actualizar');
$routes->get('dolar/obtener', 'Dolar::obtener');

// ✅ RUTAS PARA AMAZON API
$routes->post('amazon/obtener', 'Amazon::obtener');
$routes->post('amazon/validar', 'Amazon::validar');

// ✅ NUEVA RUTA PARA CÁLCULO AVANZADO  
$routes->post('historial/calcular', 'Historial::calcular');

// 🧡 RUTAS PARA DONACIONES CON MERCADOPAGO - CORREGIDO
$routes->group('donacion', function($routes) {
    $routes->get('', 'DonacionController::index');
    $routes->post('crear', 'DonacionController::crear');
    $routes->post('webhook', 'DonacionController::webhook');

    // ✅ ESTA ES LA QUE NECESITÁS
    $routes->get('checkout/(:num)', 'DonacionController::checkout/$1');

    $routes->get('exito', 'DonacionController::exito');
    $routes->get('fallo', 'DonacionController::fallo');
    $routes->get('pendiente', 'DonacionController::exito');
    $routes->get('ver/(:num)', 'DonacionController::ver/$1');
    $routes->get('estadisticas', 'DonacionController::estadisticas');
});

// 🧡 RUTAS ALTERNATIVAS PARA DONACIONES (compatibilidad) - CORREGIDO
$routes->get('donar', 'DonacionController::index');
$routes->get('apoyo', 'DonacionController::index');
$routes->get('contribuir', 'DonacionController::index');

// DEBUG: Ruta simple de prueba
$routes->get('test-donacion', function() {
    return 'La ruta funciona';
});
$routes->get('donacion/test', 'DonacionController::testCredenciales');

// ✅ NUEVA RUTA PARA CÁLCULO AVANZADO  
$routes->post('historial/calcular', 'Historial::calcular');

// ✅ NUEVAS RUTAS PARA CATEGORÍAS Y SIMULACIONES
$routes->get('historial/categoria/(:num)', 'Historial::obtenerCategoria/$1');
$routes->post('historial/simular', 'Historial::simularCalculo');

// ✅ NUEVA RUTA PARA CÁLCULO AVANZADO  
$routes->post('historial/calcular', 'Historial::calcular');

// ✅ NUEVAS RUTAS PARA CATEGORÍAS Y SIMULACIONES
$routes->get('historial/categoria/(:num)', 'Historial::obtenerCategoria/$1');
$routes->post('historial/simular', 'Historial::simularCalculo');

// ✅ RUTAS PARA GESTIÓN DE CATEGORÍAS (ADMIN FUTURO)
$routes->group('admin', ['filter' => 'auth'], function($routes) {
    $routes->get('categorias', 'Admin::categorias');
    $routes->post('categorias/actualizar/(:num)', 'Admin::actualizarCategoria/$1');
    $routes->get('estadisticas', 'Admin::estadisticas');
});