<?php

namespace Content\Model;

class Meta
{
    public mixed $id;
    public mixed $item_id;
    public mixed $meta_key;
    public mixed $value_id;
    public mixed $value_number;
    public mixed $value_string;
    public mixed $value_slug;
    public mixed $value_text;
    public mixed $status;
    public mixed $time_create;
    public mixed $time_update;
    public mixed $time_delete;
    public mixed $type;
    public mixed $option;

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
}
