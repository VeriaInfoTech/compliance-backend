<?php

namespace Pi\Logger\Model;

class History
{
    private mixed  $id;
    private int    $user_id;
    private int    $company_id;
    private string $relation_module;
    private string $relation_section;
    private int    $relation_item;
    private int    $time_create;
    private string $state;
    private string $information;
    private mixed  $user_identity;
    private mixed  $user_name;
    private mixed  $user_email;
    private mixed  $user_mobile;

    public function __construct(
        $user_id,
        $company_id,
        $relation_module,
        $relation_section,
        $relation_item,
        $time_create,
        $state,
        $information,
        $user_identity = null,
        $user_name = null,
        $user_email = null,
        $user_mobile = null,
        $id = null
    ) {
        $this->user_id          = $user_id;
        $this->company_id       = $company_id;
        $this->relation_module  = $relation_module;
        $this->relation_section = $relation_section;
        $this->relation_item    = $relation_item;
        $this->time_create      = $time_create;
        $this->state            = $state;
        $this->information      = $information;
        $this->user_identity    = $user_identity;
        $this->user_name        = $user_name;
        $this->user_email       = $user_email;
        $this->user_mobile      = $user_mobile;
        $this->id               = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    public function getRelationModule(): string
    {
        return $this->relation_module;
    }

    public function getRelationSection(): string
    {
        return $this->relation_section;
    }

    public function getRelationItem(): int
    {
        return $this->relation_item;
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
}