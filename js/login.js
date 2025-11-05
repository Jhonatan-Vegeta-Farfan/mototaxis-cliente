// js/login.js
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const loginForm = document.getElementById('login-form');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.getElementById('toggle-password');
    const loginBtn = document.getElementById('login-btn');
    const loginText = document.getElementById('login-text');
    const loginSpinner = document.getElementById('login-spinner');
    const rememberMe = document.getElementById('remember-me');

    // Cargar credenciales guardadas si existen
    loadSavedCredentials();

    // Event Listeners
    loginForm.addEventListener('submit', handleLogin);
    togglePasswordBtn.addEventListener('click', togglePasswordVisibility);

    // Función para manejar el login
    function handleLogin(e) {
        e.preventDefault();
        
        const username = usernameInput.value.trim();
        const password = passwordInput.value;
        
        if (!username || !password) {
            showAlert('Por favor, completa todos los campos', 'warning');
            return;
        }
        
        // Mostrar estado de carga
        setLoadingState(true);
        
        // Enviar credenciales al servidor
        fetch('api/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                username: username, 
                password: password 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Guardar credenciales si está marcado "Recordar"
                if (rememberMe.checked) {
                    saveCredentials(username, password);
                } else {
                    clearSavedCredentials();
                }
                
                // Guardar datos de usuario en sessionStorage
                sessionStorage.setItem('userData', JSON.stringify(data.data));
                
                showAlert('¡Login exitoso! Redirigiendo...', 'success');
                
                // Redirigir al dashboard después de 1 segundo
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else {
                showAlert(data.message, 'danger');
                setLoadingState(false);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión. Intenta nuevamente.', 'danger');
            setLoadingState(false);
        });
    }

    // Función para mostrar/ocultar contraseña
    function togglePasswordVisibility() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        // Cambiar icono
        const icon = togglePasswordBtn.querySelector('i');
        if (type === 'text') {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
            togglePasswordBtn.setAttribute('title', 'Ocultar contraseña');
        } else {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
            togglePasswordBtn.setAttribute('title', 'Mostrar contraseña');
        }
    }

    // Función para guardar credenciales en localStorage
    function saveCredentials(username, password) {
        const credentials = {
            username: username,
            password: password,
            timestamp: new Date().getTime()
        };
        localStorage.setItem('api_credentials', JSON.stringify(credentials));
    }

    // Función para cargar credenciales guardadas
    function loadSavedCredentials() {
        const saved = localStorage.getItem('api_credentials');
        if (saved) {
            try {
                const credentials = JSON.parse(saved);
                usernameInput.value = credentials.username;
                passwordInput.value = credentials.password;
                rememberMe.checked = true;
            } catch (e) {
                console.error('Error al cargar credenciales:', e);
                clearSavedCredentials();
            }
        }
    }

    // Función para limpiar credenciales guardadas
    function clearSavedCredentials() {
        localStorage.removeItem('api_credentials');
    }

    // Función para mostrar estado de carga
    function setLoadingState(loading) {
        if (loading) {
            loginText.textContent = 'Verificando...';
            loginSpinner.classList.remove('d-none');
            loginBtn.disabled = true;
        } else {
            loginText.textContent = 'Iniciar Sesión';
            loginSpinner.classList.add('d-none');
            loginBtn.disabled = false;
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
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <strong>${type === 'success' ? 'Éxito!' : type === 'danger' ? 'Error!' : type === 'warning' ? 'Advertencia!' : 'Información!'}</strong> ${message}
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

    // Prevenir envío con Enter en campos individuales
    [usernameInput, passwordInput].forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this === usernameInput) {
                    passwordInput.focus();
                } else {
                    loginForm.dispatchEvent(new Event('submit'));
                }
            }
        });
    });
});