<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
// Configuración de CORS
header("Access-Control-Allow-Origin: http://localhost:5174"); // Permite solo el origen de tu frontend
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Permite el envío de credenciales (como cookies)
header('Content-Type: application/json'); // Tipo de respuesta JSON

require_once 'Colaborador.php';
require_once 'Database.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

$colaborador = new Colaborador($db);

// Capturar los datos enviados por el frontend en formato JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

// Controlar las diferentes acciones basadas en la solicitud
switch ($action) {
    case 'register':
        if (isset($data['nombre']) && isset($data['correo']) && isset($data['contrasena'])) {
            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $contrasena = $data['contrasena'];
            $response = $colaborador->registrarColaborador($nombre, $correo, $contrasena);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para el registro']);
        }
        break;

    case 'login':
        if (isset($data['nombre']) && isset($data['correo']) && isset($data['contrasena'])) {
            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $contrasena = $data['contrasena'];
            $response = $colaborador->loginColaborador($nombre, $correo, $contrasena);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para el login']);
        }
        break;

    case 'edit':
        if (isset($data['idColaborador']) || isset($data['nombre']) || isset($data['correo']) || isset($data['contrasena']) || isset($data['numeroCelular'])) {
            $idColaborador = $data['idColaborador'];
            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $contrasena = $data['contrasena'];
            $numeroCelular = $data['numeroCelular'];

            // Llamar a la función editarCuenta
            $response = $colaborador->editarCuenta($idColaborador, $nombre, $correo, $numeroCelular, $contrasena);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
        }
        break;


    case 'getColaborador':
        if (isset($data['idColaborador'])) {
            $idColaborador = $data['idColaborador'];
            $response = $colaborador->obtenerColaboradorPorId($idColaborador);
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'ID de cliente no proporcionado']);
        }
        break;

    default:
        echo json_encode(['message' => 'Acción no válida']);
        break;
}
?>
