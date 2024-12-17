<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerService
{
    public static function sendMail($to, $subject, $body, $from = 'laraveltest592@gmail.com', $fromName = 'Laravel App')
    {
        $mail = new PHPMailer(true);

        try {
            // Konfiguracja serwera Gmail SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'laraveltest592@gmail.com';
            $mail->Password   = 'srcz cxzp wndj hnnr';
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;


            // Ustawienia nadawcy i odbiorcy
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);

            // Treść wiadomości
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            // Wysyłanie wiadomości
            $mail->send();
            return true;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
