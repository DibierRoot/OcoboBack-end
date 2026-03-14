<?php

// Configuración de CORS (debe ir antes de cualquier salida)
header("Access-Control-Allow-Origin: http://localhost:5173"); // Permite solo el origen de tu frontend
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Permite el envío de credenciales (cookies)
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

// Incluir archivos necesarios
require_once 'Database.php';
require_once 'Cliente.php';  // Aquí incluyes la clase Cliente

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Capturar los datos enviados por el frontend
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

// Manejo de acciones
switch ($action) {
    case 'sendCode':
        if (!isset($data['correo'])) {
            echo json_encode(['error' => 'Falta el parámetro email']);
            exit();
        }
        $correoElectronico = $data['correo'];

        // Crear una instancia de la clase Cliente
        $cliente = new Cliente($db);

        // Llamar al método recuperarContrasena() de la clase Cliente
        $resultado = $cliente->recuperarContrasena($correoElectronico);

        // Devolver el resultado al frontend
        echo json_encode($resultado);
        break;

    default:
        echo json_encode(['message' => 'Acción no válida']);
        break;
}
