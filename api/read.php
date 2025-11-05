<?php
// api/read.php
include 'config.php';

// Verificar autenticación básica
checkAuthentication();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT token FROM token_api ORDER BY token");
        $stmt->execute();
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'message' => 'Tokens obtenidos exitosamente',
            'data' => [
                'tokens' => $tokens,
                'total' => count($tokens)
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener los tokens',
            'error' => $e->getMessage(),
            'data' => null
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido',
        'data' => null
    ]);
}

// Función para verificar autenticación básica
function checkAuthentication() {
    // En una aplicación real, aquí verificarías la sesión o token JWT
    // Por simplicidad, solo verificamos que venga de nuestra aplicación
    $allowed_origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
    $allowed_hosts = ['localhost', '127.0.0.1'];
    
    $is_allowed = false;
    foreach ($allowed_hosts as $host) {
        if (strpos($allowed_origin, $host) !== false) {
            $is_allowed = true;
            break;
        }
    }
    
    if (!$is_allowed && !empty($allowed_origin)) {
        echo json_encode([
            'success' => false,
            'message' => 'Acceso no autorizado',
            'data' => null
        ]);
        exit;
    }
}
?>