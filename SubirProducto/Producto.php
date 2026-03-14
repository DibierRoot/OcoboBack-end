<?php
class Producto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function registrarProducto($data) {
        $data['fechaPublicacion'] = date("Y-m-d");

        $query = "INSERT INTO " . $this->table_name . " (nombre, precio, descripcion, imagen, fechaPublicacion, cantidad, idTalla, idColor, idGenero, idCategoria)
                  VALUES (:nombre, :precio, :descripcion, :imagen, :fechaPublicacion, :cantidad, :idTalla, :idColor, :idGenero, :idCategoria)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':precio', $data['precio']);
        $stmt->bindParam(':descripcion', $data['descripcion']);
        $stmt->bindParam(':imagen', $data['imagen']);
        $stmt->bindParam(':fechaPublicacion', $data['fechaPublicacion']);
        $stmt->bindParam(':cantidad', $data['cantidad']);
        $stmt->bindParam(':idTalla', $data['idTalla']);
        $stmt->bindParam(':idColor', $data['idColor']);
        $stmt->bindParam(':idGenero', $data['idGenero']);
        $stmt->bindParam(':idCategoria', $data['idCategoria']);

        if ($stmt->execute()) {
            return "Producto registrado correctamente";
        }

        return "Error al registrar el producto";
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
        $sql = "SELECT * FROM Categorias";
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
