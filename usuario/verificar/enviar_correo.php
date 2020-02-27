<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$enlaceConfirmar = $_SERVER['SERVER_NAME'].'/eDixit/usuario/verificar/?verificacion='.urlencode($_SESSION['verificacion']).'&correo='.urlencode($_SESSION['usuario_correo']);

$body = 'Hola, '.$_SESSION['usuario_nombre'].' <a href="http://'.$enlaceConfirmar.'">verifica aquí</a> tu correo electrónico para acceder a todas las características de eDixit<br>
También puedes copiar el siguiente enlace y pegarlo en la ventana de tu navegador:<br>'.$enlaceConfirmar;
$bodySinHTML = 'Hola, '.$_SESSION['usuario_nombre'].' copia y pega esto en un navegador para verificar su correo: http://'.$enlaceConfirmar.' para acceder a todas las características de eDixit';


try {
    require '../../phpmailer/src/Exception.php';
    require '../../phpmailer/src/PHPMailer.php';
    require '../../phpmailer/src/SMTP.php';
    
    $mail = new PHPMailer();
    
    //Server settings
    // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'cristichiedixit@gmail.com';
    $mail->Password = 'MiContraGuapisime312';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    //Recipients
    $mail->SetFrom('cristichiedixit@gmail.com', 'eDixit');
    $mail->AddAddress($_SESSION['usuario_correo']);
    $mail->AddReplyTo('cristichiedixit@gmail.com', "eDixit");

    // Attachments
    // $mail->addAttachment('/var/tmp/file.tar.gz');
    // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');

    // Content
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Verifica tu email en eDixit';
    $mail->Body = $body;
    $mail->AltBody = $bodySinHTML;

    if ($mail->send()) {
        $_SESSION['mensaje_cabecera'] = 'Correo de verificación enviado';
        $_SESSION['mensaje_cabecera_bien'] = true;
    } else {
        $_SESSION['mensaje_cabecera'] = 'El correo de verificación no se pudo enviar';
        $_SESSION['mensaje_cabecera_bien'] = false;
    }
    if (isset($_REQUEST['volver'])){
        header('Location: '.$_REQUEST['volver']);
    }
} catch (Exception $e) {
    echo "No se pudo enviar el email: {$mail->ErrorInfo}";
}
?>