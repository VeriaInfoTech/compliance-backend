<?php

namespace Erm\Model;

/**
 *
 */
class TaskProgress
{
    /**
     * @var mixed
     */
    private mixed $id;
    /**
     * @var string
     */
    private string $slug;
    /**
     * @var mixed
     */
    private mixed $type;
    /**
     * @var mixed
     */
    private mixed $parent_id;
    /**
     * @var int
     */
    private int $standard_id;
    /**
     * @var int
     */
    private int $section_id;
    /**
     * @var int
     */
    private int $task_id;
    /**
     * @var int
     */
    private int $user_id;
    /**
     * @var int
     */
    private int $assigner_id;
    /**
     * @var int
     */
    private int $company_id;
    /**
     * @var string
     */
    private string $level;
    /**
     * @var string
     */
    private string $status;
    /**
     * @var int
     */
    private int $answer_score;
    /**
     * @var string
     */
    private string $answer_value;
    /**
     * @var mixed
     */
    private mixed $answer_note;
    /**
     * @var mixed
     */
    private mixed $history;
    /**
     * @var int
     */
    private int $time_deadline;
    private mixed $information;
    /**
     * @var int
     */
    private int $time_create;
    /**
     * @var int
     */
    private int $time_update;

    /**
     * @param mixed $id
     * @param string $slug
     * @param mixed $type
     * @param mixed $parent_id
     * @param int $standard_id
     * @param int $section_id
     * @param int $task_id
     * @param int $user_id
     * @param int $assigner_id
     * @param int $company_id
     * @param string $level
     * @param string $status
     * @param int $answer_score
     * @param string $answer_value
     * @param mixed $answer_note
     * @param mixed $history
     * @param int $time_deadline
     * @param mixed $information
     * @param int $time_create
     * @param int $time_update
     */
    public function __construct(mixed $id, string $slug, mixed $type, mixed $parent_id, int $standard_id, int $section_id, int $task_id, int $user_id, int $assigner_id, int $company_id, string $level, string $status, int $answer_score, string $answer_value, mixed $answer_note, mixed $history, int $time_deadline, mixed $information, int $time_create, int $time_update)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->type = $type;
        $this->parent_id = $parent_id;
        $this->standard_id = $standard_id;
        $this->section_id = $section_id;
        $this->task_id = $task_id;
        $this->user_id = $user_id;
        $this->assigner_id = $assigner_id;
        $this->company_id = $company_id;
        $this->level = $level;
        $this->status = $status;
        $this->answer_score = $answer_score;
        $this->answer_value = $answer_value;
        $this->answer_note = $answer_note;
        $this->history = $history;
        $this->time_deadline = $time_deadline;
        $this->information = $information;
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
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
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
     * @return int
     */
    public function getSectionId(): int
    {
        return $this->section_id;
    }

    /**
     * @param int $section_id
     */
    public function setSectionId(int $section_id): void
    {
        $this->section_id = $section_id;
    }

    /**
     * @return int
     */
    public function getTaskId(): int
    {
        return $this->task_id;
    }

    /**
     * @param int $task_id
     */
    public function setTaskId(int $task_id): void
    {
        $this->task_id = $task_id;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return int
     */
    public function getAssignerId(): int
    {
        return $this->assigner_id;
    }

    /**
     * @param int $assigner_id
     */
    public function setAssignerId(int $assigner_id): void
    {
        $this->assigner_id = $assigner_id;
    }

    /**
     * @return int
     */
    public function getCompanyId(): int
    {
        return $this->company_id;
    }

    /**
     * @param int $company_id
     */
    public function setCompanyId(int $company_id): void
    {
        $this->company_id = $company_id;
    }

    /**
     * @return string
     */
    public function getLevel(): string
    {
        return $this->level;
    }

    /**
     * @param string $level
     */
    public function setLevel(string $level): void
    {
        $this->level = $level;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getAnswerScore(): int
    {
        return $this->answer_score;
    }

    /**
     * @param int $answer_score
     */
    public function setAnswerScore(int $answer_score): void
    {
        $this->answer_score = $answer_score;
    }

    /**
     * @return string
     */
    public function getAnswerValue(): string
    {
        return $this->answer_value;
    }

    /**
     * @param string $answer_value
     */
    public function setAnswerValue(string $answer_value): void
    {
        $this->answer_value = $answer_value;
    }

    /**
     * @return mixed
     */
    public function getAnswerNote(): mixed
    {
        return $this->answer_note;
    }

    /**
     * @param mixed $answer_note
     */
    public function setAnswerNote(mixed $answer_note): void
    {
        $this->answer_note = $answer_note;
    }

    /**
     * @return mixed
     */
    public function getHistory(): mixed
    {
        return $this->history;
    }

    /**
     * @param mixed $history
     */
    public function setHistory(mixed $history): void
    {
        $this->history = $history;
    }

    /**
     * @return int
     */
    public function getTimeDeadline(): int
    {
        return $this->time_deadline;
    }

    /**
     * @param int $time_deadline
     */
    public function setTimeDeadline(int $time_deadline): void
    {
        $this->time_deadline = $time_deadline;
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