<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

if (!isset($_GET['id'])) {
    header('Location: tokens.php');
    exit();
}

$id = $_GET['id'];

// Obtener token actual
$stmt = $pdo->prepare("SELECT * FROM tokens_api WHERE id = ?");
$stmt->execute([$id]);
$token = $stmt->fetch();

if (!$token) {
    header('Location: tokens.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Token - MotoTaxis Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title mb-0">Visualización Completa del Token</h2>
                        <a href="tokens.php" class="btn btn-secondary btn-sm">Volver a la lista</a>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Información del Token</h5>
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="30%">ID:</th>
                                        <td><?php echo $token['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Descripción:</th>
                                        <td>
                                            <?php if ($token['descripcion']): ?>
                                                <?php echo htmlspecialchars($token['descripcion']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin descripción</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Estado:</th>
                                        <td>
                                            <?php if ($token['estado']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Longitud:</th>
                                        <td><?php echo strlen($token['token']); ?> caracteres</td>
                                    </tr>
                                    <tr>
                                        <th>Fecha Registro:</th>
                                        <td><?php echo $token['fecha_registro']; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Token Completo:</label>
                            <div class="card">
                                <div class="card-body">
                                    <code class="token-full" style="font-size: 0.9em; word-break: break-all; white-space: pre-wrap;">
                                        <?php echo htmlspecialchars($token['token']); ?>
                                    </code>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-success" onclick="copiarToken()">
                                <i class="fas fa-copy me-1"></i>Copiar Token al Portapapeles
                            </button>
                            
                            <!-- Botón para cambiar estado -->
                            <?php if ($token['estado']): ?>
                                <a href="tokens.php?cambiar_estado=0&id=<?php echo $token['id']; ?>" 
                                   class="btn btn-warning"
                                   onclick="return confirm('¿Estás seguro de desactivar este token?')">
                                    <i class="fas fa-toggle-off me-1"></i>Desactivar Token
                                </a>
                            <?php else: ?>
                                <a href="tokens.php?cambiar_estado=1&id=<?php echo $token['id']; ?>" 
                                   class="btn btn-success"
                                   onclick="return confirm('¿Estás seguro de activar este token?')">
                                    <i class="fas fa-toggle-on me-1"></i>Activar Token
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="alert alert-info">
                            <strong>Nota:</strong> 
                            <ul class="mb-0">
                                <li>Este token es sensible. Asegúrese de manejarlo con cuidado y no compartirlo innecesariamente.</li>
                                <li>Los tokens inactivos no podrán acceder a la API pública.</li>
                                <li>Puede activar/desactivar el token según sea necesario.</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2 d-md-flex mt-4">
                            <a href="editar_token.php?id=<?php echo $token['id']; ?>" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Editar Descripción
                            </a>
                            <a href="tokens.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-1"></i>Volver a la Lista
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function copiarToken() {
        const tokenText = `<?php echo $token['token']; ?>`;
        
        navigator.clipboard.writeText(tokenText).then(function() {
            // Mostrar mensaje de éxito
            const btn = event.target;
            const originalText = btn.textContent;
            btn.textContent = '¡Copiado!';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-secondary');
            
            setTimeout(function() {
                btn.textContent = originalText;
                btn.classList.remove('btn-secondary');
                btn.classList.add('btn-success');
            }, 2000);
        }).catch(function(err) {
            alert('Error al copiar el token: ' + err);
        });
    }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>