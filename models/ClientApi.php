<?php
class ClientApi {
    private $conn;
    private $table_name = "client_api";

    public $id;
    public $razon_social;
    public $ruc;
    public $telefono;
    public $correo;
    public $fecha_registro;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        try {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt;
        } catch (Exception $e) {
            error_log("Error en ClientApi::read(): " . $e->getMessage());
            return false;
        }
    }
}
?>