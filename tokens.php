<?php
require_once 'includes/auth_check.php';

// Asegurar que $pdo esté disponible
if (!isset($pdo) || $pdo === null) {
    // Intentar crear conexión directa si no existe
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=prograp_cliente_api", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Error de conexión directa: " . $e->getMessage());
        $pdo = null;
    }
}

// Incluir el modelo TokenApi si existe
$tokenApiModel = null;
if ($pdo && file_exists('models/TokenApi.php')) {
    require_once 'models/TokenApi.php';
    $tokenApiModel = new TokenApi($pdo);
}

// Manejar cambio de estado
if (isset($_GET['cambiar_estado']) && $pdo) {
    $id = $_GET['id'];
    $nuevo_estado = $_GET['cambiar_estado'];
    
    try {
        if ($tokenApiModel) {
            $resultado = $tokenApiModel->updateStatus($id, $nuevo_estado);
        } else {
            // Fallback directo a PDO
            $stmt = $pdo->prepare("UPDATE token_api SET estado = ? WHERE id = ?");
            $resultado = $stmt->execute([$nuevo_estado, $id]);
        }
        
        if ($resultado) {
            $mensaje = $nuevo_estado ? 'Token activado correctamente' : 'Token desactivado correctamente';
            header('Location: tokens.php?mensaje=' . urlencode($mensaje));
            exit();
        } else {
            header('Location: tokens.php?mensaje=Error al cambiar el estado del token');
            exit();
        }
    } catch (Exception $e) {
        header('Location: tokens.php?mensaje=Error: ' . $e->getMessage());
        exit();
    }
}

// Obtener todos los tokens
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT * FROM token_api ORDER BY id DESC");
        $tokens = $stmt->fetchAll();
    } else {
        $tokens = [];
    }
} catch (Exception $e) {
    $tokens = [];
    error_log("Error obteniendo tokens: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tokens - MotoTaxis Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <?php if (!$pdo): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Advertencia:</strong> No hay conexión a la base de datos. Las funciones de tokens no están disponibles.
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestión de Tokens</h1>
            <?php if ($pdo): ?>
                <a href="agregar_token.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Generar Token
                </a>
            <?php endif; ?>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-key me-2"></i>Lista de Tokens API
                </h5>
            </div>
            <div class="card-body">
                <?php if (count($tokens) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Descripción</th>
                                    <th>Estado</th>
                                    <th>Token</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tokens as $token): ?>
                                    <tr>
                                        <td><?php echo $token['id']; ?></td>
                                        <td>
                                            <?php if ($token['descripcion']): ?>
                                                <?php echo htmlspecialchars($token['descripcion']); ?>
                                            <?php else: ?>
                                                <span class="text-muted">Sin descripción</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($token['estado']): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="token-container">
                                                <code class="token-preview" id="token-preview-<?php echo $token['id']; ?>">
                                                    <?php echo substr($token['token'], 0, 30) . '...'; ?>
                                                </code>
                                                <code class="token-complete d-none" id="token-complete-<?php echo $token['id']; ?>">
                                                    <?php echo htmlspecialchars($token['token']); ?>
                                                </code>
                                                <button type="button" class="btn btn-sm btn-outline-primary ms-2" 
                                                        onclick="toggleToken(<?php echo $token['id']; ?>)">
                                                    <span class="toggle-text">Ver</span>
                                                </button>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <!-- Botón Activar/Desactivar -->
                                                <?php if ($token['estado']): ?>
                                                    <a href="tokens.php?cambiar_estado=0&id=<?php echo $token['id']; ?>" 
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('¿Estás seguro de desactivar este token?')"
                                                       data-bs-toggle="tooltip" title="Desactivar Token">
                                                        <i class="fas fa-toggle-off"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="tokens.php?cambiar_estado=1&id=<?php echo $token['id']; ?>" 
                                                       class="btn btn-sm btn-success"
                                                       onclick="return confirm('¿Estás seguro de activar este token?')"
                                                       data-bs-toggle="tooltip" title="Activar Token">
                                                        <i class="fas fa-toggle-on"></i>
                                                    </a>
                                                <?php endif; ?>
                                                
                                                <a href="ver_token.php?id=<?php echo $token['id']; ?>" 
                                                   class="btn btn-sm btn-info"
                                                   data-bs-toggle="tooltip" title="Ver Token Completo">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="editar_token.php?id=<?php echo $token['id']; ?>" 
                                                   class="btn btn-sm btn-warning"
                                                   data-bs-toggle="tooltip" title="Editar Descripción">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="eliminar_token.php?id=<?php echo $token['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('¿Estás seguro de eliminar este token?')"
                                                   data-bs-toggle="tooltip" title="Eliminar Token">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-key fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay tokens registrados</h4>
                        <p class="text-muted mb-4">Comienza generando tu primer token para acceder a la API.</p>
                        <?php if ($pdo): ?>
                            <a href="agregar_token.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-plus me-2"></i>Generar Primer Token
                            </a>
                        <?php else: ?>
                            <p class="text-danger">No se puede generar tokens sin conexión a la base de datos.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
    function toggleToken(tokenId) {
        const preview = document.getElementById('token-preview-' + tokenId);
        const complete = document.getElementById('token-complete-' + tokenId);
        const button = preview.nextElementSibling;
        const toggleText = button.querySelector('.toggle-text');
        
        if (preview.classList.contains('d-none')) {
            preview.classList.remove('d-none');
            complete.classList.add('d-none');
            toggleText.textContent = 'Ver';
        } else {
            preview.classList.add('d-none');
            complete.classList.remove('d-none');
            toggleText.textContent = 'Ocultar';
        }
    }

    // Inicializar tooltips de Bootstrap
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>