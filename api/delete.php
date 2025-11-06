<?php
// api/delete.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || !is_numeric($input['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID del token es requerido',
            'data' => null
        ]);
        exit;
    }
    
    $id = $input['id'];
    
    try {
        // Verificar si el token existe
        $checkStmt = $pdo->prepare("SELECT token FROM token_api WHERE id = ?");
        $checkStmt->execute([$id]);
        $tokenData = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$tokenData) {
            echo json_encode([
                'success' => false,
                'message' => 'El token no existe en la base de datos',
                'data' => null
            ]);
            exit;
        }
        
        // Eliminar token
        $stmt = $pdo->prepare("DELETE FROM token_api WHERE id = ?");
        $stmt->execute([$id]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Token eliminado exitosamente',
            'data' => [
                'id' => $id,
                'token' => $tokenData['token']
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al eliminar el token',
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