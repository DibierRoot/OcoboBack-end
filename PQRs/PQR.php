<?php

class PQR {
	private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

	public function publicarMensaje($hoy, $nombre, $correo, $numeroCelular, $mensaje, $idEstadoPQR) {
        $sql = "INSERT INTO PQRs (fecha, nombre, correo, numeroCelular, mensaje, idEstadoPQR) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('sssisi', $hoy, $nombre, $correo, $numeroCelular, $mensaje, $idEstadoPQR);

        if ($stmt->execute()) {
            return ['message' => '¡Mensaje Publicado Correctamente!'];
        } else {
            return ['message' => 'Error al Publicar el Mensaje: ' . $stmt->error];
        }
    }
}
?>