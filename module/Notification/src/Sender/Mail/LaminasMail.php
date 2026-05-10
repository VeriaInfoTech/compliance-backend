<?php

namespace Pi\Notification\Sender\Mail;

use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;

class LaminasMail implements MailInterface
{
    public function send($config, $params): void
    {
        $mail = new Message();
        $mail->setFrom($config['laminas']['from']['email'], $config['laminas']['from']['name']);
        $mail->addTo($params['to']['email'], $params['to']['name']);
        $mail->setSubject($params['subject']);
        $mail->setBody($params['body']);
        $mail->setEncoding($config['laminas']['encoding']);

        $transport = new Sendmail();
        $transport->send($mail);
    }
}