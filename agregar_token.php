<?php
require_once 'includes/auth_check.php';

if (!isset($pdo) || $pdo === null) {
    die("Error: No hay conexión a la base de datos");
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'] ?? '';
    
    try {
        // Obtener el último número consecutivo usado
        $stmt = $pdo->query("SELECT token FROM tokens_api ORDER BY id DESC LIMIT 1");
        $ultimo_token = $stmt->fetch();
        
        $numero_consecutivo = 1;
        if ($ultimo_token) {
            if (preg_match('/_(\d+)$/', $ultimo_token['token'], $matches)) {
                $numero_consecutivo = (int)$matches[1] + 1;
            }
        }
        
        // Generar token único automáticamente
        $token_base = bin2hex(random_bytes(32));
        $token_completo = $token_base . '_' . $numero_consecutivo;
        
        // Insertar token con estado activo por defecto
        $stmt = $pdo->prepare("INSERT INTO tokens_api (token, descripcion, estado) VALUES (?, ?, 1)");
        $stmt->execute([$token_completo, $descripcion]);
        
        header('Location: tokens.php?mensaje=Token generado y agregado correctamente');
        exit();
    } catch (PDOException $e) {
        $mensaje = 'Error al generar el token: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Token - MotoTaxis Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title mb-0">Generar Nuevo Token</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion" 
                                       placeholder="Ej: Token para API de producción" maxlength="255">
                                <div class="form-text">Describe para qué se usará este token.</div>
                            </div>
                            
                            <div class="alert alert-info">
                                <strong>Información:</strong> 
                                <ul class="mb-0">
                                    <li>El token se generará automáticamente con un valor único y un número consecutivo al final.</li>
                                    <li>El token se creará en estado <strong>Activo</strong> por defecto.</li>
                                    <li>Puedes activar/desactivar el token posteriormente desde la lista de tokens.</li>
                                </ul>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">Generar Token Automáticamente</button>
                                <a href="tokens.php" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>