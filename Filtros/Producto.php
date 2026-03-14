<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerProducto($filtro) {
        // Consulta SQL
        $sql = "SELECT idProducto, nombre, descripcion, precio, idColor, idTalla, cantidad, imagen FROM Productos WHERE idCategoria = ?
            AND (
                idTalla = 1
                OR (idTalla = 2 AND NOT EXISTS (
                    SELECT 1 FROM Productos WHERE idTalla = 1
                    )
                )
                OR (idTalla = 3 AND NOT EXISTS (
                    SELECT 1 FROM Productos WHERE idTalla = 2
                    )
                )
                OR (idTalla = 4 AND NOT EXISTS (
                    SELECT 1 FROM Productos WHERE idTalla = 1
                    )
                )
                OR (idTalla = 5 AND NOT EXISTS (
                    SELECT 1 FROM Productos WHERE idTalla = 2
                    )
                )
                OR idTalla = 7
            )
            ORDER BY idProducto DESC";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        // Enlazar el parámetro de entrada
        $stmt->bind_param('i', $filtro);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir la imagen binaria a base64
            $imagen_base64 = base64_encode($row['imagen']);
            $productos[] = [
                'idProducto' => $row['idProducto'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'precio' => $row['precio'],
                'idColor' => $row['idColor'],
                'idTalla' => $row['idTalla'],
                'cantidad' => $row['cantidad'],
                'imagen' => 'data:image/jpeg;base64,' . $imagen_base64 // Prefijo para imagen JPEG
            ];
        }

        $stmt->close();

        return $productos;
    }

    public function obtenerProductoPorTalla($filtro) {
        // Consulta SQL
        $sql = "SELECT idProducto, nombre, descripcion, precio, idColor, idTalla, cantidad, imagen FROM Productos WHERE nombre = ? ORDER BY idTalla ASC";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        // Enlazar el parámetro de entrada
        $stmt->bind_param('s', $filtro);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir la imagen binaria a base64
            $imagen_base64 = base64_encode($row['imagen']);
            $productos[] = [
                'idProducto' => $row['idProducto'],
                'nombre' => $row['nombre'],
                'descripcion' => $row['descripcion'],
                'precio' => $row['precio'],
                'idColor' => $row['idColor'],
                'idTalla' => $row['idTalla'],
                'cantidad' => $row['cantidad'],
                'imagen' => 'data:image/jpeg;base64,' . $imagen_base64 // Prefijo para imagen JPEG
            ];
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

    public function obtenerTodasLasTallas() {
        $sql = "SELECT * FROM talla";
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
    public function obtenerProductoPorGenero($genero) {
        $sql = "SELECT idProducto, nombre, precio, idColor, idTalla, idGenero, cantidad, imagen FROM producto WHERE idCategoria = ? AND cantidad > 0";
        $stmt = $this->conn->prepare($sql);

        if ($stmt === false) {
            return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
        }

        // Enlazar el parámetro de entrada
        $stmt->bind_param('i', $genero);
        $stmt->execute();
        $result = $stmt->get_result();

        $productos = [];
        while ($row = $result->fetch_assoc()) {
            // Convertir la imagen binaria a base64
            $imagen_base64 = base64_encode($row['imagen']);
            $productos[] = [
                'idProducto' => $row['idProducto'],
                'nombre' => $row['nombre'],
                'precio' => $row['precio'],
                'idColor' => $row['idColor'],
                'idTalla' => $row['idTalla'],
                'idGenero' => $row['idGenero'],
                'cantidad' => $row['cantidad'],
                'imagen' => 'data:image/jpeg;base64,' . $imagen_base64 // Prefijo para imagen JPEG
            ];
        }

        $stmt->close();

        return $productos;
    }
}
?>
