<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: tokens.php');
    exit();
}

$id = $_GET['id'];
$mensaje = '';

// Obtener token actual
$stmt = $pdo->prepare("SELECT * FROM tokens_api WHERE id = ?");
$stmt->execute([$id]);
$token = $stmt->fetch();

if (!$token) {
    header('Location: tokens.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE tokens_api SET descripcion = ? WHERE id = ?");
        $stmt->execute([$descripcion, $id]);
        
        header('Location: tokens.php?mensaje=Token actualizado correctamente');
        exit();
    } catch (PDOException $e) {
        $mensaje = 'Error al actualizar el token: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Token - MotoTaxis Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title mb-0">Editar Token</h2>
                    </div>
                    <div class="card-body">
                        <?php if ($mensaje): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion" 
                                       value="<?php echo htmlspecialchars($token['descripcion'] ?? ''); ?>"
                                       placeholder="Ej: Token para API de producción" maxlength="255">
                                <div class="form-text">Describe para qué se usará este token.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Token Actual <span class="text-danger">*</span></label>
                                <div class="card">
                                    <div class="card-body">
                                        <code class="token-full" style="font-size: 0.9em; word-break: break-all;">
                                            <?php echo htmlspecialchars($token['token']); ?>
                                        </code>
                                    </div>
                                </div>
                                <div class="form-text">El token no puede ser modificado. Si necesita un nuevo token, elimine este y genere uno nuevo.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Estado Actual</label>
                                <div>
                                    <?php if ($token['estado']): ?>
                                        <span class="badge bg-success">Activo</span>
                                        <span class="text-muted">- El token puede acceder a la API</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactivo</span>
                                        <span class="text-muted">- El token NO puede acceder a la API</span>
                                    <?php endif; ?>
                                </div>
                                <div class="form-text">
                                    Para cambiar el estado, use los botones en la lista de tokens o en la vista completa del token.
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Actualizar Descripción
                                </button>
                                <a href="tokens.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Cancelar
                                </a>
                                <a href="ver_token.php?id=<?php echo $token['id']; ?>" class="btn btn-info">
                                    <i class="fas fa-eye me-1"></i>Ver Token Completo
                                </a>
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