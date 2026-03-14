<?php

class PQR {
	private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

	public function verPQR() {
        $sql = "SELECT * FROM PQRs";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $PQR = [];
        while ($row = $result->fetch_assoc()) {
            $PQR[] = $row;
        }

        return $PQR;
    }

    public function obtenerPQRsNoLeidos($idEstadoPQR) {
        $sql = "SELECT * FROM PQRs WHERE idEstadoPQR = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $idEstadoPQR);
        $stmt->execute();
        $result = $stmt->get_result();

        $PQR = [];
        while ($row = $result->fetch_assoc()) {
            $PQR[] = $row;
        }

        $stmt->close();

        return $PQR;
    }

    public function verPQRFiltrado($fecha) {
        $sql = "SELECT * FROM PQRs WHERE fecha = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('s', $fecha);
        $stmt->execute();
        $result = $stmt->get_result();

        $PQR = [];
        while ($row = $result->fetch_assoc()) {
            $PQR[] = $row;
        }

        $stmt->close();

        return $PQR;
    }

    public function visto() {
        $sql = "UPDATE PQRs SET idEstadoPQR = 1";
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        if ($stmt->execute()) {
            return ['message' => '¡Compra Cancelada con Exito!'];
        } else {
            return ['message' => 'Error al actualizar los datos', 'error' => $stmt->error];
        }
    }
}
?>