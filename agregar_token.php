<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $descripcion = $_POST['descripcion'] ?? '';
    
    try {
        // Obtener el último número consecutivo usado
        $stmt = $pdo->query("SELECT token FROM token_api ORDER BY id DESC LIMIT 1");
        $ultimo_token = $stmt->fetch();
        
        $numero_consecutivo = 1;
        if ($ultimo_token) {
            // Extraer el número consecutivo del último token
            if (preg_match('/_(\d+)$/', $ultimo_token['token'], $matches)) {
                $numero_consecutivo = (int)$matches[1] + 1;
            }
        }
        
        // Generar token único automáticamente
        $token_base = bin2hex(random_bytes(32)); // Genera 64 caracteres hexadecimales
        $token_completo = $token_base . '_' . $numero_consecutivo;
        
        $stmt = $pdo->prepare("INSERT INTO token_api (token, descripcion) VALUES (?, ?)");
        $stmt->execute([$token_completo, $descripcion]);
        
        header('Location: tokens.php?mensaje=Token generado y agregado correctamente');
        exit();
    } catch (PDOException $e) {
        $mensaje = 'Error al generar el token: ' . $e->getMessage();
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title mb-0">Generar Nuevo Token</h2>
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
                    
                    <div class="alert alert-info">
                        <strong>Información:</strong> El token se generará automáticamente con un valor único y un número consecutivo al final.
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

<?php include 'includes/footer.php'; ?>