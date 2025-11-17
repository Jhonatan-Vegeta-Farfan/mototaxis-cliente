<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Pública - Sistema de Mototaxis Huanta</title>
    
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

        .navbar-public {
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

        .form-control {
            border-radius: 8px;
            border: 2px solid var(--border-gray);
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(30, 60, 114, 0.15);
        }

        .api-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 500;
        }

        .api-status.online {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .api-status.offline {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-dot.online {
            background-color: #198754;
        }

        .status-dot.offline {
            background-color: #dc3545;
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
        }
    </style>
</head>
<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-public">
        <div class="container">
            <a class="navbar-brand" href="api.php">
                <i class="fas fa-motorcycle me-2"></i>
                Mototaxis Huanta - API Pública
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item">
                    <span class="nav-link" id="apiStatus">
                        <i class="fas fa-spinner fa-spin me-1"></i>
                        Verificando API...
                    </span>
                </div>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header Information -->
                <div class="text-center mb-5">
                    <h1 class="display-4 fw-bold text-primary mb-3">API de Mototaxis Huanta</h1>
                    <p class="lead text-muted mb-4">
                        Sistema integrado con la API externa de 
                        <a href="https://mototaxis-huanta.dpweb2024.com/" target="_blank" class="text-decoration-none">
                            mototaxis-huanta.dpweb2024.com
                        </a>
                    </p>
                    
                    <!-- API Status Badge -->
                    <div class="d-flex justify-content-center align-items-center gap-3 mb-4">
                        <div id="apiStatusBadge" class="api-status">
                            <span class="status-dot"></span>
                            <span>Verificando estado...</span>
                        </div>
                    </div>
                </div>

                <!-- Main Interface -->
                <div class="row">
                    <div class="col-md-12">
                        <!-- Card de Autenticación -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="fas fa-key me-2"></i>Autenticación API</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="apiToken" class="form-label">Token de Acceso</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="apiToken" 
                                               placeholder="Ingrese su token de acceso API">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleToken">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">
                                        Token requerido para acceder a los servicios de la API
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100" id="validateToken">
                                    <i class="fas fa-check-circle me-2"></i>Validar Token
                                </button>
                            </div>
                        </div>

                        <!-- Card de Búsqueda (inicialmente oculta) -->
                        <div class="card mb-4 d-none" id="searchCard">
                            <div class="card-header">
                                <h4 class="mb-0"><i class="fas fa-search me-2"></i>Buscar Mototaxi</h4>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="numeroAsignado" class="form-label">Número de la Mototaxi</label>
                                    <input type="text" class="form-control" id="numeroAsignado" 
                                           placeholder="Ej: MT-001, A-123, etc.">
                                    <div class="form-text">
                                        Ingrese el número de la mototaxi para buscar en la base de datos externa
                                    </div>
                                </div>
                                <button class="btn btn-primary w-100" id="searchMototaxi">
                                    <i class="fas fa-motorcycle me-2"></i>Buscar Mototaxi
                                </button>
                            </div>
                        </div>

                        <!-- Resultados (inicialmente oculta) -->
                        <div class="card d-none" id="resultsCard">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Mototaxi</h4>
                                <button class="btn btn-sm btn-outline-secondary" id="clearSearch">
                                    <i class="fas fa-times me-1"></i>Nueva Búsqueda
                                </button>
                            </div>
                            <div class="card-body">
                                <div id="loading" class="text-center d-none">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Buscando información del mototaxi...</p>
                                </div>
                                <div id="resultsContent"></div>
                                
                                <!-- Acordeón para JSON (inicialmente colapsado) -->
                                <div class="mt-4" id="jsonSection">
                                    <div class="accordion" id="jsonAccordion">
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                            </h2>
                                            <div id="jsonCollapse" class="accordion-collapse collapse" 
                                                 data-bs-parent="#jsonAccordion">
                                                <div class="accordion-body p-0">
                                                    <pre id="jsonResponse" class="bg-dark text-light p-3 mb-0 rounded-bottom" 
                                                         style="font-size: 0.8rem; max-height: 300px; overflow-y: auto;"></pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Documentación de la API -->
                <div class="row mt-5">
                    <div class="col-md-12">
                        <div class="card">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const apiTokenInput = document.getElementById('apiToken');
            const toggleTokenBtn = document.getElementById('toggleToken');
            const validateTokenBtn = document.getElementById('validateToken');
            const searchCard = document.getElementById('searchCard');
            const resultsCard = document.getElementById('resultsCard');
            const numeroAsignadoInput = document.getElementById('numeroAsignado');
            const searchMototaxiBtn = document.getElementById('searchMototaxi');
            const clearSearchBtn = document.getElementById('clearSearch');
            const loadingElement = document.getElementById('loading');
            const resultsContent = document.getElementById('resultsContent');
            const jsonSection = document.getElementById('jsonSection');
            const jsonResponse = document.getElementById('jsonResponse');
            const apiStatusBadge = document.getElementById('apiStatusBadge');

            // Verificar estado de la API externa al cargar la página
            verificarApiExterna();

            // Ocultar sección JSON inicialmente
            jsonSection.classList.add('d-none');

            // Toggle visibilidad del token
            toggleTokenBtn.addEventListener('click', function() {
                const type = apiTokenInput.getAttribute('type') === 'password' ? 'text' : 'password';
                apiTokenInput.setAttribute('type', type);
                toggleTokenBtn.innerHTML = type === 'password' ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
            });

            // Validar token
            validateTokenBtn.addEventListener('click', function() {
                const token = apiTokenInput.value.trim();
                
                if (!token) {
                    showAlert('Por favor ingrese un token', 'error');
                    return;
                }

                validateTokenBtn.disabled = true;
                validateTokenBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Validando...';

                fetch(`api.php?action=validar_token&token=${encodeURIComponent(token)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showAlert('✅ Token válido', 'success');
                            searchCard.classList.remove('d-none');
                            numeroAsignadoInput.focus();
                            localStorage.setItem('apiToken', token);
                        } else {
                            showAlert('❌ ' + data.message, 'error');
                            searchCard.classList.add('d-none');
                            resultsCard.classList.add('d-none');
                        }
                    })
                    .catch(error => {
                        showAlert('Error de conexión: ' + error.message, 'error');
                    })
                    .finally(() => {
                        validateTokenBtn.disabled = false;
                        validateTokenBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Validar Token';
                    });
            });

            // Buscar mototaxi
            searchMototaxiBtn.addEventListener('click', function() {
                const token = apiTokenInput.value.trim();
                const numero = numeroAsignadoInput.value.trim();
                
                if (!token) {
                    showAlert('Token no válido', 'error');
                    return;
                }
                
                if (!numero) {
                    showAlert('Por favor ingrese un número asignado', 'error');
                    return;
                }

                searchMototaxi(token, numero);
            });

            // Limpiar búsqueda
            clearSearchBtn.addEventListener('click', function() {
                resultsCard.classList.add('d-none');
                jsonSection.classList.add('d-none');
                numeroAsignadoInput.value = '';
                numeroAsignadoInput.focus();
            });

            // Función para buscar mototaxi
            function searchMototaxi(token, numero) {
                searchMototaxiBtn.disabled = true;
                searchMototaxiBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Buscando...';
                loadingElement.classList.remove('d-none');
                resultsCard.classList.remove('d-none');
                resultsContent.innerHTML = '';
                jsonSection.classList.add('d-none');

                fetch(`api.php?action=buscar&numero=${encodeURIComponent(numero)}&token=${encodeURIComponent(token)}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingElement.classList.add('d-none');
                        
                        if (data.success) {
                            displayMototaxiInfo(data.data, data.metadata.fuente);
                            // Mostrar JSON en acordeón colapsado
                            jsonResponse.textContent = JSON.stringify(data, null, 2);
                            jsonSection.classList.remove('d-none');
                            
                            // Colapsar el acordeón por defecto
                            const jsonCollapse = new bootstrap.Collapse(document.getElementById('jsonCollapse'), {
                                toggle: false
                            });
                        } else {
                            resultsContent.innerHTML = `
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    ${data.message}
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        loadingElement.classList.add('d-none');
                        resultsContent.innerHTML = `
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Error de conexión: ${error.message}
                            </div>
                        `;
                    })
                    .finally(() => {
                        searchMototaxiBtn.disabled = false;
                        searchMototaxiBtn.innerHTML = '<i class="fas fa-motorcycle me-2"></i>Buscar Mototaxi';
                    });
            }

            // Mostrar información del mototaxi
            function displayMototaxiInfo(mototaxi, fuente) {
                const fuenteBadge = fuente === 'API_EXTERNA' ? 
                    '<span class="badge bg-success">API Externa</span>' : 
                    '<span class="badge bg-info">Base Local</span>';

                const infoHtml = `
                    <div class="mb-3">
                        ${fuenteBadge}
                        <small class="text-muted ms-2">Fuente: ${fuente}</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-user me-2"></i>Información Personal
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width: 40%;">Número Asignado:</th>
                                    <td><strong class="text-success">${mototaxi.numero_asignado}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Nombre Completo:</th>
                                    <td>${mototaxi.nombre_completo}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">DNI:</th>
                                    <td><span class="badge bg-info">${mototaxi.dni}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Dirección:</th>
                                    <td>${mototaxi.direccion || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-motorcycle me-2"></i>Información del Vehículo
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width: 40%;">Placa de Rodaje:</th>
                                    <td><span class="badge bg-secondary">${mototaxi.placa_rodaje}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Año Fabricación:</th>
                                    <td>${mototaxi.anio_fabricacion || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Marca:</th>
                                    <td>${mototaxi.marca || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Color:</th>
                                    <td>
                                        <span class="badge" style="background-color: ${getColorValue(mototaxi.color)}; color: white;">
                                            ${mototaxi.color || 'No especificado'}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-cogs me-2"></i>Especificaciones Técnicas
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width: 40%;">Número Motor:</th>
                                    <td>${mototaxi.numero_motor || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Tipo Motor:</th>
                                    <td>${mototaxi.tipo_motor || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Serie:</th>
                                    <td>${mototaxi.serie || '<span class="text-muted">No especificado</span>'}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary border-bottom pb-2 mb-3">
                                <i class="fas fa-building me-2"></i>Información Adicional
                            </h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="text-muted" style="width: 40%;">Fecha Registro:</th>
                                    <td><span class="badge bg-dark">${mototaxi.fecha_registro}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Empresa:</th>
                                    <td><strong class="text-primary">${mototaxi.empresa.razon_social || '<span class="text-muted">No asignada</span>'}</strong></td>
                                </tr>
                                <tr>
                                    <th class="text-muted">RUC Empresa:</th>
                                    <td>${mototaxi.empresa.ruc || '<span class="text-muted">No disponible</span>'}</td>
                                </tr>
                                <tr>
                                    <th class="text-muted">Estado:</th>
                                    <td><span class="badge bg-success">${mototaxi.estado_registro || 'ACTIVO'}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                `;
                
                resultsContent.innerHTML = infoHtml;
            }

            // Función para verificar API externa
            function verificarApiExterna() {
                fetch('api.php?action=verificar_api')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const disponible = data.data.api_externa_disponible;
                            if (disponible) {
                                apiStatusBadge.innerHTML = `
                                    <span class="status-dot online"></span>
                                    <span>API Externa: En Línea</span>
                                `;
                                apiStatusBadge.className = 'api-status online';
                            } else {
                                apiStatusBadge.innerHTML = `
                                    <span class="status-dot offline"></span>
                                    <span>API Externa: Sin Conexión</span>
                                `;
                                apiStatusBadge.className = 'api-status offline';
                            }
                        }
                    })
                    .catch(error => {
                        apiStatusBadge.innerHTML = `
                            <span class="status-dot offline"></span>
                            <span>API Externa: Error</span>
                        `;
                        apiStatusBadge.className = 'api-status offline';
                    });
            }

            // Función auxiliar para colores
            function getColorValue(color) {
                if (!color) return '#6c757d';
                const colors = {
                    'rojo': '#dc3545',
                    'azul': '#0d6efd', 
                    'verde': '#198754',
                    'amarillo': '#ffc107',
                    'negro': '#212529',
                    'blanco': '#f8f9fa',
                    'gris': '#6c757d',
                    'naranja': '#fd7e14',
                    'morado': '#6f42c1'
                };
                return colors[color.toLowerCase()] || '#6c757d';
            }

            // Mostrar alertas
            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
                
                const alertHtml = `
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        <i class="fas ${icon} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Insertar al inicio del container
                document.querySelector('.container').insertAdjacentHTML('afterbegin', alertHtml);
                
                // Auto-remover después de 5 segundos
                setTimeout(() => {
                    const alert = document.querySelector('.alert');
                    if (alert) {
                        alert.remove();
                    }
                }, 5000);
            }

            // Cargar token guardado si existe
            const savedToken = localStorage.getItem('apiToken');
            if (savedToken) {
                apiTokenInput.value = savedToken;
            }

            // Permitir búsqueda con Enter
            numeroAsignadoInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchMototaxiBtn.click();
                }
            });

            // Permitir validación con Enter en token
            apiTokenInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    validateTokenBtn.click();
                }
            });
        });
    </script>
</body>
</html>