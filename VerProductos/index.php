<?php
// Conexión a la base de datos
$host = 'localhost';
$user = 'Cristian';
$password = 'xK9#mP2$vL5@nQ8';
$db = 'OcoboDataBase';

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

$conn = new mysqli($host, $user, $password, $db);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

    // Consulta para obtener las imágenes
    $sql = "SELECT idProducto, nombre, precio, descripcion, idColor, idTalla, cantidad, imagen, fechaPublicacion FROM Productos WHERE idTalla = 1
            OR (idTalla = 2 AND NOT EXISTS (
                SELECT 1 FROM Productos WHERE idTalla = 1
                )
            )
            OR (idTalla = 3 AND NOT EXISTS (
                SELECT 1 FROM Productos WHERE idTalla = 2
                )
            )
            OR (idTalla = 4 AND NOT EXISTS (
                SELECT 1 FROM Productos WHERE idTalla = 3
                )
            )
            OR (idTalla = 5 AND NOT EXISTS (
                SELECT 1 FROM Productos WHERE idTalla = 4
                )
            )
            OR (idTalla = 6 AND NOT EXISTS (
                SELECT 1 FROM Productos WHERE idTalla = 5
                )
            )
            OR idTalla = 7
            ORDER BY idProducto DESC";
    $result = $conn->query($sql);

    $productos = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Convertir la imagen binaria a base64
            $imagen_base64 = base64_encode($row['imagen']);
            // Agregar la imagen y su nombre a la respuesta
            $productos[] = [
                'idProducto' => $row['idProducto'],
                'nombre' => $row['nombre'],
                'precio' => $row['precio'],
                'descripcion' => $row['descripcion'],
                'idColor' => $row['idColor'],
                'idTalla' => $row['idTalla'],
                // 'idGenero' => $row['idGenero'],
                'cantidad' => $row['cantidad'],
                'fechaPublicacion' => $row['fechaPublicacion'],
                'imagen' => 'data:image/jpeg;base64,' . $imagen_base64  // Prefijo para imagen JPEG
            ];
        }
    } else {
        echo "0 resultados";
    }
    $conn->close();
    // Devolver las imágenes como respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($productos);


?>
