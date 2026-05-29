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

    public function getId(): mixed
    {
        return $this->id;
    }

    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getKey(): mixed
    {
        return $this->key;
    }

    public function setKey(mixed $key): void
    {
        $this->key = $key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    public function getTarget(): mixed
    {
        return $this->target;
    }

    public function setTarget(mixed $target): void
    {
        $this->target = $target;
    }

    public function getType(): mixed
    {
        return $this->type;
    }

    public function setType(mixed $type): void
    {
        $this->type = $type;
    }

    public function getSuffix(): mixed
    {
        return $this->suffix;
    }

    public function setSuffix(mixed $suffix): void
    {
        $this->suffix = $suffix;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }

    public function setOption(mixed $option): void
    {
        $this->option = $option;
    }

    public function getLogo(): mixed
    {
        return $this->logo;
    }

    public function setLogo(mixed $logo): void
    {
        $this->logo = $logo;
    }

    public function getStatus(): mixed
    {
        return $this->status;
    }

    public function setStatus(mixed $status): void
    {
        $this->status = $status;
    }
}
