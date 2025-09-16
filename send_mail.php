<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer classes
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'careerinfojoseph@gmail.com';   // 🔹 Replace with your Gmail
        $mail->Password = 'poqytryrdvbvrpva';     // 🔹 Replace with your App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender & recipient
        $mail->setFrom('careerinfojoseph@gmail.com', 'Lost & Found');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
