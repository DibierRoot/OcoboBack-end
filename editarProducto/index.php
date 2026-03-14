<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: http://localhost:5174");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

require_once 'Producto.php';
require_once 'Database.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Capturar los datos enviados por el frontend en formato JSON cuando no quiera pasar imagenes
// $data = json_decode(file_get_contents('php://input'), true);

// Capturar los datos enviados por el frontend en formato JSON cuando quiera pasar imagenes
$data = $_POST;
$action = isset($data['action']) ? $data['action'] : '';

$Producto = new Producto($db);

if ($action === "editarProductos") {
    if (isset($data['idProducto'])) {
        
        $idProducto = $data['idProducto'];
        $nombre = $data['nombre'];
        $precio = $data['precio'];
        $descripcion = $data['descripcion'];
        $cantidad = $data['cantidad'];
        $idTalla = $data['idTalla'];
        $idColor = $data['idColor'];
        $idCategoria = $data['idCategoria'];
        $idGenero = $data['idGenero'];

        $imagen = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
    }
        $response = $Producto->editarProductos($idProducto, $imagen, $nombre, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero);
        echo json_encode($response);
    } else {
        echo json_encode(['message' => 'Faltan parámetros para el registro']);
    }
} else {
    echo json_encode(['message' => 'Acción no válida']);

}

?>