<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function editarProductos($idProducto, $imagen, $nombre, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero) {
        if ($imagen) {
            $sql = "UPDATE Productos SET nombre = ?, imagen = ?, precio = ?, descripcion = ?, cantidad = ?, idTalla = ?, idColor = ?, idCategoria = ?, idGenero = ? WHERE idProducto = ?";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt === false) {
                return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
            }
            
            // Pasar el parámetro idProducto a la consulta
            $stmt->bind_param("ssdsiiiiii", $nombre, $imagen, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero, $idProducto);
            
            if ($stmt->execute()) {
                return ['message' => 'Producto actualizado exitosamente'];
            }

        } elseif (!$imagen) {
            $sql = "UPDATE Productos SET nombre = ?, precio = ?, descripcion = ?, cantidad = ?, idTalla = ?, idColor = ?, idCategoria = ?, idGenero = ?  WHERE idProducto = ?";
            $stmt = $this->conn->prepare($sql);
            
            if ($stmt === false) {
                return ['message' => 'Error en la preparación de la consulta', 'error' => $this->conn->error];
            }
            
            // Pasar el parámetro idProducto a la consulta
            $stmt->bind_param("sdsiiiiii", $nombre, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero, $idProducto);
            
            if ($stmt->execute()) {
                return ['message' => 'Producto actualizado exitosamente'];
            }
        } else {
                return ['message' => 'Error al ejecutar la consulta', 'error' => $stmt->error];
        }
    }
}
?>
