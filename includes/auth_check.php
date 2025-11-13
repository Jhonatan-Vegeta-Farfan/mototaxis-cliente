<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Inicializar conexión a BD si no existe
if (!isset($pdo)) {
    // Intentar diferentes rutas posibles
    $possible_paths = [
        __DIR__ . '/../config/database.php',
        __DIR__ . '/../../config/database.php',
        'config/database.php',
        '../config/database.php'
    ];
    
    $database_loaded = false;
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $database_loaded = true;
            break;
        }
    }
    
    if (!$database_loaded) {
        // Si no se encuentra el archivo, crear una conexión básica
        error_log("No se pudo encontrar config/database.php, usando modo respaldo");
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=prograp_cliente_api", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("Error de conexión en auth_check: " . $e->getMessage());
            $pdo = null;
        }
    } else {
        // Si se cargó el archivo, crear la instancia de Database
        try {
            $database = new Database();
            $pdo = $database->getConnection();
        } catch (Exception $e) {
            error_log("Error inicializando Database: " . $e->getMessage());
            $pdo = null;
        }
    }
}
?>