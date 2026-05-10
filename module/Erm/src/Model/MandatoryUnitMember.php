<?php

namespace Erm\Model;

class MandatoryUnitMember
{
    private mixed $id;
    private mixed $user_id;
    private mixed $mandatory_unit;
    private mixed $time_create;
    private mixed $time_update;

    /**
     * @param mixed $id
     * @param mixed $user_id
     * @param mixed $mandatory_unit
     * @param mixed $time_create
     * @param mixed $time_update
     */
    public function __construct(mixed $id, mixed $user_id, mixed $mandatory_unit, mixed $time_create, mixed $time_update)
    {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->mandatory_unit = $mandatory_unit;
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