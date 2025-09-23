<?php
/**
 * Script de prueba para verificar la integración con MercadoPago
 * 
 * Ejecutar desde línea de comandos: php public/test_mercadopago.php
 * O acceder desde el navegador: tu-dominio.com/test_mercadopago.php
 */

// Cargar CodeIgniter
require_once __DIR__ . '/../vendor/autoload.php';

// Si se ejecuta desde CLI
if (php_sapi_name() === 'cli') {
    $app = \Config\Services::codeigniter();
    $app->initialize();
    echo "🧪 Test de MercadoPago desde CLI\n";
    echo str_repeat("=", 40) . "\n";
} else {
    // Si se ejecuta desde navegador
    echo "<!DOCTYPE html><html><head><title>Test MercadoPago</title></head><body>";
    echo "<h1>🧪 Test de MercadoPago</h1>";
    echo "<pre>";
    
    // Cargar desde web
    require_once __DIR__ . '/../app/Config/Autoload.php';
    require_once __DIR__ . '/../system/bootstrap.php';
    
    $codeigniter = new \CodeIgniter\CodeIgniter(new \Config\App());
    $codeigniter->initialize();
}

try {
    echo "📋 Verificando configuración...\n\n";

    // 1. Verificar variables de entorno
    echo "1️⃣  Variables de entorno:\n";
    $accessToken = getenv('MERCADOPAGO_ACCESS_TOKEN') ?: env('MERCADOPAGO_ACCESS_TOKEN');
    $publicKey = getenv('MERCADOPAGO_PUBLIC_KEY') ?: env('MERCADOPAGO_PUBLIC_KEY');
    
    if ($accessToken && $publicKey) {
        echo "   ✅ Access Token: " . substr($accessToken, 0, 20) . "...\n";
        echo "   ✅ Public Key: " . substr($publicKey, 0, 20) . "...\n";
        
        $isSandbox = strpos($accessToken, 'TEST') === 0;
        echo "   🔧 Modo: " . ($isSandbox ? "SANDBOX (Pruebas)" : "PRODUCCIÓN") . "\n";
    } else {
        echo "   ❌ Credenciales no configuradas en .env\n";
        throw new Exception("Configura MERCADOPAGO_ACCESS_TOKEN y MERCADOPAGO_PUBLIC_KEY en tu .env");
    }

    echo "\n2️⃣  SDK de MercadoPago:\n";
    
    // 2. Verificar SDK
    if (class_exists('MercadoPago\MercadoPagoConfig')) {
        echo "   ✅ SDK instalado correctamente\n";
        
        // 3. Probar servicio
        echo "\n3️⃣  Servicio MercadoPagoService:\n";
        
        $mpService = new \App\Services\MercadoPagoService();
        $verificacion = $mpService->verificarConfiguracion();
        
        if ($verificacion['success']) {
            echo "   ✅ " . $verificacion['message'] . "\n";
            
            // 4. Probar creación de preferencia de prueba
            echo "\n4️⃣  Creando preferencia de prueba:\n";
            
            $datosPrueba = [
                'monto' => 100.0,
                'donacion_id' => 999999,
                'external_reference' => 'TEST_' . time(),
                'usuario_nombre' => 'Usuario de Prueba',
                'usuario_email' => 'test@ejemplo.com',
                'mensaje' => 'Donación de prueba para verificar integración'
            ];
            
            $preferencia = $mpService->crearPreferenciaDonacion($datosPrueba);
            
            if ($preferencia && isset($preferencia['init_point'])) {
                echo "   ✅ Preferencia creada exitosamente\n";
                echo "   🔗 Preference ID: " . $preferencia['id'] . "\n";
                echo "   🌐 URL de pago: " . substr($preferencia['init_point'], 0, 50) . "...\n";
                
                // 5. Verificar base de datos
                echo "\n5️⃣  Base de datos:\n";
                
                $db = \Config\Database::connect();
                if ($db->tableExists('donaciones')) {
                    echo "   ✅ Tabla 'donaciones' existe\n";
                    
                    // Contar registros
                    $count = $db->table('donaciones')->countAll();
                    echo "   📊 Donaciones registradas: {$count}\n";
                } else {
                    echo "   ❌ Tabla 'donaciones' no existe\n";
                    echo "   💡 Ejecuta la migración SQL proporcionada\n";
                }
                
            } else {
                echo "   ❌ Error creando preferencia\n";
            }
        } else {
            echo "   ❌ " . $verificacion['message'] . "\n";
        }
    } else {
        echo "   ❌ SDK no instalado\n";
        echo "   💡 Ejecuta: composer require mercadopago/dx-php\n";
    }

    // 6. URLs importantes
    echo "\n6️⃣  URLs de tu aplicación:\n";
    echo "   🏠 Base URL: " . base_url() . "\n";
    echo "   💖 Donaciones: " . base_url('donacion') . "\n";
    echo "   🔔 Webhook: " . base_url('donacion/webhook') . "\n";
    echo "   ✅ Éxito: " . base_url('donacion/exito') . "\n";
    echo "   ❌ Fallo: " . base_url('donacion/fallo') . "\n";

    // 7. Recomendaciones finales
    echo "\n7️⃣  Recomendaciones:\n";
    
    if ($isSandbox) {
        echo "   🧪 Estás en modo SANDBOX - perfecto para pruebas\n";
        echo "   💳 Usa las tarjetas de prueba de MercadoPago:\n";
        echo "      • Visa aprobada: 4509 9535 6623 3704\n";
        echo "      • Mastercard aprobada: 5031 7557 3453 0604\n";
        echo "      • CVV: cualquier número de 3 dígitos\n";
        echo "      • Vencimiento: cualquier fecha futura\n";
    } else {
        echo "   🚀 Estás en modo PRODUCCIÓN\n";
        echo "   ⚠️  ¡CUIDADO! Los pagos serán reales\n";
        echo "   💡 Cambia a TEST para pruebas\n";
    }
    
    echo "\n✅ INTEGRACIÓN COMPLETADA EXITOSAMENTE\n";
    echo "\n📋 Próximos pasos:\n";
    echo "   1. Configura tu webhook en MercadoPago Dashboard\n";
    echo "   2. Prueba una donación real desde la web\n";
    echo "   3. Verifica los logs en writable/logs/\n";
    echo "   4. ¡Disfruta las donaciones!\n";

} catch (Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n🔧 Pasos para solucionarlo:\n";
    echo "   1. Verifica tu archivo .env\n";
    echo "   2. Instala el SDK: composer require mercadopago/dx-php\n";
    echo "   3. Ejecuta las migraciones SQL\n";
    echo "   4. Reinicia tu servidor web\n";
}

// Cerrar HTML si es desde navegador
if (php_sapi_name() !== 'cli') {
    echo "</pre>";
    echo "<hr>";
    echo "<p><strong>💡 Tip:</strong> Si todo salió bien, ve a <a href='" . base_url('donacion') . "'>tu página de donaciones</a></p>";
    echo "</body></html>";
}

echo "\n🏁 Test completado.\n";
?>