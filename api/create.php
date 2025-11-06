<?php
// api/create.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar campos requeridos
    if (!isset($input['token']) || empty(trim($input['token']))) {
        echo json_encode([
            'success' => false,
            'message' => 'El token es requerido',
            'data' => null
        ]);
        exit;
    }
    
    $token = trim($input['token']);
    
    try {
        // Verificar si el token ya existe
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM token_api WHERE token = ?");
        $checkStmt->execute([$token]);
        $exists = $checkStmt->fetchColumn();
        
        if ($exists > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El token ya existe en la base de datos',
                'data' => null
            ]);
            exit;
        }
        
        // Insertar nuevo token
        $stmt = $pdo->prepare("INSERT INTO token_api (token) VALUES (?)");
        $stmt->execute([$token]);
        
        // Obtener el ID del token insertado
        $tokenId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Token creado exitosamente',
            'data' => [
                'id' => $tokenId,
                'token' => $token
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear el token',
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
?>