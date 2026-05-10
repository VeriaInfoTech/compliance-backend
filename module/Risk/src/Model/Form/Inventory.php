<?php

namespace Risk\Model\Form;

class Inventory
{
    private        $id;
    private string $title;
    private string $slug;
    private        $status;
    private        $time_create;
    private        $time_update;

    public function __construct(
        $title,
        $slug,
        $status = null,
        $time_create = null,
        $time_update = null,
        $id = null
    ) {
        $this->title       = $title;
        $this->slug        = $slug;
        $this->status      = $status;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->id          = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getSlug(): string
    {
        return $this->slug;
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