<?php

namespace Erm\Model;

class Rule
{
    private mixed $id;
    private mixed $target;
    private mixed $section_id;
    private mixed $mandatory_unit;
    private mixed $code;
    private mixed $user_id;
    private mixed $rule;
    private mixed $author;
    private mixed $approval_at;
    private mixed $cancellation_at;
    private mixed $promulgation_at;
    private mixed $is_creditable;
    private mixed $type;
    private mixed $category;
    private mixed $validity;
    private mixed $requirement;
    private mixed $status;

    /**
     * @param mixed $id
     * @param mixed $target
     * @param mixed $section_id
     * @param mixed $mandatory_unit
     * @param mixed $code
     * @param mixed $user_id
     * @param mixed $rule
     * @param mixed $author
     * @param mixed $approval_at
     * @param mixed $cancellation_at
     * @param mixed $promulgation_at
     * @param mixed $is_creditable
     * @param mixed $type
     * @param mixed $category
     * @param mixed $validity
     * @param mixed $requirement
     * @param mixed $status
     */
    public function __construct(mixed $id, mixed $target, mixed $section_id, mixed $mandatory_unit, mixed $code, mixed $user_id, mixed $rule, mixed $author, mixed $approval_at, mixed $cancellation_at, mixed $promulgation_at, mixed $is_creditable, mixed $type, mixed $category, mixed $validity, mixed $requirement, mixed $status)
    {
        $this->id = $id;
        $this->target = $target;
        $this->section_id = $section_id;
        $this->mandatory_unit = $mandatory_unit;
        $this->code = $code;
        $this->user_id = $user_id;
        $this->rule = $rule;
        $this->author = $author;
        $this->approval_at = $approval_at;
        $this->cancellation_at = $cancellation_at;
        $this->promulgation_at = $promulgation_at;
        $this->is_creditable = $is_creditable;
        $this->type = $type;
        $this->category = $category;
        $this->validity = $validity;
        $this->requirement = $requirement;
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
    public function getRule(): mixed
    {
        return $this->rule;
    }

    /**
     * @param mixed $rule
     */
    public function setRule(mixed $rule): void
    {
        $this->rule = $rule;
    }

    /**
     * @return mixed
     */
    public function getAuthor(): mixed
    {
        return $this->author;
    }

    /**
     * @param mixed $author
     */
    public function setAuthor(mixed $author): void
    {
        $this->author = $author;
    }

    /**
     * @return mixed
     */
    public function getApprovalAt(): mixed
    {
        return $this->approval_at;
    }

    /**
     * @param mixed $approval_at
     */
    public function setApprovalAt(mixed $approval_at): void
    {
        $this->approval_at = $approval_at;
    }

    /**
     * @return mixed
     */
    public function getCancellationAt(): mixed
    {
        return $this->cancellation_at;
    }

    /**
     * @param mixed $cancellation_at
     */
    public function setCancellationAt(mixed $cancellation_at): void
    {
        $this->cancellation_at = $cancellation_at;
    }

    /**
     * @return mixed
     */
    public function getPromulgationAt(): mixed
    {
        return $this->promulgation_at;
    }

    /**
     * @param mixed $promulgation_at
     */
    public function setPromulgationAt(mixed $promulgation_at): void
    {
        $this->promulgation_at = $promulgation_at;
    }

    /**
     * @return mixed
     */
    public function getIsCreditable(): mixed
    {
        return $this->is_creditable;
    }

    /**
     * @param mixed $is_creditable
     */
    public function setIsCreditable(mixed $is_creditable): void
    {
        $this->is_creditable = $is_creditable;
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
    public function getCategory(): mixed
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory(mixed $category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getValidity(): mixed
    {
        return $this->validity;
    }

    /**
     * @param mixed $validity
     */
    public function setValidity(mixed $validity): void
    {
        $this->validity = $validity;
    }

    /**
     * @return mixed
     */
    public function getRequirement(): mixed
    {
        return $this->requirement;
    }

    /**
     * @param mixed $requirement
     */
    public function setRequirement(mixed $requirement): void
    {
        $this->requirement = $requirement;
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