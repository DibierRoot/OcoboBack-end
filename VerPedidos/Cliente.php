<?php

class Cliente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerFechaFacturaSinId() {
        $sql = "SELECT idFactura, metodoPago, fechaCompra, idEstadoFactura, idCliente FROM Facturas WHERE idEstadoFactura = 1";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }

        $stmt->close();

        return $clientes;
    }

    public function obtenerFechaFactura($idCliente) {
        $sql = "SELECT idFactura, metodoPago, fechaCompra, idEstadoFactura FROM Facturas WHERE idEstadoFactura = 1";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();

        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }

        $stmt->close();

        return $clientes;
    }

    public function cancelarCompra($idFactura, $cancelar, $idColaborador) {
        // Actualizamos los datos
        $sql = "UPDATE Facturas SET idEstadoFactura = ?, idColaborador = ? WHERE idFactura = ?";
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
    
        $stmt->bind_param('iii', $cancelar, $idColaborador, $idFactura);
    
        if ($stmt->execute()) {
            return ['message' => '¡Compra Cancelada con Exito!'];
        } else {
            return ['message' => 'Error al actualizar los datos', 'error' => $stmt->error];
        }
    }
}

?>
