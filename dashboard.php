<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Obtener estadísticas
$stmt = $pdo->query("SELECT COUNT(*) as total_tokens FROM token_api");
$total_tokens = $stmt->fetch()['total_tokens'];

$stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios");
$total_usuarios = $stmt->fetch()['total_usuarios'];

// Obtener todos los tokens para mostrar en el dashboard
$stmt = $pdo->query("SELECT * FROM token_api ORDER BY id DESC");
$tokens = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <h1>Dashboard</h1>
        <p class="lead">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card text-white bg-primary mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total de Tokens</h5>
                        <h2 class="card-text"><?php echo $total_tokens; ?></h2>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-key fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card text-white bg-success mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title">Total de Usuarios</h5>
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
                    <a href="agregar_token.php" class="btn btn-success">Agregar Nuevo Token</a>
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
                <span class="badge bg-primary"><?php echo $total_tokens; ?> tokens</span>
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
                                            <span class="badge bg-secondary"><?php echo strlen($token['token']); ?> chars</span>
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

                                        <!-- Acciones rápidas -->
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="toggleDashboardToken(<?php echo $token['id']; ?>)">
                                                <span class="toggle-text-<?php echo $token['id']; ?>">Ver</span>
                                            </button>
                                            <a href="ver_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-outline-info">
                                                Completo
                                            </a>
                                            <a href="editar_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-outline-warning">
                                                Editar
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
                        <a href="agregar_token.php" class="btn btn-primary">Agregar Primer Token</a>
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

// Función para copiar token desde el dashboard
function copiarDashboardToken(tokenId) {
    const tokenCompleto = document.getElementById('dashboard-complete-' + tokenId).textContent;
    
    navigator.clipboard.writeText(tokenCompleto).then(function() {
        // Mostrar mensaje de éxito
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(function() {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    }).catch(function(err) {
        alert('Error al copiar el token: ' + err);
    });
}
</script>

<?php include 'includes/footer.php'; ?>