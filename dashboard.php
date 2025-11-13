<?php
// Incluir auth_check primero
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

// Obtener estadísticas
try {
    if ($pdo) {
        $stmt = $pdo->query("SELECT COUNT(*) as total_tokens FROM token_api");
        $total_tokens = $stmt->fetch()['total_tokens'];

        $stmt = $pdo->query("SELECT COUNT(*) as tokens_activos FROM token_api WHERE estado = 1");
        $tokens_activos = $stmt->fetch()['tokens_activos'];

        $stmt = $pdo->query("SELECT COUNT(*) as tokens_inactivos FROM token_api WHERE estado = 0");
        $tokens_inactivos = $stmt->fetch()['tokens_inactivos'];

        $stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios");
        $total_usuarios = $stmt->fetch()['total_usuarios'];

        // Obtener todos los tokens para mostrar en el dashboard
        $stmt = $pdo->query("SELECT * FROM token_api ORDER BY id DESC");
        $tokens = $stmt->fetchAll();
    } else {
        throw new Exception("No hay conexión a la base de datos");
    }
} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    $total_tokens = 0;
    $tokens_activos = 0;
    $tokens_inactivos = 0;
    $total_usuarios = 0;
    $tokens = [];
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoTaxis Cliente - Panel de Control</title>
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
                <strong>Advertencia:</strong> No hay conexión a la base de datos. Algunas funciones pueden no estar disponibles.
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <h1>PANEL DE CONTROL</h1>
                <p class="lead">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Tokens</h5>
                                <h2 class="card-text"><?php echo $total_tokens; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-key fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Tokens Activos</h5>
                                <h2 class="card-text"><?php echo $tokens_activos; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Tokens Inactivos</h5>
                                <h2 class="card-text"><?php echo $tokens_inactivos; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Total Usuarios</h5>
                                <h2 class="card-text"><?php echo $total_usuarios; ?></h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Acciones Rápidas</h5>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="tokens.php" class="btn btn-primary me-md-2">Ver Todos los Tokens</a>
                            <a href="agregar_token.php" class="btn btn-success">Generar Nuevo Token</a>
                            <a href="api.php" class="btn btn-info" target="_blank">
                                <i class="fas fa-external-link-alt me-1"></i>
                                Acceder a API Pública
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Previa de Todos los Tokens -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Vista Previa de Tokens</h5>
                        <div>
                            <span class="badge bg-primary"><?php echo $total_tokens; ?> tokens</span>
                            <span class="badge bg-success"><?php echo $tokens_activos; ?> activos</span>
                            <span class="badge bg-warning"><?php echo $tokens_inactivos; ?> inactivos</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (count($tokens) > 0): ?>
                            <div class="row">
                                <?php foreach ($tokens as $token): ?>
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 token-card">
                                            <div class="card-header">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="card-title mb-0">Token #<?php echo $token['id']; ?></h6>
                                                    <div>
                                                        <?php if ($token['estado']): ?>
                                                            <span class="badge bg-success">Activo</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Inactivo</span>
                                                        <?php endif; ?>
                                                        <span class="badge bg-secondary"><?php echo strlen($token['token']); ?> chars</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <!-- Descripción -->
                                                <?php if ($token['descripcion']): ?>
                                                    <p class="card-text mb-2">
                                                        <strong>Descripción:</strong><br>
                                                        <?php echo htmlspecialchars($token['descripcion']); ?>
                                                    </p>
                                                    <hr>
                                                <?php endif; ?>

                                                <!-- Token con toggle -->
                                                <div class="mb-3">
                                                    <label class="form-label small text-muted">Token:</label>
                                                    <div class="token-container">
                                                        <code class="token-preview" id="dashboard-preview-<?php echo $token['id']; ?>">
                                                            <?php echo substr($token['token'], 0, 25) . '...'; ?>
                                                        </code>
                                                        <code class="token-complete d-none" id="dashboard-complete-<?php echo $token['id']; ?>">
                                                            <?php echo htmlspecialchars($token['token']); ?>
                                                        </code>
                                                    </div>
                                                </div>

                                                <!-- Estado y acciones -->
                                                <div class="mb-3">
                                                    <strong>Estado:</strong>
                                                    <?php if ($token['estado']): ?>
                                                        <span class="text-success">✅ Activo</span>
                                                    <?php else: ?>
                                                        <span class="text-danger">❌ Inactivo</span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Acciones rápidas -->
                                                <div class="btn-group w-100" role="group">
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            onclick="toggleDashboardToken(<?php echo $token['id']; ?>)">
                                                        <span class="toggle-text-<?php echo $token['id']; ?>">Ver</span>
                                                    </button>
                                                    
                                                    <!-- Botón Activar/Desactivar -->
                                                    <?php if ($token['estado']): ?>
                                                        <a href="tokens.php?cambiar_estado=0&id=<?php echo $token['id']; ?>" 
                                                           class="btn btn-sm btn-outline-warning"
                                                           onclick="return confirm('¿Desactivar este token?')">
                                                            Desactivar
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="tokens.php?cambiar_estado=1&id=<?php echo $token['id']; ?>" 
                                                           class="btn btn-sm btn-outline-success"
                                                           onclick="return confirm('¿Activar este token?')">
                                                            Activar
                                                        </a>
                                                    <?php endif; ?>
                                                    
                                                    <a href="ver_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-outline-info">
                                                        Completo
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="card-footer text-muted small">
                                                <div class="d-flex justify-content-between">
                                                    <span>ID: <?php echo $token['id']; ?></span>
                                                    <a href="eliminar_token.php?id=<?php echo $token['id']; ?>" 
                                                       class="text-danger" 
                                                       onclick="return confirm('¿Estás seguro de eliminar este token?')">
                                                        Eliminar
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No hay tokens registrados.</p>
                                <a href="agregar_token.php" class="btn btn-primary">Generar Primer Token</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function toggleDashboardToken(tokenId) {
            const preview = document.getElementById('dashboard-preview-' + tokenId);
            const complete = document.getElementById('dashboard-complete-' + tokenId);
            const toggleText = document.querySelector('.toggle-text-' + tokenId);
            
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
        </script>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>