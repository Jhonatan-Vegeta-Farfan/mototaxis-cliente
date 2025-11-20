<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit();
}

$nombreUsuario = $_SESSION['usuario_nombre'] ?? '';

// Inicializar conexión a BD si no existe
if (!isset($pdo)) {
    $config_paths = [
        __DIR__ . '/config/database.php',
        __DIR__ . '/../config/database.php',
        'config/database.php',
        '../config/database.php'
    ];
    
    $database_loaded = false;
    foreach ($config_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            $database_loaded = true;
            break;
        }
    }
    
    if (!$database_loaded) {
        error_log("No se pudo encontrar config/database.php");
        $pdo = null;
    }
}

// Obtener información de tokens activos para mostrar en el dashboard
$tokensActivos = 0;
$tokenInfo = '';

try {
    if (isset($pdo)) {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM tokens_api WHERE estado = 1");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $tokensActivos = $result['total'];
        
        if ($tokensActivos > 0) {
            $stmt = $pdo->query("SELECT token, descripcion FROM tokens_api WHERE estado = 1 ORDER BY id DESC LIMIT 3");
            $tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $tokenInfo = '<div class="alert alert-success mb-3">';
            $tokenInfo .= '<h6><i class="fas fa-key me-2"></i>Sistema de Autenticación Automática Activo</h6>';
            $tokenInfo .= '<p class="mb-1">Se encontraron <strong>' . $tokensActivos . ' tokens activos</strong> en la base de datos.</p>';
            $tokenInfo .= '<p class="mb-0">El sistema utilizará automáticamente estos tokens para las consultas API.</p>';
            $tokenInfo .= '</div>';
        } else {
            $tokenInfo = '<div class="alert alert-warning mb-3">';
            $tokenInfo .= '<h6><i class="fas fa-exclamation-triangle me-2"></i>No hay Tokens Activos</h6>';
            $tokenInfo .= '<p class="mb-0">No se encontraron tokens activos en la base de datos. El sistema funcionará con datos de prueba.</p>';
            $tokenInfo .= '</div>';
        }
    }
} catch (Exception $e) {
    $tokenInfo = '<div class="alert alert-danger mb-3">';
    $tokenInfo .= '<h6><i class="fas fa-exclamation-circle me-2"></i>Error Verificando Tokens</h6>';
    $tokenInfo .= '<p class="mb-0">No se pudieron verificar los tokens activos: ' . htmlspecialchars($e->getMessage()) . '</p>';
    $tokenInfo .= '</div>';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Mototaxis Huanta - Cliente API</title>
    
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

        .navbar-main {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-bottom: 3px solid var(--accent-blue);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }

        .card {
            border: 1px solid var(--border-gray);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            background: white;
        }

        .card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            border-bottom: none;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border: none;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-blue), var(--primary-blue));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(30, 60, 114, 0.3);
        }

        .btn-success {
            background: var(--success-green);
            border: none;
        }

        .btn-success:hover {
            background: #157347;
            transform: translateY(-1px);
        }

        .btn-warning {
            background: var(--warning-orange);
            border: none;
        }

        .btn-warning:hover {
            background: #e06c1c;
            transform: translateY(-1px);
        }

        .user-info {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 8px 12px;
            margin-right: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-info .username {
            font-weight: 500;
            color: white;
        }

        .user-info .badge {
            font-size: 0.7em;
        }

        .stat-card {
            text-align: center;
            padding: 1.5rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: 600;
        }

        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-blue);
        }

        .quick-action-card {
            cursor: pointer;
            height: 100%;
        }

        .quick-action-card:hover .feature-icon {
            transform: scale(1.1);
            transition: transform 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-header {
                padding: 1rem;
            }
            
            .btn {
                padding: 0.65rem 1.25rem;
            }
            
            .container {
                padding: 0 15px;
            }

            .user-info {
                margin: 5px 0;
                text-align: center;
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar Principal -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-main">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-motorcycle me-2"></i>
                Mototaxis Huanta - Cliente API
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <div class="user-info">
                            <span class="username">
                                <i class="fas fa-user me-1"></i>
                                <?php echo htmlspecialchars($nombreUsuario); ?>
                            </span>
                            <span class="badge bg-success ms-2">Conectado</span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger btn-sm text-white ms-2" href="logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <!-- Header -->
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-primary mb-3">
                <i class="fas fa-motorcycle me-3"></i>
                Sistema de Mototaxis Huanta
            </h1>
            <p class="lead text-muted mb-4">
                Plataforma de gestión y consulta de información de mototaxis
            </p>
        </div>

        <!-- Sistema de Autenticación Automática -->
        <?php echo $tokenInfo; ?>

        <!-- Estadísticas Rápidas -->
        <div class="row mb-5">
            <div class="col-md-3 col-6 mb-4">
                <div class="card stat-card">
                    <div class="stat-number"><?php echo $tokensActivos; ?></div>
                    <div class="stat-label">Tokens Activos</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card stat-card">
                    <div class="stat-number text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-label">Sistema Automático</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card stat-card">
                    <div class="stat-number text-info">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-label">BD Local</div>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-4">
                <div class="card stat-card">
                    <div class="stat-number text-warning">
                        <i class="fas fa-cloud"></i>
                    </div>
                    <div class="stat-label">API Externa</div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="row mb-5">
            <div class="col-md-4 mb-4">
                <div class="card quick-action-card" onclick="window.location.href='api.php'">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h5 class="card-title">Consultar API</h5>
                        <p class="card-text text-muted">
                            Accede a la interfaz de consulta de mototaxis con autenticación automática
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Automático</span>
                            <span class="badge bg-info ms-1">Sin Token</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card quick-action-card" onclick="window.location.href='api.php?action=listar'">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h5 class="card-title">Listar Mototaxis</h5>
                        <p class="card-text text-muted">
                            Ver lista completa de mototaxis con paginación automática
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-primary">JSON API</span>
                            <span class="badge bg-success ms-1">Automático</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card quick-action-card" onclick="showSearchModal()">
                    <div class="card-body text-center">
                        <div class="feature-icon">
                            <i class="fas fa-motorcycle"></i>
                        </div>
                        <h5 class="card-title">Búsqueda Rápida</h5>
                        <p class="card-text text-muted">
                            Buscar mototaxi específico por número asignado
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-warning">Directo</span>
                            <span class="badge bg-success ms-1">Automático</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del Sistema -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-key me-2"></i>Nuevo Sistema de Autenticación
                                </h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Autenticación Automática:</strong> El sistema detecta tokens activos automáticamente
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Sin Configuración Manual:</strong> No requiere ingreso de token por parte del usuario
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Múltiples Tokens:</strong> Soporta múltiples tokens activos simultáneamente
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <strong>Fallback Inteligente:</strong> Usa BD local o datos de prueba si no hay tokens
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-rocket me-2"></i>Endpoints Disponibles
                                </h6>
                                <div class="mb-3">
                                    <code class="bg-light p-2 rounded d-block">
                                        /api.php?action=buscar&numero=MT-001
                                    </code>
                                    <small class="text-muted">Búsqueda automática de mototaxi</small>
                                </div>
                                <div class="mb-3">
                                    <code class="bg-light p-2 rounded d-block">
                                        /api.php?action=listar&pagina=1
                                    </code>
                                    <small class="text-muted">Listado paginado automático</small>
                                </div>
                                <div>
                                    <code class="bg-light p-2 rounded d-block">
                                        /api.php?action=tokens_activos
                                    </code>
                                    <small class="text-muted">Ver tokens activos disponibles</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda Rápida -->
    <div class="modal fade" id="searchModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-search me-2"></i>Búsqueda Rápida de Mototaxi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="quickSearchNumber" class="form-label">Número de Mototaxi</label>
                        <input type="text" class="form-control" id="quickSearchNumber" 
                               placeholder="Ej: MT-001, A-123, etc.">
                        <div class="form-text">
                            Ingrese el número asignado del mototaxi que desea buscar
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Esta búsqueda utiliza el sistema de autenticación automática.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="performQuickSearch()">
                        <i class="fas fa-search me-2"></i>Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="text-light mb-2">
                        <i class="fas fa-motorcycle me-2"></i>
                        Sistema de Mototaxis Huanta - Cliente API
                    </h5>
                    <p class="text-light opacity-75 mb-0">
                        Versión 2.0 - Autenticación Automática
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-1 text-light opacity-75">
                        &copy; 2025 VegetA CoudinG
                    </p>
                    <p class="mb-0 text-light opacity-75">
                        Todos los derechos reservados
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar modal de búsqueda rápida
        function showSearchModal() {
            const modal = new bootstrap.Modal(document.getElementById('searchModal'));
            modal.show();
            
            // Enfocar el input cuando se muestra el modal
            setTimeout(() => {
                document.getElementById('quickSearchNumber').focus();
            }, 500);
        }

        // Realizar búsqueda rápida
        function performQuickSearch() {
            const numero = document.getElementById('quickSearchNumber').value.trim();
            
            if (!numero) {
                alert('Por favor ingrese un número de mototaxi');
                return;
            }

            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('searchModal'));
            modal.hide();

            // Redirigir a la página de API con la búsqueda
            window.location.href = `api.php?action=buscar&numero=${encodeURIComponent(numero)}`;
        }

        // Permitir búsqueda con Enter en el modal
        document.getElementById('quickSearchNumber')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performQuickSearch();
            }
        });

        // Efectos visuales para las tarjetas de acción rápida
        document.addEventListener('DOMContentLoaded', function() {
            const actionCards = document.querySelectorAll('.quick-action-card');
            
            actionCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Mostrar notificación de bienvenida al nuevo sistema
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('welcome') === 'auto-auth') {
                showNotification('Sistema de autenticación automática activado correctamente', 'success');
            }
        });

        // Función para mostrar notificaciones
        function showNotification(message, type) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-info';
            const icon = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
            
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 1050; min-width: 300px;';
            notification.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 5000);
        }

        // Verificar estado del sistema al cargar
        document.addEventListener('DOMContentLoaded', function() {
            // Simular verificación de estado del sistema
            setTimeout(() => {
                const tokenCount = <?php echo $tokensActivos; ?>;
                if (tokenCount > 0) {
                    console.log('✅ Sistema de autenticación automática activo');
                } else {
                    console.log('⚠️ Sistema funcionando en modo respaldo');
                }
            }, 1000);
        });
    </script>
</body>
</html>