<?php
header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Obtener los datos del archivo
        $datos = file_get_contents($_FILES['imagen']['tmp_name']);
        
        $producto_nombre = $_POST['nombre'];
        $precio = $_POST['precio'];
        $descripcion = $_POST['descripcion'];
        $cantidad = $_POST['cantidad'];
        $idTalla = $_POST['idTalla'];
        $idColor = $_POST['idColor'];
        $idCategoria = $_POST['idCategoria'];
        $idGenero = $_POST['idGenero'];
        $fechaPublicacion = $_POST['fechaPublicacion'];

        // Conexión a la base de datos
        $conn = new mysqli("localhost", "Cristian", "xK9#mP2$vL5@nQ8", "OcoboDataBase");
        if ($conn->connect_error) {
            die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
        }

        // Preparar la consulta SQL para insertar los datos
        $stmt = $conn->prepare("INSERT INTO Productos (nombre, fechaPublicacion, precio, descripcion, imagen, cantidad, idTalla, idColor, idCategoria, idGenero)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Enlazar los parámetros
        $stmt->bind_param("sssssiiiii", $producto_nombre, $fechaPublicacion, $precio, $descripcion, $datos, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["message" => "Producto registrado correctamente"]);
        } else {
            echo json_encode(["error" => "Error al guardar la imagen"]);
        }

        // Cerrar la conexión
        $stmt->close();
        $conn->close();
    } else {
        echo json_encode(["error" => "Error al subir la imagen"]);
    }
} else {
    echo json_encode(["error" => "Método no permitido"]);
}
