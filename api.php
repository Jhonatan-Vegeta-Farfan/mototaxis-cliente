<?php
// api.php - Punto de entrada para la API Pública

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

try {
    // Incluir archivos de configuración
    $config_file = 'config/database.php';
    if (!file_exists($config_file)) {
        throw new Exception("Archivo de configuración no encontrado: " . $config_file);
    }
    
    require_once $config_file;
    
    // Incluir controlador
    $controller_file = 'controllers/ApiPublicController.php';
    if (!file_exists($controller_file)) {
        throw new Exception("Controlador no encontrado: " . $controller_file);
    }
    
    require_once $controller_file;

    // Configurar conexión a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Si no hay conexión a BD, continuar sin ella
    if (!$db) {
        error_log("Advertencia: No se pudo conectar a la base de datos, usando modo de respaldo");
    }

    // Crear instancia del controlador
    $apiController = new ApiPublicController($db);

    // Obtener la acción desde la URL
    $action = $_GET['action'] ?? 'index';

    // Enrutar las solicitudes
    switch ($action) {
        case 'listar':
            $apiController->listarMototaxis();
            break;
        case 'buscar':
            $apiController->buscarMototaxi();
            break;
        case 'validar_token':
            $apiController->validarTokenEndpoint();
            break;
        case 'verificar_api':
            $apiController->verificarApiExterna();
            break;
        case 'obtener_datos_api':
            $apiController->obtenerDatosApiExterna();
            break;
        case 'index':
        default:
            $apiController->index();
            break;
    }

} catch (Exception $e) {
    // Manejo centralizado de errores
    error_log("Error en API: " . $e->getMessage());
    
    // Verificar si los headers JSON ya fueron enviados
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    
    // Limpiar buffer de salida
    if (ob_get_length()) {
        ob_clean();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error_details' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>