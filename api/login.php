<?php
// api/login.php
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario y contraseña son requeridos',
            'data' => null
        ]);
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    try {
        // Verificar credenciales en la base de datos
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE nombre = ? AND contrasena = ?");
        $stmt->execute([$username, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Login exitoso
            $user_data = [
                'id' => $user['id'],
                'username' => $user['nombre'],
                'name' => $user['nombre'],
                'role' => 'Administrador',
                'login_time' => date('Y-m-d H:i:s')
            ];
            
            echo json_encode([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => $user_data
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos',
                'data' => null
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en el servidor',
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