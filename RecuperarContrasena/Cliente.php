<?php
// Asegúrate de incluir PHPMailer al inicio del archivo
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Si estás usando Composer, asegúrate de cargar PHPMailer

class Cliente {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Función para recuperar la contraseña
    public function recuperarContrasena($correoElectronico) {
        // Sanitizar y verificar el correo
        $correoElectronico = filter_var($correoElectronico, FILTER_SANITIZE_EMAIL);

        // Verificar si el correo existe en la base de datos
        $sql = "SELECT idCliente FROM Cliente WHERE correo = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('s', $correoElectronico); // Vinculamos el correo
        $stmt->execute();
        $stmt->store_result(); // Guardamos el resultado

        // Verificamos si el correo existe
        if ($stmt->num_rows == 0) {
            return ['message' => 'Correo de recuperación no encontrado'];
        }

        // Si el correo existe, procedemos a generar la contraseña provisional
        $stmt->fetch(); // Extraemos los datos (aunque solo estamos verificando la existencia)

        // Generar una contraseña provisional aleatoria
        $contrasenaProvisional = $this->generarContrasenaProvisional();

        // Hashear la nueva contraseña provisional
        $contrasenaHasheada = password_hash($contrasenaProvisional, PASSWORD_BCRYPT);

        // Actualizar la contraseña en la base de datos
        $sql = "UPDATE Cliente SET contrasena = ? WHERE correo = ?";
        $updateStmt = $this->conn->prepare($sql);
        $updateStmt->bind_param('ss', $contrasenaHasheada, $correoElectronico);

        if ($updateStmt->execute()) {
            // Enviar correo electrónico con la contraseña provisional
            if ($this->enviarcorreoElectronico($correoElectronico, $contrasenaProvisional)) {
                return ['message' => 'La contraseña provisional ha sido enviada a su correo'];
            } else {
                return ['message' => 'Error al enviar el correo de recuperación'];
            }
        } else {
            return ['message' => 'Error al actualizar la contraseña'];
        }
    }

    // Función para generar una contraseña provisional aleatoria
    private function generarContrasenaProvisional($longitud = 12) {
        $caracteres = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $contrasena = '';
        for ($i = 0; $i < $longitud; $i++) {
            $contrasena .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $contrasena;
    }

    // Función para enviar el correo con la contraseña provisional usando PHPMailer
    private function enviarcorreoElectronico($correoElectronico, $contrasenaProvisional) {
        $mail = new PHPMailer(true); // Instancia de PHPMailer
    
        try {
            // Configuración del servidor SMTP
            $mail->isSMTP();                                              // Usar SMTP
            $mail->Host = 'smtp.gmail.com';                                // Servidor SMTP de Gmail
            $mail->SMTPAuth = true;                                         // Autenticación SMTP
            $mail->Username = 'ocoboficialsoporte@gmail.com';                     // Tu correo
            $mail->Password = 'bofh ctpe qynm zoam';                       // Tu contraseña
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;             // Encriptación TLS
            $mail->Port = 587;                                              // Puerto SMTP
    
            // Remitente y destinatario
            $mail->setFrom('no-reply@tusitio.com', 'Soporte Técnico');
            $mail->addAddress($correoElectronico);                         // Correo del usuario
    
            // Configurar la codificación de caracteres a UTF-8
            $mail->CharSet = 'UTF-8'; // Esto es esencial para que se muestren correctamente caracteres como la "ñ"
    
            // Contenido del correo (cuerpo HTML con estilo)
            $mail->isHTML(true);                                            // Correo en formato HTML
            $mail->Subject = 'Recuperación de Contraseña';
    
            // Cuerpo del correo con estilo
            $mail->Body = "
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .container {
                        width: 100%;
                        max-width: 600px;
                        margin: 20px auto;
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    h2 {
                        color: black;
                        text-align: center;
                    }
                    .content {
                        margin-top: 20px;
                        font-size: 16px;
                        line-height: 1.6;
                    }
                    .content b {
                        color: black;
                        font-size: 18px;
                    }
                    .footer {
                        text-align: center;
                        margin-top: 40px;
                        font-size: 14px;
                        color: #999;
                    }
                    .footer a {
                        color: black;
                        text-decoration: none;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2 ><strong>Recuperación de Contraseña</strong></h2>
                    <div class='content'>
                        <p>Estimado usuario,</p>
                        <p>Hemos recibido una solicitud para recuperar la contraseña de su cuenta. A continuación, encontrará su nueva contraseña provisional:</p>
                        <p><strong><b>$contrasenaProvisional</b></strong></p>
                        <p>Por favor, ingrese a su cuenta y cámbiela lo antes posible para garantizar la seguridad de su información.</p>
                        <p>Si no solicitó esta recuperación de contraseña, por favor ignore este correo o póngase en contacto con nuestro soporte técnico.</p>
                    </div>
                    <div class='footer'>
                        <p>Si necesita asistencia, no dude en <a href='mailto:ocoboficialsoporte@gmail.com'>contactarnos</a>.</p>
                    </div>
                </div>
            </body>
            </html>";
    
            // Enviar el correo
            $mail->send();
            return true; // Si todo va bien
        } catch (Exception $e) {
            return ['message' => 'Error al enviar el correo: ' . $e->getMessage()]; // Devolver el error específico
        }
    }
    
}
?>
