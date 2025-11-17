<?php
// Clase Database para la API
class Database {
    private $host = 'localhost';
    private $db_name = 'prograp_mototaxis_huanta';
    private $username = 'prograp_mototaxis_huanta';
    private $password = '47530217vegeta';
    public $conn;

    // Configuración para consumir la API externa
    private $api_base_url = 'https://mototaxis-huanta.dpweb2024.com/';
    private $api_endpoint = 'https://mototaxis-huanta.dpweb2024.com/api.php';

    public function __construct() {
        // Intentar conexión automáticamente
        $this->getConnection();
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Crear variable global $pdo para compatibilidad
            global $pdo;
            $pdo = $this->conn;
            
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            // No lanzar excepción para permitir modo respaldo
            return false;
        }
        return $this->conn;
    }

    public function getApiBaseUrl() {
        return $this->api_base_url;
    }

    public function getApiEndpoint() {
        return $this->api_endpoint;
    }

    // Método para consumir la API externa
    public function consumeExternalAPI($params = []) {
        $url = $this->api_endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 && !empty($response)) {
            return json_decode($response, true);
        }

        return false;
    }
}

// Crear instancia global para compatibilidad
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    error_log("Error inicializando base de datos: " . $e->getMessage());
    $pdo = null;
}
?>