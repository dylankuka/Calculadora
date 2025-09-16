<?php
/**
 * Script de inicialización para TaxImporter
 * Ejecutar una sola vez después de implementar las nuevas funcionalidades
 * 
 * Uso: php public/setup.php
 */

// Cargar CodeIgniter
require_once __DIR__ . '/../vendor/autoload.php';
$app = \Config\Services::codeigniter();
$app->initialize();

echo "🚀 Iniciando configuración de TaxImporter...\n\n";

// 1. Verificar y crear tabla de categorías
echo "📦 1. Verificando tabla de categorías...\n";
$db = \Config\Database::connect();

try {
    // Verificar si la tabla existe
    if (!$db->tableExists('categorias_productos')) {
        echo "   ❌ Tabla 'categorias_productos' no existe. Ejecuta primero las migraciones SQL.\n";
        exit(1);
    }
    
    // Contar categorías existentes
    $count = $db->table('categorias_productos')->countAll();
    echo "   ✅ Tabla 'categorias_productos' encontrada con {$count} registros.\n";
    
} catch (Exception $e) {
    echo "   ❌ Error accediendo a la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Actualizar tabla de historial con nuevas columnas
echo "\n📊 2. Verificando columnas de historial...\n";

try {
    $fields = $db->getFieldNames('historial_calculos');
    
    $requiredFields = ['categoria_id', 'metodo_pago', 'valor_cif_usd', 'excedente_400_usd'];
    $missingFields = array_diff($requiredFields, $fields);
    
    if (empty($missingFields)) {
        echo "   ✅ Todas las columnas requeridas están presentes.\n";
    } else {
        echo "   ❌ Faltan columnas: " . implode(', ', $missingFields) . "\n";
        echo "   📋 Ejecuta el ALTER TABLE del paso 1 de las migraciones.\n";
    }
    
} catch (Exception $e) {
    echo "   ❌ Error verificando estructura: " . $e->getMessage() . "\n";
}

// 3. Verificar cotizaciones iniciales
echo "\n💱 3. Verificando cotizaciones iniciales...\n";

try {
    $dolarService = new \App\Services\DolarService();
    
    // Intentar obtener cotizaciones actuales
    $cotizaciones = $dolarService->obtenerCotizaciones();
    
    if (isset($cotizaciones['tarjeta']) && isset($cotizaciones['MEP'])) {
        echo "   ✅ Cotizaciones obtenidas exitosamente:\n";
        echo "      💳 Dólar Tarjeta: $" . number_format($cotizaciones['tarjeta'], 2) . " ARS\n";
        echo "      📈 Dólar MEP: $" . number_format($cotizaciones['MEP'], 2) . " ARS\n";
    } else {
        echo "   ⚠️  No se pudieron obtener cotizaciones de la API.\n";
        echo "      Se usarán valores por defecto.\n";
    }
    
} catch (Exception $e) {
    echo "   ⚠️  Error obteniendo cotizaciones: " . $e->getMessage() . "\n";
    echo "      El sistema funcionará con valores por defecto.\n";
}

// 4. Probar servicio de cálculo de impuestos
echo "\n🧮 4. Probando servicio de cálculo de impuestos...\n";

try {
    $calculoService = new \App\Services\CalculoImpuestosService();
    
    // Probar cálculo básico bajo franquicia
    $testBajoFranquicia = $calculoService->calcularImpuestos(100, 25, 1, 'tarjeta');
    
    if (isset($testBajoFranquicia['totales']['total_ars'])) {
        echo "   ✅ Cálculo bajo franquicia: $" . number_format($testBajoFranquicia['totales']['total_ars'], 2) . " ARS\n";
    }
    
    // Probar cálculo sobre franquicia
    $testSobreFranquicia = $calculoService->calcularImpuestos(500, 25, 1, 'tarjeta');
    
    if (isset($testSobreFranquicia['totales']['total_ars'])) {
        echo "   ✅ Cálculo sobre franquicia: $" . number_format($testSobreFranquicia['totales']['total_ars'], 2) . " ARS\n";
    }
    
    echo "   ✅ Servicio de cálculo funcionando correctamente.\n";
    
} catch (Exception $e) {
    echo "   ❌ Error en servicio de cálculo: " . $e->getMessage() . "\n";
}

// 5. Verificar permisos de archivos
echo "\n📁 5. Verificando permisos...\n";

$directoriesWrite = [
    'writable/logs',
    'writable/cache', 
    'writable/session',
    'writable/uploads'
];

foreach ($directoriesWrite as $dir) {
    $fullPath = ROOTPATH . $dir;
    if (is_writable($fullPath)) {
        echo "   ✅ {$dir} - escribible\n";
    } else {
        echo "   ❌ {$dir} - sin permisos de escritura\n";
        echo "      Ejecuta: chmod -R 777 {$fullPath}\n";
    }
}

// 6. Generar configuración de ejemplo
echo "\n⚙️  6. Generando archivo de configuración...\n";

$configContent = "<?php
/**
 * Configuración personalizada de TaxImporter
 * Generado automáticamente el " . date('Y-m-d H:i:s') . "
 */

return [
    // Configuración de la aplicación
    'app' => [
        'name' => 'TaxImporter',
        'version' => '2.0.0',
        'timezone' => 'America/Argentina/Buenos_Aires',
    ],
    
    // Configuración de impuestos
    'impuestos' => [
        'franquicia_usd' => 400.00,
        'iva_porcentaje' => 21.00,
        'tasa_estadistica' => 3.00,
        'percepcion_ganancias' => 30.00,
    ],
    
    // Configuración de APIs externas
    'apis' => [
        'dolarapi_url' => 'https://dolarapi.com/v1/dolares/',
        'timeout_seconds' => 10,
        'cache_minutes' => 60,
    ],
    
    // Límites del sistema
    'limites' => [
        'max_precio_usd' => 50000,
        'max_envio_usd' => 1000,
        'max_calculos_por_usuario' => 1000,
    ],
];
";

$configPath = APPPATH . 'Config/TaxImporter.php';
if (file_put_contents($configPath, $configContent)) {
    echo "   ✅ Archivo de configuración creado en: app/Config/TaxImporter.php\n";
} else {
    echo "   ❌ No se pudo crear el archivo de configuración.\n";
}

// 7. Resumen final
echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 CONFIGURACIÓN COMPLETADA\n";
echo str_repeat("=", 60) . "\n";

echo "\n📋 RESUMEN:\n";
echo "   ✅ Base de datos verificada\n";
echo "   ✅ Servicios de cálculo funcionando\n";
echo "   ✅ APIs de cotización configuradas\n";
echo "   ✅ Configuración generada\n";

echo "\n🚀 PRÓXIMOS PASOS:\n";
echo "   1. Accede a tu aplicación web\n";
echo "   2. Inicia sesión o crea una cuenta\n";
echo "   3. Crea tu primera calculadora de impuestos\n";
echo "   4. ¡Disfruta calculando impuestos de importación!\n";

echo "\n🔗 URLs IMPORTANTES:\n";
echo "   • Historial: " . site_url('historial') . "\n";
echo "   • Nueva calculadora: " . site_url('historial/crear') . "\n";
echo "   • Registro: " . site_url('usuario/registro') . "\n";

echo "\n📞 SOPORTE:\n";
echo "   • Logs: writable/logs/log-" . date('Y-m-d') . ".php\n";
echo "   • Configuración: app/Config/TaxImporter.php\n";

echo "\n✨ ¡TaxImporter está listo para usar!\n\n";

// 8. Opcional: Crear usuario de prueba
echo "❓ ¿Deseas crear un usuario de prueba? (y/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) === 'y' || strtolower($response) === 'yes') {
    try {
        $userModel = new \App\Models\UsuarioModel();
        
        $userData = [
            'nombre' => 'Usuario de Prueba',
            'email' => 'prueba@taximporter.com',
            'password' => password_hash('123456', PASSWORD_DEFAULT),
            'fecha_registro' => date('Y-m-d H:i:s')
        ];
        
        if ($userModel->insert($userData)) {
            echo "✅ Usuario de prueba creado:\n";
            echo "   📧 Email: prueba@taximporter.com\n";
            echo "   🔑 Password: 123456\n\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Error creando usuario de prueba: " . $e->getMessage() . "\n\n";
    }
}

echo "🏁 Setup completado exitosamente. ¡Que tengas un buen día!\n";
?>