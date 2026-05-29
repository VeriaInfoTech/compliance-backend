<?php

namespace Content\Model;

class Meta
{
    private mixed $id;
    private mixed $item_id;
    private mixed $meta_key;
    private mixed $value_id;
    private mixed $value_number;
    private mixed $value_string;
    private mixed $value_slug;
    private mixed $value_text;
    private mixed $status;
    private mixed $time_create;
    private mixed $time_update;
    private mixed $time_delete;
    private mixed $type;
    private mixed $option;

    public function __construct(
        mixed $id,
        mixed $item_id,
        mixed $meta_key,
        mixed $value_id,
        mixed $value_number,
        mixed $value_string,
        mixed $value_slug,
        mixed $value_text,
        mixed $status,
        mixed $time_create,
        mixed $time_update,
        mixed $time_delete,
        mixed $type,
        mixed $option
    ) {
        $this->id = $id;
        $this->item_id = $item_id;
        $this->meta_key = $meta_key;
        $this->value_id = $value_id;
        $this->value_number = $value_number;
        $this->value_string = $value_string;
        $this->value_slug = $value_slug;
        $this->value_text = $value_text;
        $this->status = $status;
        $this->time_create = $time_create;
        $this->time_update = $time_update;
        $this->time_delete = $time_delete;
        $this->type = $type;
        $this->option = $option;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getItemId(): mixed
    {
        return $this->item_id;
    }

    public function getMetaKey(): mixed
    {
        return $this->meta_key;
    }

    public function getValueId(): mixed
    {
        return $this->value_id;
    }

    public function getValueNumber(): mixed
    {
        return $this->value_number;
    }

    public function getValueString(): mixed
    {
        return $this->value_string;
    }

    public function getValueSlug(): mixed
    {
        return $this->value_slug;
    }

    public function getValueText(): mixed
    {
        return $this->value_text;
    }

    public function getStatus(): mixed
    {
        return $this->status;
    }

    public function getTimeCreate(): mixed
    {
        return $this->time_create;
    }

    public function getTimeUpdate(): mixed
    {
        return $this->time_update;
    }

    public function getTimeDelete(): mixed
    {
        return $this->time_delete;
    }

    public function getType(): mixed
    {
        return $this->type;
    }

    public function getOption(): mixed
    {
        return $this->option;
    }
}
