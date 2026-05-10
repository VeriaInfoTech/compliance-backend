<?php

namespace Risk\Model\Form;

class Element
{
    private        $id;
    private string $code;
    private string $title;
    private string $description;
    private string $value;
    private string $type;
    private     int   $required;
    private     int   $display_order;
    private        $status;
    private        $time_create;
    private        $time_update;


    public function __construct(
        $code,
        $title,
        $description,
        $value,
        $type,
        $required,
        $display_order,
        $status = null,
        $time_create = null,
        $time_update = null,
        $id = null
    ) {
        $this->code          = $code;
        $this->title         = $title;
        $this->description   = $description;
        $this->value         = $value;
        $this->type          = $type;
        $this->required      = $required;
        $this->display_order = $display_order;
        $this->status        = $status;
        $this->time_create   = $time_create;
        $this->time_update   = $time_update;
        $this->id            = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getRequired(): ?int
    {
        return $this->required;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->display_order;
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