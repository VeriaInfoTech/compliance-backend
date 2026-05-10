<?php

namespace Pi\Notification\Factory\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Service\UtilityService;
use Pi\Notification\Repository\NotificationRepositoryInterface;
use Pi\Notification\Sender\Mail\LaminasMail;
use Pi\Notification\Sender\Mail\PhpMail;
use Pi\Notification\Sender\Mail\SymfonyMail;
use Pi\Notification\Sender\Push\Apns;
use Pi\Notification\Sender\Push\Fcm;
use Pi\Notification\Sender\SMS\KaveNegar;
use Pi\Notification\Sender\SMS\PayamakYab;
use Pi\Notification\Sender\SMS\Twilio;
use Pi\Notification\Service\NotificationService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotificationServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return NotificationService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NotificationService
    {
        // Get config
        $config = $container->get('config');

        return new NotificationService(
            $container->get(NotificationRepositoryInterface::class),
            $container->get(UtilityService::class),
            $container->get(LaminasMail::class),
            $container->get(SymfonyMail::class),
            $container->get(PhpMail::class),
            $container->get(Fcm::class),
            $container->get(Apns::class),
            $container->get(Twilio::class),
            $container->get(PayamakYab::class),
            $container->get(KaveNegar::class),
            $config['notification']
        );
    }
}