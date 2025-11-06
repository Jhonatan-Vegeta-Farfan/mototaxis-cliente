<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $token = $_POST['token'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    
    if (!empty($token)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO token_api (token, descripcion) VALUES (?, ?)");
            $stmt->execute([$token, $descripcion]);
            
            header('Location: tokens.php?mensaje=Token agregado correctamente');
            exit();
        } catch (PDOException $e) {
            $mensaje = 'Error al agregar el token: ' . $e->getMessage();
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
                <h2 class="card-title mb-0">Agregar Nuevo Token</h2>
            </div>
            <div class="card-body">
                <?php if ($mensaje): ?>
                    <div class="alert alert-danger"><?php echo $mensaje; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                        <input type="text" class="form-control" id="descripcion" name="descripcion" 
                               placeholder="Ej: Token para API de producción" maxlength="255">
                        <div class="form-text">Describe para qué se usará este token.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="token" class="form-label">Token <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="token" name="token" rows="4" 
                                  placeholder="Ingrese el token API completo" required></textarea>
                        <div class="form-text">Pegue aquí el token completo que desea almacenar.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary">Agregar Token</button>
                        <a href="tokens.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>