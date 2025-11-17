<?php
// config/database.php
class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    // Configuración para desarrollo/producción
    private $api_base_url;
    private $api_endpoint;

    public function __construct() {
        // Detectar entorno
        if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['HTTP_HOST'] === '127.0.0.1') {
            // Configuración desarrollo
            $this->host = 'localhost';
            $this->db_name = 'dpwebcom_mototaxis_huanta';
            $this->username = 'root';
            $this->password = '';
            $this->api_base_url = 'http://localhost/mototaxis-api/';
            $this->api_endpoint = 'http://localhost/mototaxis-api/api.php';
        } else {
            // Configuración producción
            $this->host = 'localhost';
            $this->db_name = 'dpwebcom_mototaxis_huanta';
            $this->username = 'dpwebcom_mototaxis_huanta';
            $this->password = '47530217vegeta';
            $this->api_base_url = 'https://mototaxis-huanta.dpweb2024.com/';
            $this->api_endpoint = 'https://mototaxis-huanta.dpweb2024.com/api.php';
        }

        $this->getConnection();
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            global $pdo;
            $pdo = $this->conn;
            
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
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

    public function consumeExternalAPI($params = []) {
        $url = $this->api_endpoint;
        
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_USERAGENT => 'MotoTaxis-API-Consumer/1.0'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log("Error cURL API externa: " . $error);
            return false;
        }

        if ($http_code === 200 && !empty($response)) {
            return json_decode($response, true);
        }

        return false;
    }
}

// Crear instancia global
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    error_log("Error inicializando base de datos: " . $e->getMessage());
    $pdo = null;
}
?>