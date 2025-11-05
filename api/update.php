<?php
// api/update.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
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
    $token = isset($input['token']) ? trim($input['token']) : null;
    $nombre = isset($input['nombre']) ? trim($input['nombre']) : null;
    $descripcion = isset($input['descripcion']) ? trim($input['descripcion']) : null;
    $estado = isset($input['estado']) ? $input['estado'] : null;
    
    try {
        // Verificar si el token existe
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM token_api WHERE id = ?");
        $checkStmt->execute([$id]);
        $exists = $checkStmt->fetchColumn();
        
        if ($exists === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El token a actualizar no existe',
                'data' => null
            ]);
            exit;
        }
        
        // Verificar si el nuevo token ya existe (si se está cambiando)
        if ($token) {
            $checkTokenStmt = $pdo->prepare("SELECT COUNT(*) FROM token_api WHERE token = ? AND id != ?");
            $checkTokenStmt->execute([$token, $id]);
            $tokenExists = $checkTokenStmt->fetchColumn();
            
            if ($tokenExists > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'El nuevo token ya existe en la base de datos',
                    'data' => null
                ]);
                exit;
            }
        }
        
        // Construir consulta dinámica
        $fields = [];
        $params = [];
        
        if ($token) {
            $fields[] = "token = ?";
            $params[] = $token;
        }
        
        if ($nombre !== null) {
            $fields[] = "nombre = ?";
            $params[] = $nombre;
        }
        
        if ($descripcion !== null) {
            $fields[] = "descripcion = ?";
            $params[] = $descripcion;
        }
        
        if ($estado) {
            $fields[] = "estado = ?";
            $params[] = $estado;
        }
        
        if (empty($fields)) {
            echo json_encode([
                'success' => false,
                'message' => 'No hay campos para actualizar',
                'data' => null
            ]);
            exit;
        }
        
        $fields[] = "fecha_actualizacion = CURRENT_TIMESTAMP";
        $params[] = $id;
        
        $sql = "UPDATE token_api SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        echo json_encode([
            'success' => true,
            'message' => 'Token actualizado exitosamente',
            'data' => [
                'id' => $id,
                'token' => $token,
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'estado' => $estado
            ]
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar el token',
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