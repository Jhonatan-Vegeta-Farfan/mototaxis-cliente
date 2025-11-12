<?php
class TokenApi {
    private $conn;
    private $table_name = "token_api";

    public $id;
    public $token;
    public $descripcion;
    public $estado;
    public $fecha_registro;
    public $id_client_api;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByToken($token) {
        try {
            if (!$this->conn) {
                throw new Exception("Conexión a BD no disponible");
            }

            $query = "SELECT * FROM " . $this->table_name . " WHERE token = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $token);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return $row;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en TokenApi::getByToken(): " . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus($id) {
        try {
            if (!$this->conn) {
                throw new Exception("Conexión a BD no disponible");
            }

            // Obtener el estado actual
            $query = "SELECT estado FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $current = $stmt->fetch(PDO::FETCH_ASSOC);
                $nuevo_estado = $current['estado'] ? 0 : 1;
                
                // Actualizar estado
                $query = "UPDATE " . $this->table_name . " SET estado = ? WHERE id = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $nuevo_estado);
                $stmt->bindParam(2, $id);
                
                if ($stmt->execute()) {
                    return $nuevo_estado;
                }
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en TokenApi::toggleStatus(): " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus($id, $estado) {
        try {
            if (!$this->conn) {
                throw new Exception("Conexión a BD no disponible");
            }

            $query = "UPDATE " . $this->table_name . " SET estado = ? WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $estado);
            $stmt->bindParam(2, $id);
            
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error en TokenApi::updateStatus(): " . $e->getMessage());
            return false;
        }
    }
}
?>