<?php

namespace Pi\Notification\Sender\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class SymfonyMail implements MailInterface
{
    /**
     * @throws TransportExceptionInterface
     */
    public function send($config, $params): void
    {
        // Configure the Transport
        $transport = Transport::fromDsn($config['symfony']['dns']);

        // Create the Mailer
        $mailer = new Mailer($transport);

        // Set Address
        $fromAddress = new Address($config['symfony']['from']['email'], $config['symfony']['from']['name']);
        $toAddress   = new Address($params['to']['email'], $params['to']['name']);

        // Create the Email
        $email = (new Email())
            ->from($fromAddress)
            ->to($toAddress)
            ->subject($params['subject'])
            ->html($params['body']);

        // Send mail
        try {
            $mailer->send($email);
            echo "Email sent successfully!";
        } catch (\Exception $e) {
            echo "Failed to send email: " . $e->getMessage();

            echo '<pre>';
            var_dump($e);
            die;
        }
    }
}