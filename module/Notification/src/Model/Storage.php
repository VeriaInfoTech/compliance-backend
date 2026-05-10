<?php

namespace Pi\Notification\Model;

class Storage
{
    private mixed  $id;
    private int    $company_id;
    private int    $sender_id;
    private int    $receiver_id;
    private int    $status;
    private int    $viewed;
    private int    $sent;
    private int    $time_create;
    private int    $time_update;
    private string $type;
    private string $information;

    /**
     * @param int    $company_id
     * @param int    $sender_id
     * @param int    $receiver_id
     * @param int    $status
     * @param int    $viewed
     * @param int    $sent
     * @param int    $time_create
     * @param int    $time_update
     * @param string $type
     * @param string $information
     * @param mixed  $id
     */
    public function __construct(
        int    $company_id,
        int    $sender_id,
        int    $receiver_id,
        int    $status,
        int    $viewed,
        int    $sent,
        int    $time_create,
        int    $time_update,
        string $type,
        string $information,
        mixed  $id
    ) {
        $this->id          = $id;
        $this->company_id  = $company_id;
        $this->sender_id   = $sender_id;
        $this->receiver_id = $receiver_id;
        $this->status      = $status;
        $this->viewed      = $viewed;
        $this->sent        = $sent;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->type        = $type;
        $this->information = $information;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @return int
     */
    public function getSenderId(): int
    {
        return $this->sender_id;
    }

    /**
     * @return int
     */
    public function getReceiverId(): int
    {
        return $this->receiver_id;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getViewed(): int
    {
        return $this->viewed;
    }

    /**
     * @return int
     */
    public function getSent(): int
    {
        return $this->sent;
    }

    /**
     * @return int
     */
    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    /**
     * @return int
     */
    public function getTimeUpdate(): int
    {
        return $this->time_update;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getInformation(): string
    {
        return $this->information;
    }
}