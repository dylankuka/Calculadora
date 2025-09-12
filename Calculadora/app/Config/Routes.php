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

// 🧡 RUTAS PARA DONACIONES CON MERCADOPAGO
$routes->group('donacion', function($routes) {
    // Página principal de donaciones
    $routes->get('', 'Donacion::index');
    
    // Crear nueva donación y redirigir a MercadoPago
    $routes->post('crear', 'Donacion::crear');
    
    // Webhook para notificaciones de MercadoPago (sin autenticación)
    $routes->post('webhook', 'Donacion::webhook');
    
    // Páginas de retorno desde MercadoPago
    $routes->get('exito', 'Donacion::exito');
    $routes->get('fallo', 'Donacion::fallo');
    $routes->get('pendiente', 'Donacion::exito'); // Redirige al mismo lugar
    
    // Ver detalles de una donación específica
    $routes->get('ver/(:num)', 'Donacion::ver/$1');
    
    // Rutas adicionales para administración (futuro)
    $routes->get('estadisticas', 'Donacion::estadisticas');
});

// 🧡 RUTAS ALTERNATIVAS PARA DONACIONES (compatibilidad)
$routes->get('donar', 'Donacion::index');
$routes->get('apoyo', 'Donacion::index');
$routes->get('contribuir', 'Donacion::index');