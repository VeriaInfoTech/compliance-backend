<?php

namespace Erm\Model;

class TaskAudit
{
    private mixed  $id;
    private string $slug;
    private int    $standard_id;
    private int    $section_id;
    private int    $task_id;
    private int    $user_id;
    private int    $company_id;
    private int    $time_create;
    private int    $time_update;
    private string $level;
    private int    $answer_score;
    private string $answer_value;
    private mixed  $answer_note;

    public function __construct(
        $slug,
        $standard_id,
        $section_id,
        $task_id,
        $user_id,
        $company_id,
        $time_create,
        $time_update,
        $level,
        $answer_score,
        $answer_value,
        $answer_note,
        $id = null
    ) {
        $this->id           = $id;
        $this->slug         = $slug;
        $this->standard_id  = $standard_id;
        $this->section_id   = $section_id;
        $this->task_id      = $task_id;
        $this->user_id      = $user_id;
        $this->company_id   = $company_id;
        $this->time_create  = $time_create;
        $this->time_update  = $time_update;
        $this->level        = $level;
        $this->answer_score = $answer_score;
        $this->answer_value = $answer_value;
        $this->answer_note  = $answer_note;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getStandardId(): int
    {
        return $this->standard_id;
    }

    public function getSectionId(): int
    {
        return $this->section_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    public function getTimeCreate(): int
    {
        return $this->time_create;
    }

    public function getTimeUpdate(): int
    {
        return $this->time_update;
    }

    public function getLevel(): string
    {
        return $this->level;
    }

    public function getAnswerScore(): int
    {
        return $this->answer_score;
    }

    public function getAnswerValue(): string
    {
        return $this->answer_value;
    }

    public function getAnswerNote(): ?string
    {
        return $this->answer_note;
    }
}