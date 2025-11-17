<?php
class CountRequest {
    private $conn;
    private $table_name = "count_request";

    public $id;
    public $id_token_api;
    public $tipo;
    public $fecha;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        try {
            $query = "INSERT INTO " . $this->table_name . " 
                     SET id_token_api=:id_token_api, tipo=:tipo, fecha=NOW()";
            
            $stmt = $this->conn->prepare($query);
            
            $stmt->bindParam(":id_token_api", $this->id_token_api);
            $stmt->bindParam(":tipo", $this->tipo);
            
            if ($stmt->execute()) {
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error en CountRequest::create(): " . $e->getMessage());
            return false;
        }
    }
}
?>