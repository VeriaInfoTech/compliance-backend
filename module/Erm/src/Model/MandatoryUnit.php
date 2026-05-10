<?php

namespace Erm\Model;

class MandatoryUnit
{
    private mixed $id;
    private mixed $title;
    private mixed $slug;
    private mixed $information;

    /**
     * @param mixed $id
     * @param mixed $title
     * @param mixed $slug
     * @param mixed $information
     */
    public function __construct(mixed $id, mixed $title, mixed $slug, mixed $information)
    {
        $this->id = $id;
        $this->title = $title;
        $this->slug = $slug;
        $this->information = $information;
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


}