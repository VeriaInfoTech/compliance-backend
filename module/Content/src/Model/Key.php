<?php

namespace Content\Model;

class Key
{
    private mixed $id;
    private mixed $key;
    private mixed $value;
    private mixed $target;
    private mixed $type;
    private mixed $suffix;
    private mixed $option;
    private mixed $logo;
    private mixed $status;

    /**
     * @param mixed $id
     * @param mixed $key
     * @param mixed $value
     * @param mixed $target
     * @param mixed $type
     * @param mixed $suffix
     * @param mixed $option
     * @param mixed $logo
     * @param mixed $status
     */
    public function __construct(mixed $id, mixed $key, mixed $value, mixed $target, mixed $type, mixed $suffix, mixed $option, mixed $logo, mixed $status)
    {
        $this->id = $id;
        $this->key = $key;
        $this->value = $value;
        $this->target = $target;
        $this->type = $type;
        $this->suffix = $suffix;
        $this->option = $option;
        $this->logo = $logo;
        $this->status = $status;
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
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey(mixed $key): void
    {
        $this->key = $key;
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
    public function getSuffix(): mixed
    {
        return $this->suffix;
    }

    /**
     * @param mixed $suffix
     */
    public function setSuffix(mixed $suffix): void
    {
        $this->suffix = $suffix;
    }

    /**
     * @return mixed
     */
    public function getOption(): mixed
    {
        return $this->option;
    }

    /**
     * @param mixed $option
     */
    public function setOption(mixed $option): void
    {
        $this->option = $option;
    }

    /**
     * @return mixed
     */
    public function getLogo(): mixed
    {
        return $this->logo;
    }

    /**
     * @param mixed $logo
     */
    public function setLogo(mixed $logo): void
    {
        $this->logo = $logo;
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



}