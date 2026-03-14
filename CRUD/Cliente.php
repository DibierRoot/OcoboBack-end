<?php

class Cliente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarCliente($nombre, $correo, $contrasena) {
        // Verifica si el correo ya existe
        $sql = "SELECT idCliente FROM Cliente WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ['message' => 'El correo electrónico ya está registrado'];
        }

        // Si el correo no existe, procede con el registro
        $contrasenaHasheada = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO Cliente (nombre, correo, contrasena) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('sss', $nombre, $correo, $contrasenaHasheada);

        if ($stmt->execute()) {
            $idCliente = $stmt->insert_id;
            // $this->actualizarEstadoOnline($correo);
            return ['message' => 'Registro exitoso', 'idCliente' => $idCliente];
        } else {
            return ['message' => 'Error al registrar: ' . $stmt->error];
        }
    }

    public function loginCliente($correo, $contrasena) {
        $idCliente = "";
        $contrasenaHasheada = "";

        $sql = "SELECT idCliente, contrasena FROM Cliente WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta'];
        }

        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($idCliente, $contrasenaHasheada);
            $stmt->fetch();

            if (password_verify($contrasena, $contrasenaHasheada)) {
                $_SESSION['idCliente'] = $idCliente;
                // $this->actualizarEstadoOnline($correo);
                return [
                    'message' => 'Login exitoso',
                    'idCliente' => $idCliente
                ];
            } else {
                return ['message' => '¡Contraseña o Correo Electronico Incorrecto!'];
            }
        } else {
            return ['message' => 'Correo electrónico no encontrado'];
        }
    }

    // private function actualizarEstadoOnline($correo) {
    //     $update_sql = "UPDATE cliente SET online = 1 WHERE correo = ?";
    //     $update_stmt = $this->conn->prepare($update_sql);
    //     $update_stmt->bind_param('s', $correo);
    //     $update_stmt->execute();
    // }

    public function editarCuenta($idCliente, $nombre, $correo, $contrasena, $direccion, $numeroCelular, $correoRecuperacion, $puntoReferencia) {
        // Si no se proporciona una nueva contraseña, obtenemos la actual
        if (empty($contrasena)) {
            $sqlPassword = "SELECT contrasena FROM Cliente WHERE idCliente = ?";
            $stmtPassword = $this->conn->prepare($sqlPassword);
    
            if ($stmtPassword === false) {
                return ['message' => 'Error al preparar consulta para obtener contraseña', 'error' => $this->conn->error];
            }
    
            $stmtPassword->bind_param('i', $idCliente);
            $stmtPassword->execute();
            $resultPassword = $stmtPassword->get_result();
    
            if ($resultPassword->num_rows > 0) {
                $contrasenaHasheada = $resultPassword->fetch_assoc()['contrasena'];
            } else {
                return ['message' => 'Usuario no encontrado', 'error' => $stmtPassword->error];
            }
        } else {
            // Si se proporciona una nueva contraseña, la hasheamos
            $contrasenaHasheada = password_hash($contrasena, PASSWORD_DEFAULT);
        }
    
        // Actualizamos los datos
        $sql = "UPDATE Cliente SET nombre = ?, correo = ?, direccion = ?, numeroCelular = ?, contrasena = ?, correoRecuperacion = ?, puntoReferencia = ? WHERE idCliente = ?";
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
    
        $stmt->bind_param('sssisssi', $nombre, $correo, $direccion, $numeroCelular, $contrasenaHasheada, $correoRecuperacion, $puntoReferencia, $idCliente);
    
        if ($stmt->execute()) {
            return ['message' => 'Datos actualizados correctamente'];
        } else {
            return ['message' => 'Error al actualizar los datos', 'error' => $stmt->error];
        }
    }

    public function obtenerClientePorId($idCliente) {
        if (empty($idCliente)) {
            return ['message' => 'ID de cliente no proporcionado'];
        }

        $sql = "SELECT idCliente, nombre, correo, correoRecuperacion, numeroCelular, direccion, puntoReferencia FROM Cliente WHERE idCliente = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $idCliente);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return ['message' => 'Cliente no encontrado'];
        }
    }

    public function obtenerTodosLosClientes() {
        $sql = "SELECT nombre, direccion, correo, puntoReferencia FROM Cliente";
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

        return $clientes;
    }

    public function buscarClientes($filtro) {
        $sql = "SELECT nombre, direccion, correo, puntoReferencia FROM Cliente WHERE correo LIKE CONCAT('%', ?, '%') OR nombre LIKE CONCAT('%', ?, '%')";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('ss', $filtro, $filtro);
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
        $sql = "SELECT idFactura, metodoPago, productos, fechaCompra, idEstadoFactura FROM Facturas WHERE idCliente = ? ORDER BY idFactura DESC";
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

    public function cancelarCompra($idFactura, $cancelar = 1) {
        // Actualizamos los datos
        $sql = "UPDATE Facturas SET idEstadoFactura = ? WHERE idFactura = ?";
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
    
        $stmt->bind_param('ii', $cancelar, $idFactura);
    
        if ($stmt->execute()) {
            return ['message' => '¡Compra Cancelada con Exito!'];
        } else {
            return ['message' => 'Error al actualizar los datos', 'error' => $stmt->error];
        }
    }
}

?>
