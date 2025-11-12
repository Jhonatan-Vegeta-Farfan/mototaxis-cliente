<?php
class Database {
    private $host = 'localhost';
    private $db_name = 'prograp_cliente_api';
    private $username = 'prograp_jhonatan_mototaxis_huanta';
    private $password = '47530217vegeta';
    public $conn;

    private $api_base_url = 'https://mototaxis-huanta.dpweb2024.com/';
    private $api_endpoint = 'https://mototaxis-huanta.dpweb2024.com/api.php';

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log("Conexión PDO exitosa");
        } catch(PDOException $exception) {
            error_log("Error de conexión PDO: " . $exception->getMessage());
            // Crear conexión de respaldo sin base de datos específica
            try {
                $this->conn = new PDO("mysql:host=" . $this->host, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                error_log("Conexión PDO alternativa establecida");
            } catch(PDOException $e) {
                error_log("Error de conexión PDO alternativa: " . $e->getMessage());
                return false;
            }
        }
        return $this->conn;
    }

    public function getApiBaseUrl() {
        return $this->api_base_url;
    }

    public function getApiEndpoint() {
        return $this->api_endpoint;
    }

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
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("Error cURL: " . $curl_error);
            return false;
        }

        if ($http_code === 200 && !empty($response)) {
            return json_decode($response, true);
        }

        error_log("Error HTTP: " . $http_code . " - Respuesta: " . $response);
        return false;
    }
}

// Conexión PDO global para compatibilidad
try {
    $database = new Database();
    $pdo = $database->getConnection();
    
    if (!$pdo) {
        throw new Exception("No se pudo establecer conexión PDO");
    }
} catch (Exception $e) {
    error_log("Error inicializando conexión PDO global: " . $e->getMessage());
    $pdo = null;
}
?>