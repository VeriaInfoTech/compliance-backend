<?php

namespace Erm\Model;

class TaskSection
{
    private mixed $id;
    private mixed $slug;
    private int $standard_id;
    private mixed $type;
    private int $parent_id;
    private string $code;
    private string $title;
    private int $status;
    private int $time_create;
    private int $time_update;

    /**
     * @param mixed $id
     * @param mixed $slug
     * @param int $standard_id
     * @param mixed $type
     * @param int $parent_id
     * @param string $code
     * @param string $title
     * @param int $status
     * @param int $time_create
     * @param int $time_update
     */
    public function __construct(mixed $id, mixed $slug, int $standard_id, mixed $type, int $parent_id, string $code, string $title, int $status, int $time_create, int $time_update)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->standard_id = $standard_id;
        $this->type = $type;
        $this->parent_id = $parent_id;
        $this->code = $code;
        $this->title = $title;
        $this->status = $status;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
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
     * @return int
     */
    public function getStandardId(): int
    {
        return $this->standard_id;
    }

    /**
     * @param int $standard_id
     */
    public function setStandardId(int $standard_id): void
    {
        $this->standard_id = $standard_id;
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
     * @return int
     */
    public function getParentId(): int
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId(int $parent_id): void
    {
        $this->parent_id = $parent_id;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    /**
     * @param int $time_create
     */
    public function setTimeCreate(int $time_create): void
    {
        $this->time_create = $time_create;
    }

    /**
     * @return int
     */
    public function getTimeUpdate(): int
    {
        return $this->time_update;
    }

    /**
     * @param int $time_update
     */
    public function setTimeUpdate(int $time_update): void
    {
        $this->time_update = $time_update;
    }


}