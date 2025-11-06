<?php
require_once 'includes/auth_check.php';
require_once 'config/database.php';

// Obtener estadísticas
$stmt = $pdo->query("SELECT COUNT(*) as total_tokens FROM token_api");
$total_tokens = $stmt->fetch()['total_tokens'];

$stmt = $pdo->query("SELECT COUNT(*) as total_usuarios FROM usuarios");
$total_usuarios = $stmt->fetch()['total_usuarios'];
?>

<?php include 'includes/header.php'; ?>

<div class="row">
    <div class="col-md-12">
        <h1>Dashboard</h1>
        <p class="lead">Bienvenido, <?php echo $_SESSION['usuario_nombre']; ?></p>
    </div>
</div>

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

<?php include 'includes/footer.php'; ?>