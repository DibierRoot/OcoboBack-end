<?php

class Colaborador {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarSuper($correo, $contrasena) {
        // Verifica si el correo ya existe
        $sql = "SELECT idSuperAdministrador FROM superAdministrador WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ['message' => 'El correo electrónico ya está registrado'];
        }

        // Si el correo no existe, procede con el registro
        $contrasenaHasheada = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO superAdministrador (correo, contrasena) VALUES (?, ?)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('ss', $correo, $contrasenaHasheada);

        if ($stmt->execute()) {
            $idColaborador = $stmt->insert_id;
            return ['message' => 'Registro exitoso', 'idColaborador' => $idColaborador];
        } else {
            return ['message' => 'Error al registrar: ' . $stmt->error];
        }
    }

    public function registrarColaborador($nombre, $correo, $contrasena) {
        // Verifica si el correo ya existe
        $sql = "SELECT idColaborador FROM Colaborador WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return ['message' => 'El correo electrónico ya está registrado'];
        }

        // Si el correo no existe, procede con el registro
        $contrasenaHasheada = password_hash($contrasena, PASSWORD_BCRYPT);

        $sql = "INSERT INTO Colaborador (nombre, correo, contrasena) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('sss', $nombre, $correo, $contrasenaHasheada);

        if ($stmt->execute()) {
            $idColaborador = $stmt->insert_id;
            return ['message' => 'Registro exitoso', 'idColaborador' => $idColaborador];
        } else {
            return ['message' => 'Error al registrar: ' . $stmt->error];
        }
    }

    public function loginColaborador($nombre, $correo, $contrasena) {
        $idColaborador = "";
        $contrasenaHasheada = "";

        $sql = "SELECT idColaborador, contrasena FROM Colaborador WHERE nombre = ? AND correo = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta'];
        }

        $stmt->bind_param('ss', $nombre, $correo);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($idColaborador, $contrasenaHasheada);
            $stmt->fetch();

            if (password_verify($contrasena, $contrasenaHasheada)) {
                $_SESSION['idColaborador'] = $idColaborador;
                return [
                    'message' => 'Login exitoso',
                    'idColaborador' => $idColaborador
                ];
            } else {
                return ['message' => 'Contraseña incorrecta'];
            }
        } else {
            return ['message' => 'Usuario no encontrado'];
        }
    }

    public function editarCuenta($idColaborador, $nombre, $correo, $numeroCelular, $contrasena) {
        // Si no se proporciona una nueva contraseña, obtenemos la actual
        if (empty($contrasena)) {
            $sqlPassword = "SELECT contrasena FROM Colaborador WHERE idColaborador = ?";
            $stmtPassword = $this->conn->prepare($sqlPassword);
    
            if ($stmtPassword === false) {
                return ['message' => 'Error al preparar consulta para obtener contraseña', 'error' => $this->conn->error];
            }
    
            $stmtPassword->bind_param('i', $idColaborador);
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
        $sql = "UPDATE Colaborador SET nombre = ?, correo = ?, numeroCelular = ?, contrasena = ? WHERE idColaborador = ?";
        $stmt = $this->conn->prepare($sql);
    
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
    
        $stmt->bind_param('ssisi', $nombre, $correo, $numeroCelular, $contrasenaHasheada, $idColaborador);
    
        if ($stmt->execute()) {
            return ['message' => 'Datos actualizados correctamente'];
        } else {
            return ['message' => 'Error al actualizar los datos', 'error' => $stmt->error];
        }
    }

    public function obtenerColaboradorPorId($idColaborador) {
        if (empty($idColaborador)) {
            return ['message' => 'ID de Colaborador no proporcionado'];
        }

        $sql = "SELECT idColaborador, nombre, correo FROM Colaborador WHERE idColaborador = ?";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('i', $idColaborador);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            return ['message' => 'Colaborador no encontrado'];
        }
    }
}

?>