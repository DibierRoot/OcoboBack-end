<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerTodosLosProductos() {
        $sql = "SELECT * FROM Productos";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        return $productos;
    }

    public function obtenerProducto($filtro) {
        $sql = "SELECT * FROM Productos WHERE nombre LIKE CONCAT('%', ?, '%') OR descripcion LIKE CONCAT('%', ?, '%')";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->bind_param('ss', $filtro, $filtro);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }

        $stmt->close();

        return $productos;
    }

    public function eliminarProductos($idProducto) {
        $sql = "DELETE FROM Productos WHERE idProducto = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
        
        // Pasar el parámetro idProducto a la consulta
        $stmt->bind_param("i", $idProducto);
        
        if ($stmt->execute()) {
            return ['message' => 'Producto eliminado exitosamente'];
        } else {
            return ['message' => 'Error al ejecutar la consulta', 'error' => $stmt->error];
        }
    }

    public function editarProductos($idProducto, $imagen, $nombre, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero) {
        $sql = "UPDATE Productos SET nombre = ?, imagen = ?, precio = ?, descripcion = ?, cantidad = ?, idTalla = ?, idColor = ?, idCategoria = ?, idGenero = ? WHERE idProducto = ?";
        $stmt = $this->conn->prepare($sql);
        
        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }
        
        // Pasar el parámetro idProducto a la consulta
        $stmt->bind_param("ssdsiiiiii", $nombre, $imagen, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero, $idProducto);
        
        if ($stmt->execute()) {
            return ['message' => 'Producto actualizado exitosamente'];
        } else {
            return ['message' => 'Error al ejecutar la consulta', 'error' => $stmt->error];
        }
    }

    public function obtenerTodosLosColores() {
        $sql = "SELECT * FROM Colores";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $colores = [];
        while ($row = $result->fetch_assoc()) {
            $colores[] = $row;
        }

        return $colores;
    }

    public function obtenerTodasLasTallas() {
        $sql = "SELECT * FROM Tallas";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $tallas = [];
        while ($row = $result->fetch_assoc()) {
            $tallas[] = $row;
        }

        return $tallas;
    }

    public function obtenerTodosLosGeneros() {
        $sql = "SELECT * FROM Generos";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $generos = [];
        while ($row = $result->fetch_assoc()) {
            $generos[] = $row;
        }

        return $generos;
    }

    public function obtenerTodasLasCategorias() {
        $sql = "SELECT idCategoria, categoria FROM Categorias";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }

        return $categorias;
    }
}
?>
