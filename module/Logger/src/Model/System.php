<?php

namespace Pi\Logger\Model;

class System
{
    private mixed  $id;
    private string $path;
    private string $message;
    private int    $priority;
    private string $level;
    private int    $user_id;
    private int    $company_id;
    private string $timestamp;
    private int    $time_create;
    private string $information;
    private mixed  $user_identity;
    private mixed  $user_name;
    private mixed  $user_email;
    private mixed  $user_mobile;

    /**
     * @param string      $path
     * @param string      $message
     * @param int         $priority
     * @param string      $level
     * @param int         $user_id
     * @param int         $company_id
     * @param string      $timestamp
     * @param int         $time_create
     * @param string      $information
     * @param string|null $user_identity
     * @param string|null $user_name
     * @param string|null $user_email
     * @param string|null $user_mobile
     * @param mixed|null  $id
     */
    public function __construct(
        string $path,
        string $message,
        int    $priority,
        string $level,
        int    $user_id,
        int    $company_id,
        string $timestamp,
        int    $time_create,
        string $information,
        string $user_identity = null,
        string $user_name = null,
        string $user_email = null,
        string $user_mobile = null,
        mixed  $id = null
    ) {
        $this->id            = $id;
        $this->path          = $path;
        $this->message       = $message;
        $this->priority      = $priority;
        $this->level         = $level;
        $this->user_id       = $user_id;
        $this->company_id    = $company_id;
        $this->timestamp     = $timestamp;
        $this->time_create   = $time_create;
        $this->information   = $information;
        $this->user_identity = $user_identity;
        $this->user_name     = $user_name;
        $this->user_email    = $user_email;
        $this->user_mobile   = $user_mobile;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return $this->timestamp;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
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
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @return string
     */
    public function getInformation(): string
    {
        return $this->information;
    }

    public function getUserIdentity(): ?string
    {
        return $this->user_identity;
    }

    public function getUserName(): ?string
    {
        return $this->user_name;
    }

    public function getUserEmail(): ?string
    {
        return $this->user_email;
    }

    public function getUserMobile(): ?string
    {
        return $this->user_mobile;
    }
}