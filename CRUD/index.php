<?php

// Configuración de CORS
error_reporting(E_ALL);
ini_set('display_errors', 0); // Oculta errores en producción

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

header("Access-Control-Allow-Origin: http://localhost:5173"); // Permite solo el origen de tu frontend
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
require_once __DIR__ . '/../GenerarFactura/vendor/autoload.php';
// Incluir archivos necesarios
require_once 'Cliente.php';
require_once 'Database.php';

use setasign\Fpdi\Fpdi;

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

$cliente = new Cliente($db);

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
            $response = $cliente->registrarCliente($nombre, $correo, $contrasena);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para el registro']);
        }
        break;

    case 'login':
        if (isset($data['correo']) && isset($data['contrasena'])) {
            $correo = $data['correo'];
            $contrasena = $data['contrasena'];
            $response = $cliente->loginCliente($correo, $contrasena);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para el login']);
        }
        break;

    case 'getCliente':
        if (isset($data['idCliente'])) {
            $idCliente = $data['idCliente'];
            $response = $cliente->obtenerClientePorId($idCliente);
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'ID de cliente no proporcionado']);
        }
        break;


    case 'edit':
        if (isset($data['idCliente']) || isset($data['nombre']) || isset($data['correo']) || isset($data['contrasena']) || isset($data['direccion']) || isset($data['numeroCelular']) || isset($data['correoRecuperacion']) || isset($data['puntoReferencia'])) {
            $idCliente = $data['idCliente'];
            $nombre = $data['nombre'];
            $correo = $data['correo'];
            $contrasena = $data['contrasena'];
            $direccion = $data['direccion'];
            $numeroCelular = $data['numeroCelular'];
            $correoRecuperacion = $data['correoRecuperacion'];
            $puntoReferencia = $data['puntoReferencia'];

            // Llamar a la función editarCuenta
            $response = $cliente->editarCuenta($idCliente, $nombre, $correo, $contrasena, $direccion, $numeroCelular, $correoRecuperacion, $puntoReferencia);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
        }
        break;

    case 'obtenerTodos':
        $response = $cliente->obtenerTodosLosClientes();
        echo json_encode($response);
        break;

    case 'buscarClientes':
        $filtro = isset($data['filtro']) ? $data['filtro'] : null;
    
        if ($filtro) {
            $response = $cliente->buscarClientes($filtro);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Filtro no proporcionado']);
        }
        break;

        case 'getFechaFactura' :
            if (isset($data['idCliente'])) {
                $idCliente = $data['idCliente'];
                $response = $cliente->obtenerFechaFactura($idCliente);
                echo json_encode($response);
            } else {
                echo json_encode(['error' => 'ID de Cliente no proporcionado']);
            }
        break;

            case 'getFacturasPorCliente':
                if (isset($data['idCliente'])) {
                    $idCliente = $data['idCliente'];
                    $facturas = [];
            
                    // Ruta donde se almacenan los archivos PDF de las facturas
                    $directorio = __DIR__ . '/../GenerarFactura/facturas'; // Asegúrate de poner la ruta correcta
            
                    // Abrir el directorio
                    if ($handle = opendir($directorio)) {
                        // Leer los archivos en el directorio
                        while (false !== ($archivo = readdir($handle))) {
                            // Comprobar si el archivo es un PDF y contiene el idCliente en su nombre
                            if (strpos($archivo, "factura_fecha_") !== false && pathinfo($archivo, PATHINFO_EXTENSION) === 'pdf') {
                                $facturas[] = [
                                    'codigoFactura' => $archivo, // Puedes usar el nombre del archivo como código de factura
                                    'ruta_pdf' => 'http://localhost/OcoboBack-end/GenerarFactura/facturas/' . $archivo
                                ];
                            }
                        }
                        closedir($handle);
                    }
            
                    echo json_encode($facturas);
                } else {
                    echo json_encode(['error' => 'ID de cliente no proporcionado']);
                }
            break;

            case 'cancelarCompra' :
                if (isset($data['idFactura']) || isset($data['cancelar'])) {
                    $idFactura = $data['idFactura'];
                    $cancelar = $data['cancelar'];
                    // Llamar a la función cancelarCompra
                    $response = $cliente->cancelarCompra($idFactura, $cancelar);
                    echo json_encode($response);
                } else {
                    echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
                }
                break;

            
    default:
        echo json_encode(['message' => 'Acción no válida']);
    break;
}