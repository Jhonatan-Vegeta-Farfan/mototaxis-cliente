<?php
// api/login.php
header('Content-Type: application/json');

// Simulación de base de datos de usuarios (en un caso real, esto vendría de una BD)
$valid_users = [
    'jhonatan', 'vegeta' => [
        'password' => '123456789',
        'name' => 'Jhonatan','Vegeta',
        'role' => 'Administrador'
    ]
];

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
    
    // Verificar credenciales
    if (isset($valid_users[$username]) && $valid_users[$username]['password'] === $password) {
        // Iniciar sesión (en un caso real usaríamos session_start() y $_SESSION)
        $user_data = [
            'username' => $username,
            'name' => $valid_users[$username]['name'],
            'role' => $valid_users[$username]['role'],
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
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido',
        'data' => null
    ]);
}
?>