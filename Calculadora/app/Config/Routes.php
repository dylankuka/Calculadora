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
$routes->get('usuario/olvide-password', 'Usuario::olvidePassword');
$routes->post('usuario/enviar-recuperacion', 'Usuario::enviarRecuperacion');
$routes->get('usuario/resetear/(:any)', 'Usuario::resetear/$1');
$routes->post('usuario/guardar-password', 'Usuario::guardarPassword');

// ✅ RUTAS DEL HISTORIAL
$routes->get('historial', 'Historial::index');
$routes->get('historial/crear', 'Historial::crear');
$routes->post('historial/guardar', 'Historial::guardar');
$routes->get('historial/ver/(:num)', 'Historial::ver/$1');
$routes->get('historial/editar/(:num)', 'Historial::editar/$1');
$routes->post('historial/actualizar/(:num)', 'Historial::actualizar/$1');
$routes->get('historial/eliminar/(:num)', 'Historial::eliminar/$1');
$routes->post('historial/calcular', 'Historial::calcular');
$routes->get('historial/categoria/(:num)', 'Historial::obtenerCategoria/$1');
$routes->post('historial/simular', 'Historial::simularCalculo');

// ✅ RUTAS DE CALCULADORA (OPCIONAL)
$routes->get('calculadora', 'Calculadora::formulario');
$routes->post('calcular', 'Calculadora::calcular');
$routes->get('formulario', 'Calculadora::formulario');

// ✅ RUTAS PARA COTIZACIONES
$routes->get('dolar/actualizar', 'Dolar::actualizar');
$routes->get('dolar/obtener', 'Dolar::obtener');

// ✅ RUTAS PARA RAINFOREST API (AMAZON)
$routes->post('amazon/obtener', 'Amazon::obtener');
$routes->post('amazon/validar', 'Amazon::validar');
$routes->post('amazon/buscar', 'Amazon::buscar');
$routes->get('amazon/testConexion', 'Amazon::testConexion');
$routes->get('amazon/verificarCuenta', 'Amazon::verificarCuenta');

// ✅ RUTA PARA TEST DE RAINFOREST API
$routes->get('test-rainforest', 'TestRainforest::index');
$routes->get('test/rainforest', 'TestRainforest::index'); // Alias

// 🧡 RUTAS PARA DONACIONES CON MERCADOPAGO
$routes->group('donacion', function($routes) {
    $routes->get('', 'DonacionController::index');
    $routes->post('crear', 'DonacionController::crear');
    $routes->post('webhook', 'DonacionController::webhook');
    $routes->get('checkout/(:num)', 'DonacionController::checkout/$1');
    $routes->get('success', 'DonacionController::success');
    $routes->get('exito', 'DonacionController::exito');
    $routes->get('failure', 'DonacionController::failure');
    $routes->get('fallo', 'DonacionController::fallo');
    $routes->get('pendiente', 'DonacionController::exito');
    $routes->get('ver/(:num)', 'DonacionController::ver/$1');
    $routes->get('estadisticas', 'DonacionController::estadisticas');
});

// 🧡 RUTAS ALTERNATIVAS PARA DONACIONES (compatibilidad)
$routes->get('donar', 'DonacionController::index');
$routes->get('apoyo', 'DonacionController::index');
$routes->get('contribuir', 'DonacionController::index');

// ✅ RUTAS DE ADMINISTRACIÓN (UNIFICADAS)
$routes->group('admin', function($routes) {
    // Dashboard unificado (todas las secciones en tabs)
    $routes->get('', 'Admin::index');
    
    // Acciones de usuarios
    $routes->post('usuarios/cambiar-rol/(:num)', 'Admin::cambiarRol/$1');
    $routes->get('usuarios/toggle/(:num)', 'Admin::toggleUsuario/$1');
    
    // Acciones de cotizaciones
    $routes->get('cotizaciones/actualizar', 'Admin::actualizarCotizaciones');
    
    // Acciones de categorías
    $routes->post('categorias/actualizar/(:num)', 'Admin::actualizarCategoria/$1');
    
    // Rutas legacy (redirigen al dashboard con tab correspondiente)
    $routes->get('usuarios', 'Admin::usuarios');
    $routes->get('donaciones', 'Admin::donaciones');
    $routes->get('cotizaciones', 'Admin::cotizaciones');
    $routes->get('categorias', 'Admin::categorias');
    $routes->get('estadisticas', 'Admin::estadisticas');
});