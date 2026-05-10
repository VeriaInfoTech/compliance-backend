<?php

namespace Erm\Model;

class TaskRisk
{
    private mixed $id;
    private mixed $slug;
    private mixed $type;
    private mixed $parent_id;
    private mixed $assigner_id;
    private mixed $standard_id;
    private mixed $section_id;
    private mixed $task_id;
    private mixed $rule_id;
    private mixed $warranty_id;
    private mixed $user_id;
    private mixed $company_id;
    private mixed $level;
    private mixed $control;
    private mixed $risk_intensity;
    private mixed $risk_effect;
    private mixed $risk_data;
    private mixed $risk_threat;
    private mixed $risk_damage;
    private mixed $risk_response_type;
    private mixed $risk_execution_percent;
    private mixed $risk_proposed_action;
    private mixed $risk_scenario;
    private mixed $status;
    private mixed $history;
    private mixed $time_deadline;
    private mixed $time_create;
    private mixed $time_update;
    private mixed $time_delete;

    /**
     * @param mixed $id
     * @param mixed $slug
     * @param mixed $type
     * @param mixed $parent_id
     * @param mixed $assigner_id
     * @param mixed $standard_id
     * @param mixed $section_id
     * @param mixed $task_id
     * @param mixed $rule_id
     * @param mixed $warranty_id
     * @param mixed $user_id
     * @param mixed $company_id
     * @param mixed $level
     * @param mixed $control
     * @param mixed $risk_intensity
     * @param mixed $risk_effect
     * @param mixed $risk_data
     * @param mixed $risk_threat
     * @param mixed $risk_damage
     * @param mixed $risk_response_type
     * @param mixed $risk_execution_percent
     * @param mixed $risk_proposed_action
     * @param mixed $risk_scenario
     * @param mixed $status
     * @param mixed $history
     * @param mixed $time_deadline
     * @param mixed $time_create
     * @param mixed $time_update
     * @param mixed $time_delete
     */
    public function __construct(mixed $id, mixed $slug, mixed $type, mixed $parent_id, mixed $assigner_id, mixed $standard_id, mixed $section_id, mixed $task_id, mixed $rule_id, mixed $warranty_id, mixed $user_id, mixed $company_id, mixed $level, mixed $control, mixed $risk_intensity, mixed $risk_effect, mixed $risk_data, mixed $risk_threat, mixed $risk_damage, mixed $risk_response_type, mixed $risk_execution_percent, mixed $risk_proposed_action, mixed $risk_scenario, mixed $status, mixed $history, mixed $time_deadline, mixed $time_create, mixed $time_update, mixed $time_delete)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->type = $type;
        $this->parent_id = $parent_id;
        $this->assigner_id = $assigner_id;
        $this->standard_id = $standard_id;
        $this->section_id = $section_id;
        $this->task_id = $task_id;
        $this->rule_id = $rule_id;
        $this->warranty_id = $warranty_id;
        $this->user_id = $user_id;
        $this->company_id = $company_id;
        $this->level = $level;
        $this->control = $control;
        $this->risk_intensity = $risk_intensity;
        $this->risk_effect = $risk_effect;
        $this->risk_data = $risk_data;
        $this->risk_threat = $risk_threat;
        $this->risk_damage = $risk_damage;
        $this->risk_response_type = $risk_response_type;
        $this->risk_execution_percent = $risk_execution_percent;
        $this->risk_proposed_action = $risk_proposed_action;
        $this->risk_scenario = $risk_scenario;
        $this->status = $status;
        $this->history = $history;
        $this->time_deadline = $time_deadline;
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
     * @return string
     */
    public function getType(): mixed
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
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
     * @return mixed
     */
    public function getAssignerId(): mixed
    {
        return $this->assigner_id;
    }

    /**
     * @param mixed $assigner_id
     */
    public function setAssignerId(mixed $assigner_id): void
    {
        $this->assigner_id = $assigner_id;
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
    public function getTaskId(): mixed
    {
        return $this->task_id;
    }

    /**
     * @param mixed $task_id
     */
    public function setTaskId(mixed $task_id): void
    {
        $this->task_id = $task_id;
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
    public function getCompanyId(): mixed
    {
        return $this->company_id;
    }

    /**
     * @param mixed $company_id
     */
    public function setCompanyId(mixed $company_id): void
    {
        $this->company_id = $company_id;
    }

    /**
     * @return mixed
     */
    public function getLevel(): mixed
    {
        return $this->level;
    }

    /**
     * @param mixed $level
     */
    public function setLevel(mixed $level): void
    {
        $this->level = $level;
    }

    /**
     * @return mixed
     */
    public function getControl(): mixed
    {
        return $this->control;
    }

    /**
     * @param mixed $control
     */
    public function setControl(mixed $control): void
    {
        $this->control = $control;
    }

    /**
     * @return mixed
     */
    public function getRiskIntensity(): mixed
    {
        return $this->risk_intensity;
    }

    /**
     * @param mixed $risk_intensity
     */
    public function setRiskIntensity(mixed $risk_intensity): void
    {
        $this->risk_intensity = $risk_intensity;
    }

    /**
     * @return mixed
     */
    public function getRiskEffect(): mixed
    {
        return $this->risk_effect;
    }

    /**
     * @param mixed $risk_effect
     */
    public function setRiskEffect(mixed $risk_effect): void
    {
        $this->risk_effect = $risk_effect;
    }

    /**
     * @return mixed
     */
    public function getRiskData(): mixed
    {
        return $this->risk_data;
    }

    /**
     * @param mixed $risk_data
     */
    public function setRiskData(mixed $risk_data): void
    {
        $this->risk_data = $risk_data;
    }

    /**
     * @return mixed
     */
    public function getRiskThreat(): mixed
    {
        return $this->risk_threat;
    }

    /**
     * @param mixed $risk_threat
     */
    public function setRiskThreat(mixed $risk_threat): void
    {
        $this->risk_threat = $risk_threat;
    }

    /**
     * @return mixed
     */
    public function getRiskDamage(): mixed
    {
        return $this->risk_damage;
    }

    /**
     * @param mixed $risk_damage
     */
    public function setRiskDamage(mixed $risk_damage): void
    {
        $this->risk_damage = $risk_damage;
    }

    /**
     * @return mixed
     */
    public function getRiskResponseType(): mixed
    {
        return $this->risk_response_type;
    }

    /**
     * @param mixed $risk_response_type
     */
    public function setRiskResponseType(mixed $risk_response_type): void
    {
        $this->risk_response_type = $risk_response_type;
    }

    /**
     * @return mixed
     */
    public function getRiskExecutionPercent(): mixed
    {
        return $this->risk_execution_percent;
    }

    /**
     * @param mixed $risk_execution_percent
     */
    public function setRiskExecutionPercent(mixed $risk_execution_percent): void
    {
        $this->risk_execution_percent = $risk_execution_percent;
    }

    /**
     * @return mixed
     */
    public function getRiskProposedAction(): mixed
    {
        return $this->risk_proposed_action;
    }

    /**
     * @param mixed $risk_proposed_action
     */
    public function setRiskProposedAction(mixed $risk_proposed_action): void
    {
        $this->risk_proposed_action = $risk_proposed_action;
    }

    /**
     * @return mixed
     */
    public function getRiskScenario(): mixed
    {
        return $this->risk_scenario;
    }

    /**
     * @param mixed $risk_scenario
     */
    public function setRiskScenario(mixed $risk_scenario): void
    {
        $this->risk_scenario = $risk_scenario;
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
     * @return mixed
     */
    public function getTimeDeadline(): mixed
    {
        return $this->time_deadline;
    }

    /**
     * @param mixed $time_deadline
     */
    public function setTimeDeadline(mixed $time_deadline): void
    {
        $this->time_deadline = $time_deadline;
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