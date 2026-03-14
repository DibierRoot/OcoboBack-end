<?php

class Database {
    private $host = "localhost";
    private $user = "Cristian";
    private $pass = "xK9#mP2$vL5@nQ8";
    private $dbname = "OcoboDataBase";
    public $conn;

    // Método para obtener la conexión a la base de datos
    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->dbname);

            if ($this->conn->connect_error) {
                throw new Exception("Conexión fallida: " . $this->conn->connect_error);
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'error']);
        }

        return $this->conn;
    }
}

?>
