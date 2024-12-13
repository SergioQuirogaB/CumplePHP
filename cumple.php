<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

// Cargar la lista de clientes desde un archivo JSON
function loadClients($filename = 'usuarios.json') {
    if (!file_exists($filename)) {
        throw new Exception("Archivo JSON no encontrado: $filename");
    }
    $jsonContent = file_get_contents($filename);
    return json_decode($jsonContent, true);
}

// Enviar correo de cumpleaños
function sendBirthdayEmail($client, $clients) {
    $senderEmail = "magicandy2023@gmail.com";
    $senderPassword = "gsnu kupj fklj njtl"; // Asegúrate de usar una contraseña de aplicación de Google

    $receiverEmail = $client['email'];

    // Crear la lista de CC
    $ccEmails = array_filter(
        array_column($clients, 'email'),
        fn($email) => $email !== $receiverEmail
    );

    $mail = new PHPMailer(true);
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $senderEmail;
        $mail->Password = $senderPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Configuración del correo
        $mail->setFrom($senderEmail, 'Notificaciones');
        $mail->addAddress($receiverEmail, $client['name']);
        foreach ($ccEmails as $ccEmail) {
            $mail->addCC($ccEmail);
        }
        $mail->isHTML(true);
        $mail->Subject = utf8_encode("Hoy " . $client['name'] . " está de cumpleaños!"); // Asegúrate de que se codifique correctamente
        $mail->CharSet = 'UTF-8'; // Establece la codificación de caracteres a UTF-8

        // Contenido del correo (HTML directamente en el código)
        $htmlContent = "
        <html>
        <head>
            <title>¡Feliz Cumpleaños!</title>
            <style>
                /* Estilos responsivos */
                body {
                    font-family: Arial, sans-serif;
                }
                img {
                    max-width: 100%; /* Hace que la imagen sea responsiva */
                    height: auto;
                }
            </style>
        </head>
        <body>
            <img src='cid:image1' alt='Tarjeta de Cumpleaños'>
        </body>
        </html>";

        // Enviar el contenido HTML
        $mail->Body = $htmlContent;

        // Adjuntar imagen
        $imagePath = __DIR__ . '/TarjetaCumpleañosTF.gif';
        if (file_exists($imagePath)) {
            $mail->addEmbeddedImage($imagePath, 'image1');
        } else {
            throw new Exception("Imagen no encontrada: $imagePath");
        }

        // Enviar correo
        $mail->send();
        echo "Correo enviado a {$client['name']} ({$receiverEmail})\n";
    } catch (Exception $e) {
        echo "Error al enviar el correo a {$client['name']} ({$receiverEmail}): {$mail->ErrorInfo}\n";
    }
}

// Verificar cumpleaños
function checkBirthdays() {
    $clients = loadClients();
    $today = date('m-d');
    foreach ($clients as $client) {
        if ($client['birthday'] === $today) {
            sendBirthdayEmail($client, $clients);
        }
    }
}

// Verificar la hora antes de enviar los correos
function checkTimeAndSendEmails() {
    $currentTime = date('H:i'); 
    $targetTime = '08:20'; 

    if ($currentTime === $targetTime) {
        checkBirthdays(); 
    } else {
        echo "No es la hora correcta para enviar correos. Hora actual: $currentTime\n";
    }
}

checkTimeAndSendEmails();
?>
