<?php

namespace Erm\Model;

class TaskList
{
    private mixed  $id;
    private mixed  $parent_id;
    private mixed  $type;
    private mixed $user_id;
    private mixed    $standard_id;
    private mixed    $section_id;
    private mixed $code;
    private mixed $title;
    private mixed  $description;
    private mixed  $value;
    private mixed  $rule_id;
    private mixed  $warranty_id;
    private mixed  $mandatory_unit;
    private mixed    $reference_id;
    private mixed    $has_clause;
    private mixed    $status;
    private mixed    $time_create;
    private mixed    $time_update;

    /**
     * @param mixed $id
     * @param mixed $parent_id
     * @param mixed $type
     * @param mixed $user_id
     * @param mixed $standard_id
     * @param mixed $section_id
     * @param mixed $code
     * @param mixed $title
     * @param mixed $description
     * @param mixed $value
     * @param mixed $rule_id
     * @param mixed $warranty_id
     * @param mixed $mandatory_unit
     * @param mixed $status
     * @param mixed $time_create
     * @param mixed $time_update
     */
    public function __construct(
        mixed $id, mixed $parent_id, mixed $type, mixed $user_id, mixed $standard_id, mixed $section_id, mixed $code, mixed $title, mixed $description, mixed $value, mixed $rule_id, mixed $warranty_id, mixed $mandatory_unit, mixed $status, mixed $has_clause, mixed $reference_id, mixed $time_create, mixed $time_update)
    {
        $this->id = $id;
        $this->parent_id = $parent_id;
        $this->type = $type;
        $this->user_id = $user_id;
        $this->standard_id = $standard_id;
        $this->section_id = $section_id;
        $this->code = $code;
        $this->title = $title;
        $this->description = $description;
        $this->value = $value;
        $this->rule_id = $rule_id;
        $this->warranty_id = $warranty_id;
        $this->mandatory_unit = $mandatory_unit;
        $this->status = $status;
        $this->has_clause = $has_clause;
        $this->reference_id = $reference_id;
        $this->time_update = $time_update;
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
    public function getParentId(): mixed
    {
        return $this->parent_id;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId(mixed $parent_id): void
    {
        $this->parent_id = $parent_id;
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
    public function getStandardId(): mixed
    {
        return $this->standard_id;
    }

    /**
     * @param mixed $standard_id
     */
    public function setStandardId(mixed $standard_id): void
    {
        $this->standard_id = $standard_id;
    }

    /**
     * @return mixed
     */
    public function getSectionId(): mixed
    {
        return $this->section_id;
    }

    /**
     * @param mixed $section_id
     */
    public function setSectionId(mixed $section_id): void
    {
        $this->section_id = $section_id;
    }

    /**
     * @return mixed
     */
    public function getCode(): mixed
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode(mixed $code): void
    {
        $this->code = $code;
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
    public function getDescription(): mixed
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription(mixed $description): void
    {
        $this->description = $description;
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
    public function getRuleId(): mixed
    {
        return $this->rule_id;
    }

    /**
     * @param mixed $rule_id
     */
    public function setRuleId(mixed $rule_id): void
    {
        $this->rule_id = $rule_id;
    }

    /**
     * @return mixed
     */
    public function getWarrantyId(): mixed
    {
        return $this->warranty_id;
    }

    /**
     * @param mixed $warranty_id
     */
    public function setWarrantyId(mixed $warranty_id): void
    {
        $this->warranty_id = $warranty_id;
    }

    /**
     * @return mixed
     */
    public function getMandatoryUnit(): mixed
    {
        return $this->mandatory_unit;
    }

    /**
     * @param mixed $mandatory_unit
     */
    public function setMandatoryUnit(mixed $mandatory_unit): void
    {
        $this->mandatory_unit = $mandatory_unit;
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
    public function getReferenceId(): mixed
    {
        return $this->reference_id;
    }

    /**
     * @param mixed $reference_id
     */
    public function setReferenceId(mixed $reference_id): void
    {
        $this->reference_id = $reference_id;
    }

    /**
     * @return mixed
     */
    public function getHasClause(): mixed
    {
        return $this->has_clause;
    }

    /**
     * @param mixed $has_clause
     */
    public function setHasClause(mixed $has_clause): void
    {
        $this->has_clause = $has_clause;
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



}