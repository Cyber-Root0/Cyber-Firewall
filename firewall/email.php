<?php

require_once 'src/PHPMailer.php';
require_once 'src/SMTP.php';
require_once 'src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function SendEmail($destino,$assunto,$mensagem){
$mail = new PHPMailer(true);

$destino=$destino;
$assunto=$assunto;
$mensagem=$mensagem;

    try {
    
        $mail->SMTPDebug = 0;                     
        $mail->isSMTP();
        $mail->SMTPAuth=true;
        $mail->SMTPSecure='tls';
        $mail->Host='tls://smtp.gmail.com';
        $mail->Username='';
        $mail->Password='';
        $mail->Port=587;
        $mail->setFrom('','Cyber Firewall');
        $mail->addReplyTo('','Cyber Firewall');
        $mail->addAddress($destino);
        $mail->IsHTML(true);
        $mail->CharSet='UTF-8';
        $mail->Subject=$assunto;
        $mail->Body=$mensagem;
        $envia=$mail->Send(); 
        return "Enviado com Sucesso";

    } catch (Exception $e) {
        return "Limite Ja Excedido";
    
    }
}


?>
