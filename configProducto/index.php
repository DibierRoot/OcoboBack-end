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
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

$Producto = new Producto($db);

switch ($action) {

    case 'obtenerProducto':
        $filtro = isset($data['filtro']) ? $data['filtro'] : null;
    
        if ($filtro) {
            $response = $Producto->obtenerProducto($filtro);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Filtro no proporcionado']);
        }
        break;

    case 'eliminarProductos' :
        if (isset($data['idProducto'])) {
            $idProducto = $data['idProducto'];
            $response = $Producto->eliminarProductos($idProducto);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'error al eliminar el producto']);
        }
        break;

    case 'editarProductos':
        if (isset($data['idProducto']) && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $idProducto = $data['idProducto'];
            $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
            $nombre = $data['nombre'];
            $precio = $data['precio'];
            $descripcion = $data['descripcion'];
            $cantidad = $data['cantidad'];
            $idTalla = $data['idTalla'];
            $idColor = $data['idColor'];
            $idCategoria = $data['idCategoria'];
            $idGenero = $data['idGenero'];
            $response = $Producto->editarProductos($idProducto, $imagen, $nombre, $precio, $descripcion, $cantidad, $idTalla, $idColor, $idCategoria, $idGenero);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para el registro']);
        }
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
        echo json_encode(['message' => 'Acción no válida']);
        break;
}

?>