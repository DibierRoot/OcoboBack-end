<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header('Content-Type: application/json');
date_default_timezone_set("America/Bogota");

// Configuración de CORS
error_reporting(E_ALL);
ini_set('display_errors', 0); // Oculta errores en producción

ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error_log.txt');

use setasign\Fpdi\Fpdi;
require_once __DIR__ . '/../GenerarFactura/vendor/autoload.php';

require_once 'Database.php';

// Crear la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
        
            if (isset($data['idCliente'], $data['metodoPago'], $data['productos'])) {
                $idCliente = $data['idCliente'];
                $metodoPago = $data['metodoPago'];
                $productos = $data['productos'];
                $idEstadoFactura = 1;
        
                try {
                    // Insertar la factura en la base de datos
                    $queryFactura = "INSERT INTO Facturas (idCliente, metodoPago, fechaCompra, idEstadoFactura, productos) VALUES (?, ?, NOW(), ?, ?)";
                    $stmtFactura = $db->prepare($queryFactura);
                    
                    // Convertir productos a JSON
                    $productosJson = json_encode($productos);
                    
                    // Vincular los parámetros con los tipos correctos
                    $stmtFactura->bind_param('isis', $idCliente, $metodoPago, $idEstadoFactura, $productosJson);
                    
                    // Ejecutar la consulta
                    $stmtFactura->execute();
                    
                    // Obtener el ID de la factura generada
                    $idFactura = $stmtFactura->insert_id;

                    foreach ($productos as $producto) {
                        $queryDescuento = "UPDATE Productos SET cantidad = cantidad - ? WHERE idProducto = ?";
                        $stmtDescuento = $db->prepare($queryDescuento);
                        $stmtDescuento->bind_param('ii', $producto['cantidad'], $producto['idProducto']);
                        $stmtDescuento->execute();
        
                        // Verificar si se afectó alguna fila (producto actualizado)
                        if ($stmtDescuento->affected_rows === 0) {
                            throw new Exception('Error al actualizar el inventario para el producto: ' . $producto['nombre']);
                        }
                    }
        
                    // Obtener la información del cliente (ajustado para usar 'idCliente')
                    $queryCliente = "SELECT nombre, numeroCelular, direccion, puntoReferencia, correo FROM Cliente WHERE idCliente = ?";
                    $stmtCliente = $db->prepare($queryCliente);
                    $stmtCliente->bind_param('i', $idCliente); // 'i' para integer
                    $stmtCliente->execute();
                    $resultadoCliente = $stmtCliente->get_result();
                    $cliente = $resultadoCliente->fetch_assoc();

                    if (!$cliente) {
                        throw new Exception('Cliente no encontrado');
                    }

        
                    // Generar un código único para la factura
                    $codigoFactura = 'FAC-' . date('Y') . '-' . str_pad($idFactura, 4, '0', STR_PAD_LEFT);
                    $timestamp = date("Y-m-d");  // Usar timestamp como parte del nombre del archivo
                    $aleatorio = mt_rand(1, 100);
                    $archivoPdf = 'fecha_' . $timestamp . '_' . $idCliente . $aleatorio . '.pdf';
        

                    // Generar el PDF de la factura
                    // Generar el PDF de la factura con el diseño especificado
                    // Generar el PDF de la factura con el diseño especificado
                    $pdf = new Fpdi();
                    $pdf->AddPage();
                    $pdf->SetFont('Arial', 'B', 18);

                    // Fondo de color gris
                    $pdf->setFillColor(28,28, 28);

                    // Logo en la parte superior (reemplaza con la ruta del logo de tu tienda)
                    $pdf->Image(__DIR__ . '/facturas/img/Logo.jpeg', 90, 10, 30); // Ajusta la ruta y tamaño del logo

                    // Título principal
                    $pdf->Ln(40); // Espacio hacia abajo
                    $pdf->Cell(190, 10, mb_convert_encoding('¡Pago Exitoso!', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                    // Detalles principales
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Ln(5);
                    $pdf->Cell(190, 10, 'Fecha: ' . date('d/m/Y') . ' a las ' . date('H:i'), 0, 1, 'C');
                    $pdf->Cell(190, 10, 'Codigo: ' . $codigoFactura, 0, 1, 'C');

                    $pdf->Ln(5);
                    $pdf->Cell(190, 10, 'Entrega: ' . date('d/m/Y') . ' - ' . date('d/m/Y', strtotime('+5 days')), 0, 1, 'C');
                    $pdf->SetFont('Arial', '', 10);
                    $pdf->Cell(190, 10, mb_convert_encoding('Su pedido tardará un aproximado de 3 a 10 días en llegar', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
                    $pdf->Cell(190, 5, mb_convert_encoding('dependiendo de la ciudad donde se encuentre', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');

                    // Línea divisoria
                    $pdf->Ln(10);
                    $pdf->SetDrawColor(150, 150, 150);
                    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
                    $pdf->Ln(5);

                    // Detalles del cliente
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(190, 10, mb_convert_encoding('Detalles del Cliente', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
                    $pdf->SetFont('Arial', '', 12);
                    $pdf->Cell(100, 10, 'Nombre: ' . $cliente['nombre'], 0, 1);
                    $pdf->Cell(100, 10, 'Celular: ' . $cliente['numeroCelular'], 0, 1);
                    $pdf->Cell(100, 10, 'Direccion: ' . $cliente['direccion'], 0, 1);
                    $pdf->Cell(100, 10, 'Punto de Referencia: ' . $cliente['puntoReferencia'], 0, 1);
                    $pdf->Cell(100, 10, 'Correo : ' . $cliente['correo'], 0, 1);

                    // Línea divisoria
                    $pdf->Ln(5);
                    $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
                    $pdf->Ln(5);

                    // Detalle de los productos
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(190, 10, mb_convert_encoding('Detalle de los Productos', 'ISO-8859-1', 'UTF-8'), 0, 1, 'L');
                    $pdf->SetFont('Arial', '', 12);

                    // Agregar tabla de productos
                    $pdf->Cell(100, 10, 'Producto', 1, 0, 'C');
                    $pdf->Cell(30, 10, 'Cantidad', 1, 0, 'C');
                    $pdf->Cell(30, 10, 'Precio', 1, 0, 'C');
                    $pdf->Cell(30, 10, 'Subtotal', 1, 1, 'C'); // Salto de línea al final

                    $total = 0;
                    foreach ($productos as $producto) {
                        $subtotal = $producto['cantidad'] * $producto['precio'];
                        $total += $subtotal;

                        $pdf->Cell(100, 10, $producto['nombre'], 1, 0, 'C');
                        $pdf->Cell(30, 10, $producto['cantidad'], 1, 0, 'C');
                        $pdf->Cell(30, 10, '$' . number_format($producto['precio'], 2), 1, 0, 'C');
                        $pdf->Cell(30, 10, '$' . number_format($subtotal, 2), 1, 1, 'C');
                    }

                    // Total de la factura
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(160, 10, 'Total:', 1, 0, 'R');
                    $pdf->Cell(30, 10, '$' . number_format($total, 2), 1, 1, 'C');

                    // Pie de página
                    $pdf->Ln(10);
                    $pdf->SetFont('Arial', 'B', 12);
                    $pdf->Cell(190, 10, 'OcoboShop Tienda oficial', 0, 1, 'C');

                    // Guardar el PDF
                    $directorioFacturas = __DIR__ . '/facturas';
                    if (!file_exists($directorioFacturas)) {
                        mkdir($directorioFacturas, 0755, true); // Crear el directorio si no existe
                    }
                    $rutaFactura = $directorioFacturas . '/factura_' . $archivoPdf;
                    $pdf->Output('F', $rutaFactura);
                    // Retornar la URL pública del PDF
                    $urlFactura = "http://localhost/OcoboBack-end/GenerarFactura/facturas/factura_" . $archivoPdf ;
        
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Factura generada con éxito',
                        'idFactura' => $idFactura,
                        'invoiceUrl' => $urlFactura,
                    ]);
                } catch (Exception $e) {
                    error_log("Error al generar la factura: " . $e->getMessage());
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Error al generar la factura: ' . $e->getMessage(),
                    ]);
                }
            } else {
                error_log("Faltan parámetros para generar la factura: " . print_r($data, true));
                echo json_encode(['message' => 'Faltan parámetros para generar la factura']);
            }
        }