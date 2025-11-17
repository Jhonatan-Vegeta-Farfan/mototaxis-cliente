<?php
// config/development.php
define('BASE_URL', 'http://localhost/mototaxis-cliente');
define('ENVIRONMENT', 'development');

// Configuración de base de datos para desarrollo
$development_config = [
    'host' => 'localhost',
    'dbname' => 'dpwebcom_mototaxis_huanta',
    'username' => 'root',
    'password' => ''
];

// Mostrar errores en desarrollo
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
}
?>