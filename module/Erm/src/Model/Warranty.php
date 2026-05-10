<?php

namespace Erm\Model;

class Warranty
{
    private mixed $id;
    private mixed $slug;
    private string $title;

    public function __construct(
        $title,
        $slug = null,
        $id = null
    )
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->title = $title;
    }

    /**
     * @return mixed|null
     */
    public function getId(): mixed
    {
        return $this->id;
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
    public function getTitle(): string
    {
        return $this->title;
    }

}