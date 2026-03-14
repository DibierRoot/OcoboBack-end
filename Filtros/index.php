<?php

error_reporting(E_ALL);
ini_set('display_errors', 0); // Oculta errores en producción

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');

require_once 'Producto.php';
require_once 'Database.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Capturar los datos enviados por el frontend en formato JSON
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

    case 'obtenerProductoPorTalla':
        $filtro = isset($data['filtro']) ? $data['filtro'] : null;
    
        if ($filtro) {
            $response = $Producto->obtenerProductoPorTalla($filtro);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Filtro no proporcionado']);
        }
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

    case 'obtenerProductoPorGenero':
            $genero = isset($data['genero']) ? $data['genero'] : null;
        
            if ($genero) {
                $response = $Producto->obtenerProductoPorGenero($genero);
                echo json_encode($response);
            } else {
                echo json_encode(['message' => 'Filtro no proporcionado']);
            }
            break;
                     
    default:
        echo json_encode(['message' => 'Acción no válida']);
        break;
}

?>