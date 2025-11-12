<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Incluir el modelo TokenApi
require_once 'models/TokenApi.php';

// Manejar cambio de estado
if (isset($_GET['cambiar_estado'])) {
    $id = $_GET['id'];
    $nuevo_estado = $_GET['cambiar_estado'];
    
    try {
        $tokenApiModel = new TokenApi($pdo);
        $resultado = $tokenApiModel->updateStatus($id, $nuevo_estado);
        
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
$stmt = $pdo->query("SELECT * FROM token_api ORDER BY id DESC");
$tokens = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gestión de Tokens</h1>
    <a href="agregar_token.php" class="btn btn-success">Generar Token</a>
</div>

<?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_GET['mensaje']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Token</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tokens) > 0): ?>
                        <?php foreach ($tokens as $token): ?>
                            <tr>
                                <td><?php echo $token['id']; ?></td>
                                <td>
                                    <?php echo $token['descripcion'] ? htmlspecialchars($token['descripcion']) : '<span class="text-muted">Sin descripción</span>'; ?>
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
                                               onclick="return confirm('¿Estás seguro de desactivar este token?')">
                                                Desactivar
                                            </a>
                                        <?php else: ?>
                                            <a href="tokens.php?cambiar_estado=1&id=<?php echo $token['id']; ?>" 
                                               class="btn btn-sm btn-success"
                                               onclick="return confirm('¿Estás seguro de activar este token?')">
                                                Activar
                                            </a>
                                        <?php endif; ?>
                                        
                                        <a href="ver_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-info">Ver Completo</a>
                                        <a href="editar_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                                        <a href="eliminar_token.php?id=<?php echo $token['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro de eliminar este token?')">Eliminar</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay tokens registrados</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
</script>

<?php include 'includes/footer.php'; ?>