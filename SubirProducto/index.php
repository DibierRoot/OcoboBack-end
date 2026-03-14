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

$Producto = new Producto($db);

// Capturar los datos enviados por el frontend en formato JSON
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? '';

    switch ($action) {
        case 'registrarProducto':
            $nombre = $data['nombre'];
            $imagen = $_FILES['imagen']['name'];
            $tempPath = $_FILES['imagen']['tmp_name'];
            $targetPath = "uploads/" . $imagen;

            // Mover la imagen al directorio uploads/
            if (move_uploaded_file($tempPath, $targetPath)) {
                $data['imagen'] = $imagen;
                $response = $producto->registrarProducto($data);
            } else {
                $response = "Error al cargar la imagen";
            }

            echo json_encode(['message' => $response]);
            break;
            
        case 'obtenerTodosLosColores':
            $response = $Producto->obtenerTodosLosColores();
            echo json_encode($response);
            break;

        case 'obtenerTodasLasTallas':
            $response = $Producto->obtenerTodasLasTallas();
            echo json_encode($response);
            break;
        
        case 'obtenerTodasLasCategorias':
            $response = $Producto->obtenerTodasLasCategorias();
            echo json_encode($response);
            break;

        case 'obtenerTodosLosGeneros':
            $response = $Producto->obtenerTodosLosGeneros();
            echo json_encode($response);
            break;
                
        default:
            echo json_encode(['message' => 'Accion no valida']);
            break;
    }
}

?>