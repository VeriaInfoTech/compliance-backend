<?php

namespace Risk\Model\Form;

class Link
{
    private $id;
    private int $form_id;
    private int $element_id;
    private        $status;

    public function __construct(
        $form_id,
        $element_id,
        $status = null,
        $id = null
    ) {
        $this->form_id    = $form_id;
        $this->element_id = $element_id;
        $this->status      = $status;
        $this->id         = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getElementId(): ?int
    {
        return $this->element_id;
    }

    public function getFormId(): ?int
    {
        return $this->form_id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }
}