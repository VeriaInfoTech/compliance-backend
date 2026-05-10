<?php

namespace Pi\Notification\Service;

use PHPMailer\PHPMailer\Exception;
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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class NotificationService implements ServiceInterface
{
    /* @var NotificationRepositoryInterface */
    protected NotificationRepositoryInterface $notificationRepository;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /** @var LaminasMail */
    protected LaminasMail $laminasMailSender;

    /** @var SymfonyMail */
    protected SymfonyMail $symfonyMailSender;

    /** @var PhpMail */
    protected PhpMail $mailerSender;

    /** @var Fcm */
    protected Fcm $fcmSender;

    /** @var Apns */
    protected Apns $apnsSender;

    /** @var Twilio */
    protected Twilio $twilioSender;

    /** @var PayamakYab */
    protected PayamakYab $payamakYabSender;

    /** @var KaveNegar */
    protected KaveNegar $kaveNegarSender;

    /* @var array */
    protected array $config;

    /* @var string */
    private string $mailSender = 'phpMailer';

    /* @var string */
    private string $smsSender = 'twilio';

    /* @var string */
    private string $pushSender = 'fcm';

    /**
     * @param NotificationRepositoryInterface $notificationRepository
     * @param UtilityService                  $utilityService
     * @param LaminasMail                     $laminasMailSender
     * @param SymfonyMail                     $symfonyMailSender
     * @param PhpMail                         $mailerSender
     * @param Fcm                             $fcmSender
     * @param Apns                            $apnsSender
     * @param Twilio                          $twilioSender
     * @param PayamakYab                      $payamakYabSender
     * @param KaveNegar                       $kaveNegarSender
     * @param                                 $config
     */
    public function __construct(
        NotificationRepositoryInterface $notificationRepository,
        UtilityService                  $utilityService,
        LaminasMail                     $laminasMailSender,
        SymfonyMail                     $symfonyMailSender,
        PhpMail                         $mailerSender,
        Fcm                             $fcmSender,
        Apns                            $apnsSender,
        Twilio                          $twilioSender,
        PayamakYab                      $payamakYabSender,
        KaveNegar                       $kaveNegarSender,
                                        $config
    ) {
        $this->notificationRepository = $notificationRepository;
        $this->utilityService         = $utilityService;
        $this->laminasMailSender      = $laminasMailSender;
        $this->symfonyMailSender      = $symfonyMailSender;
        $this->mailerSender           = $mailerSender;
        $this->fcmSender              = $fcmSender;
        $this->apnsSender             = $apnsSender;
        $this->twilioSender           = $twilioSender;
        $this->payamakYabSender       = $payamakYabSender;
        $this->kaveNegarSender        = $kaveNegarSender;
        $this->config                 = $config;
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function getNotificationList($params): array
    {
        // Get notifications list
        $limit  = $params['limit'] ?? 25;
        $page   = $params['page'] ?? 1;
        $order  = $params['order'] ?? ['time_create DESC', 'id DESC'];
        $offset = ($page - 1) * $limit;

        // Set params
        $listParams = [
            'page'   => $page,
            'limit'  => $limit,
            'order'  => $order,
            'offset' => $offset,
            'status' => 1,
        ];

        if (isset($params['user_id']) && !empty($params['user_id'])) {
            $listParams['user_id'] = $params['user_id'];
        }

        // Get list
        $list   = [];
        $rowSet = $this->notificationRepository->getNotificationList($listParams);
        foreach ($rowSet as $row) {
            $list[] = $this->canonizeNotification($row);
        }

        // Get count
        $count = $this->notificationRepository->getNotificationCount($listParams);

        return [
            'result' => true,
            'data'   => [
                'list'      => $list,
                'paginator' => [
                    'count' => $count,
                    'limit' => $limit,
                    'page'  => $page,
                ],
            ],
            'error'  => new \stdClass(),
        ];
    }

    /**
     * @param string $parameter
     * @param string $type
     *
     * @return array
     */
    public function getNotification(string $parameter, string $type = 'id'): array
    {
        $notification = $this->notificationRepository->getNotification($parameter, $type);
        return $this->canonizeNotification($notification);
    }

    /**
     * @param $params
     *
     * @return int
     */
    public function getNotCount($params): array
    {
        return [
            'result' => true,
            'data'   => [
                'count'  => $this->notificationRepository->getNotificationCount(
                    [
                        'user_id' => $params['user_id'],
                        'status'  => 1,
                    ]
                ),
                'unread' => $this->notificationRepository->getUnreadNotificationCount(
                    [
                        'user_id' => $params['user_id'],
                        'status'  => 1,
                    ]
                ),
            ],
            'error'  => [],
        ];
    }

    /**
     * @param $notification
     */
    public function updateView($notification): void
    {
        // Set params
        $updateParams = [
            'viewed' => 1,
        ];

        $this->notificationRepository->updateNotification((int)$notification['id'], $updateParams);
    }

    /**
     * @param $notification
     *
     * @return array
     */
    public function canonizeNotification($notification): array
    {
        if (empty($notification)) {
            return [];
        }

        if (is_object($notification)) {
            $notification = [
                'id'          => (int)$notification->getId(),
                'company_id'  => $notification->getCompanyId(),
                'sender_id'   => $notification->getSenderId(),
                'receiver_id' => $notification->getReceiverId(),
                'type'        => $notification->getType(),
                'status'      => $notification->getStatus(),
                'viewed'      => $notification->getViewed(),
                'time_create' => $notification->getTimeCreate(),
                'time_update' => $notification->getTimeUpdate(),
                'sent'        => $notification->getSent(),
                'information' => $notification->getInformation(),
            ];
        } else {
            $notification = [
                'id'          => (int)$notification['id'],
                'company_id'  => $notification['company_id'],
                'sender_id'   => $notification['sender_id'],
                'receiver_id' => $notification['receiver_id'],
                'type'        => $notification['type'],
                'status'      => $notification['status'],
                'viewed'      => $notification['viewed'],
                'time_create' => $notification['time_create'],
                'time_update' => $notification['time_update'],
                'sent'        => $notification['sent'],
                'information' => $notification['information'],
            ];
        }

        // Set time view
        $notification['time_create_view'] = $this->utilityService->date($notification['time_create']);
        $notification['time_update_view'] = $this->utilityService->date($notification['time_update']);

        // ToDo: Check it for change key or delete
        $notification['time_create'] = date('Y M d H:i:s', $notification['time_create']);

        // Set information
        $information = (!empty($notification['information'])) ? json_decode($notification['information'], true) : [];
        unset($notification['information']);

        return array_merge($notification, $information);
    }

    /**
     * @param        $params
     * @param string $section
     *
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function send($params, string $section = 'customer'): void
    {
        // Set sender
        $this->mailSender = $params['mail_sender'] ?? $this->config['defaults']['mail'];
        $this->smsSender  = $params['sms_sender'] ?? $this->config['defaults']['sms'];
        $this->pushSender = $params['push_sender'] ?? $this->config['defaults']['push'];

        // Send a mail notification
        if (isset($params['mail']) && !empty($params['mail'])) {
            $this->sendMail($params['mail']);
        }

        // Send a sms notification
        if (isset($params['sms']) && !empty($params['sms'])) {
            $this->sendSms($params['sms']);
        }

        // Send a push notification
        if (isset($params['push']) && !empty($params['push'])) {
            $this->sendPush($section, $params['push']);
        }

        // Save to DB
        if (isset($params['information']) && !empty($params['information'])) {
            // Set params
            $addParams = [
                'company_id'  => $params['information']['company_id'] ?? 0,
                'sender_id'   => $params['information']['sender_id'] ?? 0,
                'receiver_id' => $params['information']['receiver_id'],
                'type'        => $params['information']['type'],
                'viewed'      => 0,
                'sent'        => 1,
                'time_create' => time(),
                'time_update' => time(),
                'information' => json_encode($params['information'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ];

            if (isset($params['information']['viewed'])) {
                $addParams['viewed'] = $params['information']['viewed'];
            }

            // Add notification to DB
            $this->notificationRepository->addNotification($addParams);
        }
    }

    /**
     * @param $params
     *
     * @return array
     * @throws Exception
     */
    public function middleSend($params): array
    {
        $notificationParams = [
            'information' =>
                [
                    'device_token' => '/topics/global',
                    'in_app'       => false,
                    'in_app_title' => $params['title'],
                    'title'        => $params['title'],
                    'in_app_body'  => $params['message'],
                    'body'         => $params['message'],
                    'event'        => 'global',
                    'user_id'      => (int)$params['user_id'],
                    'item_id'      => 0,
                    'viewed'       => 1,
                    'sender_id'    => $params['user_id'],
                    'type'         => 'global',
                    'image_url'    => '',
                    'receiver_id'  => 0,
                ],
        ];

        $notificationParams['push'] = $notificationParams['information'];
        $this->send($notificationParams, 'customer');

        return [
            'result' => true,
        ];
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    protected function sendMail($mailParams): void
    {
        switch ($this->mailSender) {
            default:
            case 'phpMailer':
                $this->mailerSender->send($this->config['mail'], $mailParams);
                break;

            case 'laminasMail':
                $this->laminasMailSender->send($this->config['mail'], $mailParams);
                break;

            case 'symfonyMail':
                $this->symfonyMailSender->send($this->config['mail'], $mailParams);
                break;
        }
    }

    protected function sendSms($smsParams): void
    {
        switch ($this->smsSender) {
            default:
            case 'twilio':
                $this->twilioSender->send($this->config['sms'], $smsParams);
                break;

            case 'payamakyab':
                $this->payamakYabSender->send($this->config['sms'], $smsParams);
                break;

            case 'kavenegar':
                $this->kaveNegarSender->send($this->config['sms'], $smsParams);
                break;
        }
    }

    protected function sendPush($section, $pushParams): void
    {
        switch ($this->pushSender) {
            default:
            case 'fcm':
                $this->fcmSender->send($this->config['push'][$section], $pushParams);
                break;

            case 'apns':
                $this->apnsSender->send($this->config['push'][$section], $pushParams);
                break;
        }
    }
}
