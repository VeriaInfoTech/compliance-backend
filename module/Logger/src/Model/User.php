<?php

namespace Pi\Logger\Model;

class User
{
    private mixed  $id;
    private int    $user_id;
    private int    $operator_id;
    private int    $time_create;
    private string $state;
    private string $information;
    private mixed  $user_identity;
    private mixed  $user_name;
    private mixed  $user_email;
    private mixed  $user_mobile;
    private mixed  $operator_identity;
    private mixed  $operator_name;
    private mixed  $operator_email;
    private mixed  $operator_mobile;

    public function __construct(
        $user_id,
        $operator_id,
        $time_create,
        $state,
        $information,
        $user_identity = null,
        $user_name = null,
        $user_email = null,
        $user_mobile = null,
        $operator_identity = null,
        $operator_name = null,
        $operator_email = null,
        $operator_mobile = null,
        $id = null
    ) {
        $this->user_id           = $user_id;
        $this->operator_id       = $operator_id;
        $this->time_create       = $time_create;
        $this->state             = $state;
        $this->information       = $information;
        $this->user_identity     = $user_identity;
        $this->user_name         = $user_name;
        $this->user_email        = $user_email;
        $this->user_mobile       = $user_mobile;
        $this->operator_identity = $operator_identity;
        $this->operator_name     = $operator_name;
        $this->operator_email    = $operator_email;
        $this->operator_mobile   = $operator_mobile;
        $this->id                = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @return int
     */
    public function getOperatorId(): int
    {
        return $this->operator_id;
    }

    /**
     * @param int $operator_id
     */
    public function setOperatorId(int $operator_id): void
    {
        $this->operator_id = $operator_id;
    }


    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    public function getState(): string
    {
        return $this->state;
    }

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

    public function getOperatorIdentity(): ?string
    {
        return $this->operator_identity;
    }

    public function getOperatorName(): ?string
    {
        return $this->operator_name;
    }

    public function getOperatorEmail(): ?string
    {
        return $this->operator_email;
    }

    public function getOperatorMobile(): ?string
    {
        return $this->operator_mobile;
    }
}