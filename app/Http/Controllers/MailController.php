<?php

namespace App\Http\Controllers;

use PHPMailer;
use Illuminate\Http\Request;

class MailController extends Controller
{
    public function Mail($to, $name, $subject, $body, $altBody = "")
    {
        $mail = new PHPMailer;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->IsSMTP();  // telling the class to use SMTP
        $mail->Mailer = env('MAIL_DRIVER');
        $mail->SMTPSecure = env('MAIL_ENCRYPTION');
        $mail->Host = env('MAIL_HOST');
        $mail->Port = env('MAIL_PORT');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME'); // SMTP username
        $mail->Password = env('MAIL_PASSWORD'); // SMTP password
        $mail->Priority = 1;

        $mail->setFrom(env('MAIL_USERNAME'), env('MAIL_FROM_NAME'));
        $mail->addAddress($to, $name);// Add a recipient
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = $altBody;
        $mail->isHTML(true);
        //$mail->AddEmbeddedImage(public_path() . "/img/logo.png", 'logo');

        $sent = $mail->send();
        return $sent;
    }
}
