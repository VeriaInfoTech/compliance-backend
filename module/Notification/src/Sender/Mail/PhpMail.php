<?php

namespace Pi\Notification\Sender\Mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class PhpMail implements MailInterface
{
    /**
     * @throws Exception
     */
    public function send($config, $params): void
    {
        $SMTPSecure = '';
        switch ($config['phpmailer']['smtp']['SMTPSecure']) {
            case 'ENCRYPTION_STARTTLS':
                $SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                break;

            case 'ENCRYPTION_SMTPS':
                $SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                break;
        }

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        //Server settings
        $mail->isSMTP();
        $mail->Host       = $config['phpmailer']['smtp']['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['phpmailer']['smtp']['username'];
        $mail->Password   = $config['phpmailer']['smtp']['password'];
        $mail->SMTPSecure = $SMTPSecure;
        $mail->Port       = $config['phpmailer']['smtp']['port'];

        // Mail setting
        $mail->setFrom($config['phpmailer']['from']['email'], $config['phpmailer']['from']['name']);
        $mail->addAddress($params['to']['email'], $params['to']['name']);
        $mail->isHTML(true);
        $mail->Subject = $params['subject'];
        $mail->Body    = $params['body'];
        $mail->CharSet = $config['phpmailer']['encoding'];
        //$mail->SMTPDebug = 1;
        $mail->send();
    }
}