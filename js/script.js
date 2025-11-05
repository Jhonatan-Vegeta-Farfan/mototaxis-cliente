// js/script.js
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el usuario está logueado
    checkAuthentication();

    // Elementos del DOM
    const tokenForm = document.getElementById('token-form');
    const tokenInput = document.getElementById('token-input');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const refreshBtn = document.getElementById('refresh-btn');
    const tokensList = document.getElementById('tokens-list');
    const tokenCount = document.getElementById('token-count');
    const formTitle = document.getElementById('form-title');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const confirmDeleteBtn = document.getElementById('confirm-delete');
    const tokenToDeleteElement = document.getElementById('token-to-delete');
    const logoutBtn = document.getElementById('logout-btn');
    const userName = document.getElementById('user-name');
    const userRole = document.getElementById('user-role');
    const loginTime = document.getElementById('login-time');
    
    // Variables de estado
    let isEditing = false;
    let currentEditToken = '';
    let tokenToDelete = '';

    // Cargar información del usuario
    loadUserInfo();

    // Cargar tokens al iniciar
    loadTokens();

    // Event Listeners
    tokenForm.addEventListener('submit', handleFormSubmit);
    cancelBtn.addEventListener('click', cancelEdit);
    refreshBtn.addEventListener('click', loadTokens);
    confirmDeleteBtn.addEventListener('click', confirmDelete);
    logoutBtn.addEventListener('click', handleLogout);

    // Función para verificar autenticación
    function checkAuthentication() {
        const userData = sessionStorage.getItem('userData');
        if (!userData) {
            window.location.href = 'index.html';
            return;
        }
    }

    // Función para cargar información del usuario
    function loadUserInfo() {
        const userData = JSON.parse(sessionStorage.getItem('userData'));
        if (userData) {
            userName.textContent = userData.name;
            userRole.textContent = userData.role;
            loginTime.textContent = `Conectado desde: ${formatDate(userData.login_time)}`;
        }
    }

    // Función para manejar logout
    function handleLogout(e) {
        e.preventDefault();
        
        if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
            sessionStorage.removeItem('userData');
            window.location.href = 'index.html';
        }
    }

    // Función para formatear fecha
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('es-ES');
    }

    // Función para manejar el envío del formulario
    function handleFormSubmit(e) {
        e.preventDefault();
        
        const token = tokenInput.value.trim();
        
        if (!token) {
            showAlert('El token no puede estar vacío', 'warning');
            return;
        }
        
        if (isEditing) {
            updateToken(currentEditToken, token);
        } else {
            createToken(token);
        }
    }

    // Función para crear un token
    function createToken(token) {
        setFormLoadingState(true);
        
        fetch('api/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                tokenForm.reset();
                loadTokens();
                cancelEdit();
            } else {
                showAlert(data.message, 'danger');
            }
            setFormLoadingState(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión al crear el token', 'danger');
            setFormLoadingState(false);
        });
    }

    // Función para cargar todos los tokens
    function loadTokens() {
        setTokensLoadingState(true);
        
        fetch('api/read.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayTokens(data.data.tokens);
                tokenCount.textContent = `${data.data.total} token${data.data.total !== 1 ? 's' : ''}`;
            } else {
                showAlert(data.message, 'danger');
                tokensList.innerHTML = '<div class="text-center p-4 text-danger">Error al cargar los tokens</div>';
            }
            setTokensLoadingState(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión al cargar los tokens', 'danger');
            tokensList.innerHTML = '<div class="text-center p-4 text-danger">Error de conexión</div>';
            setTokensLoadingState(false);
        });
    }

    // Función para mostrar los tokens en la lista
    function displayTokens(tokens) {
        if (tokens.length === 0) {
            tokensList.innerHTML = `
                <div class="text-center p-4 text-muted">
                    <i class="fas fa-inbox fa-3x mb-3"></i>
                    <p>No hay tokens registrados</p>
                    <small class="text-muted">Usa el formulario para agregar tu primer token</small>
                </div>
            `;
            return;
        }

        let html = '';
        tokens.forEach(tokenObj => {
            const token = tokenObj.token;
            // Acortar token largo para mejor visualización
            const displayToken = token.length > 50 ? token.substring(0, 47) + '...' : token;
            
            html += `
                <div class="token-item">
                    <div class="token-value">
                        <code class="token-text" title="${token}">${displayToken}</code>
                        ${token.length > 50 ? '<small class="text-muted ms-2">(token largo)</small>' : ''}
                    </div>
                    <div class="token-actions">
                        <button class="btn btn-sm btn-outline-primary edit-token" data-token="${token}" title="Editar token">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger delete-token" data-token="${token}" title="Eliminar token">
                            <i class="fas fa-trash"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary copy-token" data-token="${token}" title="Copiar token">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        tokensList.innerHTML = html;
        
        // Agregar event listeners a los botones
        document.querySelectorAll('.edit-token').forEach(button => {
            button.addEventListener('click', function() {
                const token = this.getAttribute('data-token');
                editToken(token);
            });
        });
        
        document.querySelectorAll('.delete-token').forEach(button => {
            button.addEventListener('click', function() {
                const token = this.getAttribute('data-token');
                showDeleteModal(token);
            });
        });

        document.querySelectorAll('.copy-token').forEach(button => {
            button.addEventListener('click', function() {
                const token = this.getAttribute('data-token');
                copyToClipboard(token);
            });
        });
    }

    // Función para copiar token al portapapeles
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showAlert('Token copiado al portapapeles', 'success');
        }).catch(err => {
            console.error('Error al copiar: ', err);
            showAlert('Error al copiar el token', 'danger');
        });
    }

    // Función para editar un token
    function editToken(token) {
        isEditing = true;
        currentEditToken = token;
        tokenInput.value = token;
        formTitle.textContent = 'Editar Token';
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Actualizar Token';
        cancelBtn.style.display = 'inline-block';
        tokenInput.focus();
        
        // Scroll al formulario en móviles
        if (window.innerWidth < 768) {
            tokenForm.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Función para cancelar la edición
    function cancelEdit() {
        isEditing = false;
        currentEditToken = '';
        tokenForm.reset();
        formTitle.textContent = 'Agregar Nuevo Token';
        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Guardar Token';
        cancelBtn.style.display = 'none';
    }

    // Función para actualizar un token
    function updateToken(oldToken, newToken) {
        setFormLoadingState(true);
        
        fetch('api/update.php', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                old_token: oldToken, 
                new_token: newToken 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                cancelEdit();
                loadTokens();
            } else {
                showAlert(data.message, 'danger');
            }
            setFormLoadingState(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión al actualizar el token', 'danger');
            setFormLoadingState(false);
        });
    }

    // Función para mostrar el modal de eliminación
    function showDeleteModal(token) {
        tokenToDelete = token;
        tokenToDeleteElement.textContent = token.length > 30 ? token.substring(0, 30) + '...' : token;
        tokenToDeleteElement.setAttribute('title', token);
        deleteModal.show();
    }

    // Función para confirmar la eliminación
    function confirmDelete() {
        if (!tokenToDelete) return;
        
        setDeleteLoadingState(true);
        
        fetch('api/delete.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ token: tokenToDelete })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert(data.message, 'success');
                loadTokens();
            } else {
                showAlert(data.message, 'danger');
            }
            deleteModal.hide();
            tokenToDelete = '';
            setDeleteLoadingState(false);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión al eliminar el token', 'danger');
            deleteModal.hide();
            tokenToDelete = '';
            setDeleteLoadingState(false);
        });
    }

    // Función para mostrar estado de carga del formulario
    function setFormLoadingState(loading) {
        const submitText = isEditing ? 'Actualizar Token' : 'Guardar Token';
        if (loading) {
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Procesando...';
            submitBtn.disabled = true;
        } else {
            submitBtn.innerHTML = `<i class="fas fa-save me-1"></i> ${submitText}`;
            submitBtn.disabled = false;
        }
    }

    // Función para mostrar estado de carga de tokens
    function setTokensLoadingState(loading) {
        if (loading) {
            refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';
            refreshBtn.disabled = true;
        } else {
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Actualizar';
            refreshBtn.disabled = false;
        }
    }

    // Función para mostrar estado de carga de eliminación
    function setDeleteLoadingState(loading) {
        if (loading) {
            confirmDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Eliminando...';
            confirmDeleteBtn.disabled = true;
        } else {
            confirmDeleteBtn.innerHTML = 'Eliminar';
            confirmDeleteBtn.disabled = false;
        }
    }

    // Función para mostrar alertas
    function showAlert(message, type) {
        const alertContainer = document.getElementById('alert-container');
        
        const alertClass = {
            'success': 'alert-success',
            'danger': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const alertHTML = `
            <div class="alert alert-custom ${alertClass} alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas ${getAlertIcon(type)} me-2"></i>
                    <div>
                        <strong>${getAlertTitle(type)}</strong> ${message}
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        alertContainer.innerHTML = alertHTML;
        
        // Auto-eliminar la alerta después de 5 segundos
        setTimeout(() => {
            const alert = alertContainer.querySelector('.alert');
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        }, 5000);
    }

    // Función para obtener icono de alerta
    function getAlertIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'danger': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        return icons[type] || 'fa-info-circle';
    }

    // Función para obtener título de alerta
    function getAlertTitle(type) {
        const titles = {
            'success': 'Éxito!',
            'danger': 'Error!',
            'warning': 'Advertencia!',
            'info': 'Información!'
        };
        return titles[type] || 'Información!';
    }

    // Prevenir envío con Enter en el campo de token
    tokenInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            tokenForm.dispatchEvent(new Event('submit'));
        }
    });
});