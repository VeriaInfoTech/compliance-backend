<?php

namespace Risk\Model\Form;

class Record
{
    private     $id;
    private int $user_id;
    private int $company_id;
    private int $form_id;
    private     $status;
    private     $time_create;
    private     $time_update;

    public function __construct(
        $user_id,
        $company_id,
        $form_id,
        $status = null,
        $time_create = null,
        $time_update = null,
        $id = null
    ) {
        $this->user_id     = $user_id;
        $this->company_id  = $company_id;
        $this->form_id     = $form_id;
        $this->status      = $status;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->id          = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getCompanyId(): ?int
    {
        return $this->company_id;
    }

    public function getFormId(): ?int
    {
        return $this->form_id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getTimeCreate(): ?int
    {
        return $this->time_create;
    }

    public function getTimeUpdate(): ?int
    {
        return $this->time_update;
    }
}