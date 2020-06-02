<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// $enlaceConfirmar = 'http://www.iesmurgi.org:86/~cristobal/usuario/verificar/?verificacion='.urlencode($_SESSION['verificacion']).'&correo='.urlencode($_SESSION['usuario_correo']);
$enlaceConfirmar = $_SERVER['SERVER_NAME'].$_SERVER['CONTEXT_PREFIX'].'/usuario/verificar/?verificacion='.urlencode($_SESSION['verificacion']).'&correo='.urlencode($_SESSION['usuario_correo']);
// var_export($_SERVER);
// echo '<br>';
// die($enlaceConfirmar);

$body = 'Hola, '.$_SESSION['usuario_nombre'].' <a href="'.$enlaceConfirmar.'">verifica aquí</a> tu correo electrónico para acceder a todas las características de eDixit<br>
También puedes copiar el siguiente enlace y pegarlo en la ventana de tu navegador:<br>'.$enlaceConfirmar;
$bodySinHTML = 'Hola, '.$_SESSION['usuario_nombre'].' copie y pegue este enlace en un navegador para verificar su correo: '.$enlaceConfirmar;


try {
    require '../../phpmailer/src/Exception.php';
    require '../../phpmailer/src/PHPMailer.php';
    require '../../phpmailer/src/SMTP.php';
    
    // Modo debug
    // $mail = new PHPMailer(true);
    $mail = new PHPMailer();
    
    //Server settings
    $mail->isSMTP();
    $mai->Mailer = 'smtp';
    // $mail->SMTPDebug = SMTP::DEBUG_CONNECTION;
    // $mail->Debugoutput = 'html';
    // $mail->Host = 'smtp.gmail.com';
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // $mail->Host = '142.250.13.108';
    $mail->Host = gethostbyname('tls://smtp.gmail.com');
    $mail->SMTPAuth = true;
    $mail->Username = 'cristichiedixit@gmail.com';
    $mail->Password = 'MiContraGuapisime312';
    $mail->Password = 'kwvhjykpheqxtdum';
    // $mail->Password = 'kwvhjykpheqxtdum';
    $mail->SMTPSecure = 'tls';
    $mail->Port = '587';
    // $mail->SMTPSecure = 'ssl';
    // $mail->Port = 465;
    // $PHPMailer->SMTPOptions = array (
    //     'ssl' => array (
    //         'verify_peer' => false,
    //         'verify_peer_name' => false,
    //         'allow_self_signed' => true
    //     )
    // );

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
    $mail->Subject = 'Verifica tu email para jugar a eDixit';
    $mail->Body = $body;
    $mail->AltBody = $bodySinHTML;

    if ($mail->send()) {
        $_SESSION['mensaje_cabecera'] = 'Correo de verificación enviado';
        $_SESSION['mensaje_cabecera_bien'] = true;
    } else {
        // file_put_contents('errores.log', 'Error al enviar correo: '.$mail->ErrorInfo);
        $_SESSION['mensaje_cabecera'] = 'El correo de verificación no se pudo enviar, contacte con un administrador. Disculpe las molestias.';
        $_SESSION['mensaje_cabecera_bien'] = false;
    }
    if (isset($_REQUEST['volver'])){
        header('Location: '.$_REQUEST['volver']);
    }
} catch (Exception $e) {
    echo "No se pudo enviar el email: {$mail->ErrorInfo}";
}
?>