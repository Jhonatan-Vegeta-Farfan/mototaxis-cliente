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
$stmt = $pdo->prepare("SELECT * FROM token_api WHERE id = ?");
$stmt->execute([$id]);
$token = $stmt->fetch();

if (!$token) {
    header('Location: tokens.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nuevo_token = $_POST['token'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    
    if (!empty($nuevo_token)) {
        try {
            $stmt = $pdo->prepare("UPDATE token_api SET token = ?, descripcion = ? WHERE id = ?");
            $stmt->execute([$nuevo_token, $descripcion, $id]);
            
            header('Location: tokens.php?mensaje=Token actualizado correctamente');
            exit();
        } catch (PDOException $e) {
            $mensaje = 'Error al actualizar el token: ' . $e->getMessage();
        }
    } else {
        $mensaje = 'Por favor, ingrese un token válido';
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">Editar Token</h2>
            </div>
            <div class="card-body">
                <?php if ($mensaje): ?>
                    <div class="alert alert-danger"><?php echo $mensaje; ?></div>
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
                        <label for="token" class="form-label">Token <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="token" name="token" rows="4" required><?php echo htmlspecialchars($token['token']); ?></textarea>
                        <div class="form-text">Token completo. Puede verlo completamente en la página de visualización.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">Actualizar Token</button>
                        <a href="tokens.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>