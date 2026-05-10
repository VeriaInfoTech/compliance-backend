<?php

namespace Erm\Model;

class ErmMeta
{

    private mixed $id;
    private mixed $user_id;
    private mixed $target;
    private mixed $type;
    private mixed $slug;
    private mixed $value;
    private mixed $title;
    private mixed $status;
    private mixed $information;
    private mixed $time_create;
    private mixed $time_update;
    private mixed $time_delete;

    /**
     * @param mixed $id
     * @param mixed $user_id
     * @param mixed $target
     * @param mixed $type
     * @param mixed $slug
     * @param mixed $value
     * @param mixed $title
     * @param mixed $status
     * @param mixed $information
     * @param mixed $time_create
     * @param mixed $time_update
     * @param mixed $time_delete
     */
    public function __construct(mixed $id, mixed $user_id, mixed $target, mixed $type, mixed $slug, mixed $value, mixed $title, mixed $status, mixed $information, mixed $time_create, mixed $time_update, mixed $time_delete)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->target = $target;
        $this->type = $type;
        $this->slug = $slug;
        $this->value = $value;
        $this->title = $title;
        $this->status = $status;
        $this->information = $information;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->time_delete = $time_delete;
    }

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getUserId(): mixed
    {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId(mixed $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getTarget(): mixed
    {
        return $this->target;
    }

    /**
     * @param mixed $target
     */
    public function setTarget(mixed $target): void
    {
        $this->target = $target;
    }

    /**
     * @return mixed
     */
    public function getType(): mixed
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType(mixed $type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getSlug(): mixed
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug(mixed $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getTitle(): mixed
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle(mixed $title): void
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus(mixed $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getInformation(): mixed
    {
        return $this->information;
    }

    /**
     * @param mixed $information
     */
    public function setInformation(mixed $information): void
    {
        $this->information = $information;
    }

    /**
     * @return mixed
     */
    public function getTimeCreate(): mixed
    {
        return $this->time_create;
    }

    /**
     * @param mixed $time_create
     */
    public function setTimeCreate(mixed $time_create): void
    {
        $this->time_create = $time_create;
    }

    /**
     * @return mixed
     */
    public function getTimeUpdate(): mixed
    {
        return $this->time_update;
    }

    /**
     * @param mixed $time_update
     */
    public function setTimeUpdate(mixed $time_update): void
    {
        $this->time_update = $time_update;
    }

    /**
     * @return mixed
     */
    public function getTimeDelete(): mixed
    {
        return $this->time_delete;
    }

    /**
     * @param mixed $time_delete
     */
    public function setTimeDelete(mixed $time_delete): void
    {
        $this->time_delete = $time_delete;
    }


}