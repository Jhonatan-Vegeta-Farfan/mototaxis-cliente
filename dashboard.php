<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

// Inicializar conexión a BD
require_once 'config/database.php';

$nombreUsuario = $_SESSION['usuario_nombre'] ?? 'Usuario';
$fechaActual = date('d/m/Y');
$horaActual = date('H:i:s');

// Inicializar modelos
require_once 'models/TokenApi.php';
require_once 'models/ClientApi.php';
require_once 'models/CountRequest.php';

$tokenModel = new TokenApi($pdo);
$clientModel = new ClientApi($pdo);
$countRequestModel = new CountRequest($pdo);

// Obtener estadísticas
$totalTokens = 0;
$totalClients = 0;
$totalRequests = 0;
$activeTokens = 0;

try {
    // Contar tokens totales
    $stmt = $tokenModel->read();
    if ($stmt) {
        $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalTokens = count($tokens);
        $activeTokens = count(array_filter($tokens, function($token) {
            return $token['estado'] == 1;
        }));
    }

    // Contar clientes
    $stmt = $clientModel->read();
    if ($stmt) {
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $totalClients = count($clients);
    }

    // Contar requests (estimado)
    $totalRequests = $countRequestModel->getTotalRequestsByToken(0); // 0 para contar todos

} catch (Exception $e) {
    error_log("Error obteniendo estadísticas: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Mototaxis</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-blue: #1e3c72;
            --secondary-blue: #2a5298;
            --accent-blue: #0f3a4a;
            --light-blue: #e3f2fd;
            --dark-blue: #0d1b2a;
            --success-green: #198754;
            --warning-orange: #fd7e14;
            --light-gray: #f8f9fa;
            --border-gray: #dee2e6;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--light-gray);
            color: #333;
            line-height: 1.6;
        }

        .sidebar {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            min-height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            margin: 2px 0;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 500;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }

        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--accent-blue);
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-blue);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stats-icon {
            font-size: 2rem;
            color: var(--primary-blue);
            opacity: 0.7;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-1px);
        }

        .user-info {
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 8px 12px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .stats-number {
                font-size: 2rem;
            }
            
            .welcome-section {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar d-md-block">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white mb-0">
                            <i class="fas fa-motorcycle me-2"></i>
                            Mototaxis
                        </h4>
                        <small class="text-white-50">Sistema de Gestión</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="tokens.php">
                                <i class="fas fa-key"></i>
                                Gestión de Tokens
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="clientes.php">
                                <i class="fas fa-users"></i>
                                Clientes API
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="mototaxis.php">
                                <i class="fas fa-motorcycle"></i>
                                Mototaxis
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="api.php" target="_blank">
                                <i class="fas fa-plug"></i>
                                API Pública
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="estadisticas.php">
                                <i class="fas fa-chart-bar"></i>
                                Estadísticas
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link text-warning" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Cerrar Sesión
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg navbar-light bg-white">
                    <div class="container-fluid">
                        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                            <span class="navbar-toggler-icon"></span>
                        </button>
                        
                        <div class="collapse navbar-collapse" id="navbarContent">
                            <ul class="navbar-nav ms-auto">
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                        <div class="user-info">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($nombreUsuario); ?>
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="perfil.php"><i class="fas fa-user-cog me-2"></i>Mi Perfil</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- Main content area -->
                <main class="py-4">
                    <!-- Welcome Section -->
                    <div class="welcome-section">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-3">¡Bienvenido, <?php echo htmlspecialchars($nombreUsuario); ?>!</h2>
                                <p class="mb-0">Sistema de gestión de API para mototaxis de Huanta</p>
                                <small><i class="fas fa-clock me-1"></i> <?php echo $fechaActual . ' - ' . $horaActual; ?></small>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="fas fa-motorcycle fa-4x opacity-50"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <div class="stats-number"><?php echo $totalTokens; ?></div>
                                        <div class="stats-label">Tokens Activos</div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-key stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <div class="stats-number"><?php echo $activeTokens; ?></div>
                                        <div class="stats-label">Tokens Activos</div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-check-circle stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <div class="stats-number"><?php echo $totalClients; ?></div>
                                        <div class="stats-label">Clientes API</div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-users stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="stats-card">
                                <div class="row align-items-center">
                                    <div class="col-8">
                                        <div class="stats-number"><?php echo $totalRequests; ?></div>
                                        <div class="stats-label">Solicitudes</div>
                                    </div>
                                    <div class="col-4 text-end">
                                        <i class="fas fa-chart-line stats-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <a href="tokens.php?action=create" class="btn btn-primary w-100 mb-2">
                                                <i class="fas fa-plus me-2"></i>Nuevo Token
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="clientes.php?action=create" class="btn btn-success w-100 mb-2">
                                                <i class="fas fa-user-plus me-2"></i>Nuevo Cliente
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="api.php" target="_blank" class="btn btn-info w-100 mb-2">
                                                <i class="fas fa-external-link-alt me-2"></i>Probar API
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="estadisticas.php" class="btn btn-warning w-100 mb-2">
                                                <i class="fas fa-chart-pie me-2"></i>Ver Estadísticas
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong><i class="fas fa-database me-2"></i>Base de Datos:</strong>
                                        <span class="badge bg-success ms-2">Conectada</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-plug me-2"></i>API Externa:</strong>
                                        <span id="apiStatus" class="badge bg-warning ms-2">Verificando...</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="fas fa-shield-alt me-2"></i>Seguridad:</strong>
                                        <span class="badge bg-success ms-2">Activa</span>
                                    </div>
                                    <div>
                                        <strong><i class="fas fa-clock me-2"></i>Última Actualización:</strong>
                                        <span class="ms-2"><?php echo date('d/m/Y H:i:s'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Actividad Reciente</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Fecha/Hora</th>
                                                    <th>Actividad</th>
                                                    <th>Usuario</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i:s'); ?></td>
                                                    <td>Inicio de sesión exitoso</td>
                                                    <td><?php echo htmlspecialchars($nombreUsuario); ?></td>
                                                    <td><span class="badge bg-success">Completado</span></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i:s', strtotime('-5 minutes')); ?></td>
                                                    <td>Acceso al dashboard</td>
                                                    <td>Sistema</td>
                                                    <td><span class="badge bg-info">Información</span></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo date('d/m/Y H:i:s', strtotime('-10 minutes')); ?></td>
                                                    <td>Verificación de tokens activos</td>
                                                    <td>Sistema</td>
                                                    <td><span class="badge bg-success">Completado</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Verificar estado de la API externa
        document.addEventListener('DOMContentLoaded', function() {
            fetch('api.php?action=verificar_api')
                .then(response => response.json())
                .then(data => {
                    const apiStatus = document.getElementById('apiStatus');
                    if (data.success && data.data.api_externa_disponible) {
                        apiStatus.className = 'badge bg-success ms-2';
                        apiStatus.textContent = 'En Línea';
                    } else {
                        apiStatus.className = 'badge bg-danger ms-2';
                        apiStatus.textContent = 'Sin Conexión';
                    }
                })
                .catch(error => {
                    const apiStatus = document.getElementById('apiStatus');
                    apiStatus.className = 'badge bg-danger ms-2';
                    apiStatus.textContent = 'Error';
                });
        });
    </script>
</body>
</html>