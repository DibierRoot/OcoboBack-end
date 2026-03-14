<?php

// Configuración de CORS
header("Access-Control-Allow-Origin: http://localhost:5174"); // Permite solo el origen de tu frontend
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE"); // Métodos permitidos
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With"); // Encabezados permitidos
header("Access-Control-Allow-Credentials: true"); // Permite el envío de credenciales (como cookies)
header('Content-Type: application/json'); // Tipo de respuesta JSON

// Si es una solicitud OPTIONS (preflight request), responder correctamente
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
    header('Access-Control-Allow-Credentials: true');
    http_response_code(204);
    exit();
}

// Iniciar la sesión, necesario para acceder a $_SESSION
// Incluir archivos necesarios
require_once 'Database.php';
require_once 'Cliente.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

$cliente = new Cliente($db);

// Capturar los datos enviados por el frontend en formato JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($data['action']) ? $data['action'] : '';

switch ($action) {

    case 'getFacturasPorClienteAdmin':
        $facturas = [];
        $directorio = __DIR__ . '/../GenerarFactura/facturas/';

        try {
            $filtro = isset($data['filtro']) ? $data['filtro'] : null;
            $sql = "SELECT idCliente, nombre, correo, numeroCelular FROM Cliente WHERE nombre LIKE ? OR correo LIKE ?";
            $stmt = $db->prepare($sql);

            if (!$stmt) {
                echo json_encode(['error' => 'Error en la consulta de clientes: ' . $db->error]);
                return;
            }

            // Enlazar el parámetro de entrada
            $busqueda = "%{$filtro}%";
            $stmt->bind_param('ss', $busqueda, $busqueda);
            $stmt->execute();
            $result = $stmt->get_result();

            $clientes = [];
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row;
            }

            if (empty($clientes)) {
                echo json_encode(['error' => 'No se encontraron clientes']);
                return;
            }

            foreach ($clientes as $cliente) {
                $idCliente = $cliente['idCliente'];
                $clienteFacturas = [];

                if ($handle = opendir($directorio)) {
                    while (false !== ($archivo = readdir($handle))) {
                        // Asegurarse de que el archivo es una factura del cliente (usamos una expresión regular)
                        if (preg_match("/factura_fecha_[0-9]{4}-[0-9]{2}-[0-9]{2}_" . $idCliente . "[0-9]*\.pdf$/", $archivo)) {
                            $clienteFacturas[] = [
                                'codigoFactura' => pathinfo($archivo, PATHINFO_FILENAME),
                                'ruta_pdf' => 'http://localhost/OcoboBack-end/GenerarFactura/facturas/' . $archivo,
                                'idCliente' => $idCliente,
                            ];
                        }
                    }
                    closedir($handle);
                }

                $facturas[] = [
                    'cliente' => [
                        'idCliente' => $idCliente,
                        'nombre' => $cliente['nombre'],
                        'correo' => $cliente['correo'],
                        'numeroCelular' => $cliente['numeroCelular']
                    ],
                    'facturas' => $clienteFacturas
                ];
            }

            // Fusionar los datos de clientes con las facturas
            $clientesConFacturas = [];
            foreach ($facturas as $dato) {
                $clientesConFacturas[] = array_merge($dato['cliente'], ['facturas' => $dato['facturas']]);
            }

            // Devolver la respuesta en formato JSON
            echo json_encode(['clientes' => $clientesConFacturas]);

        $stmt->close();

        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al procesar los datos: ' . $e->getMessage()]);
        }
        break;

    case 'getFacturaPorClienteAdmin' :
        $facturas2 = [];
        $directorio = __DIR__ . '/../GenerarFactura/facturas/';

        try {
            $estadoFactura = isset($data['estadoFactura']) ? $data['estadoFactura'] : null;
            $sql = "SELECT c.idCliente, c.nombre, c.correo, c.numeroCelular, f.idFactura AS idFactura, f.metodoPago AS metodo, f.fechaCompra AS fecha, f.idEstadoFactura AS estado, co.nombre AS nombreColaborador FROM Cliente c INNER JOIN Facturas f ON c.idCliente = f.idCliente LEFT JOIN Colaborador co ON f.idColaborador = co.idColaborador WHERE f.idEstadoFactura = ?";
            $stmt = $db->prepare($sql);

            if (!$stmt) {
                echo json_encode(['error' => 'Error en la consulta de clientes: ' . $db->error]);
                return;
            }

            // Enlazar el parámetro de entrada
            $busqueda = $estadoFactura;
            $stmt->bind_param('i', $busqueda);
            $stmt->execute();
            $result = $stmt->get_result();

            $clientes = [];
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row;
            }

            if (empty($clientes)) {
                echo json_encode(['error' => 'No se encontraron clientes', $estadoFactura]);
                return;
            }

            foreach ($clientes as $cliente) {
                $idCliente = $cliente['idCliente'];
                $clienteFacturas = [];

                if ($handle = opendir($directorio)) {
                    while (false !== ($archivo = readdir($handle))) {
                        // Asegurarse de que el archivo es una factura del cliente (usamos una expresión regular)
                        if (preg_match("/^factura_factura_{$idCliente}(_\d+)?\.pdf$/", $archivo)) {
                            $clienteFacturas[] = [
                                'codigoFactura' => pathinfo($archivo, PATHINFO_FILENAME),
                                'ruta_pdf' => 'http://localhost/OcoboBack-end/GenerarFactura/facturas/' . $archivo,
                                'idCliente' => $idCliente
                            ];
                        }
                    }
                    closedir($handle);
                }

                $facturas2[] = [
                    'cliente' => [
                        'idCliente' => $idCliente,
                        'nombre' => $cliente['nombre'],
                        'correo' => $cliente['correo'],
                        'numeroCelular' => $cliente['numeroCelular'],
                        'idFactura' => $cliente['idFactura'],
                        'estado' => $cliente['estado'],
                        'fecha' => $cliente['fecha'],
                        'nombreColaborador' => $cliente ['nombreColaborador']
                    ],
                    'facturas' => $clienteFacturas
                ];
            }

            // Fusionar los datos de clientes con las facturas
            $clientesConFacturas = [];
            foreach ($facturas2 as $dato) {
                $clientesConFacturas[] = array_merge($dato['cliente'], ['facturas' => $dato['facturas']]);
            }

            // Devolver la respuesta en formato JSON
            echo json_encode(['clientes' => $clientesConFacturas]);

        } catch (Exception $e) {
            echo json_encode(['error' => 'Error al procesar los datos: ' . $e->getMessage()]);
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

    case 'cancelarCompra' :
        if (isset($data['idFactura']) || isset($data['cancelar']) || isset($data['idColaborador'])) {
            $idFactura = $data['idFactura'];
            $cancelar = $data['cancelar'];
            $idColaborador = $data['idColaborador'];
            // Llamar a la función cancelarCompra
            $response = $cliente->cancelarCompra($idFactura, $cancelar, $idColaborador);
            echo json_encode($response);
        } else {
            echo json_encode(['message' => 'Faltan parámetros para editar la cuenta']);
        }
        break;

    default:
        echo json_encode(['message' => 'Acción no válida']);
        break;
}
?>
