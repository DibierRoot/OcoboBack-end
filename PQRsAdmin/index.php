<?php

// Configuración de CORS
error_reporting(E_ALL);
ini_set('display_errors', 0); // Oculta errores en producción

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header("Access-Control-Allow-Origin: http://localhost:5174"); // Permite solo el origen de tu frontend
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Métodos permitidos
header("Access-Control-Allow-Headers: X-API-KEY, Origin, Accept, Access-Control-Request-Method, Content-Type, Authorization, X-Requested-With"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Permite el envío de credenciales (como cookies)
header('Content-Type: application/json'); // Tipo de respuesta JSON

// Si es una solicitud OPTIONS (preflight request), responder correctamente
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Responde con los métodos y encabezados permitidos
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    // Responde con el código 204 para indicar que no hay contenido
    http_response_code(204);
    exit(); // Terminamos la ejecución aquí para evitar continuar con el procesamiento de la solicitud
}

// Iniciar la sesión, necesario para acceder a $_SESSION
session_start();
require_once 'PQR.php';
require_once 'Database.php';

use setasign\Fpdi\Fpdi;
// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

$PQR = new PQR($db);

// Capturar los datos enviados por el frontend en formato JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

// Controlar las diferentes acciones basadas en la solicitud
switch ($action) {
    case 'obtenerPQRs':
            $response = $PQR->verPQR();
            echo json_encode($response);
        break;

    case 'obtenerPQRsNoLeidos' :
        if (isset($data['idEstadoPQR'])) {
            $idEstadoPQR = $data['idEstadoPQR'];
            $response = $PQR->obtenerPQRsNoLeidos($idEstadoPQR);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
        }
    break;

    case 'obtenerPQRFiltrado' :
        if (isset($data['fecha'])) {
            $fecha = $data['fecha'];
            $response = $PQR->verPQRFiltrado($fecha);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
        }
    break;

    case 'leerPQRs' :
            $response = $PQR->visto();
            echo json_encode($response);
    break;
    }

?>