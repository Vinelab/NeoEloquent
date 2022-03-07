<?php

namespace Vinelab\NeoEloquent;

class LabelAction
{
    private string $label;
    private bool $set;

    public function __construct(string $label, bool $set = true)
    {
        $this->label = $label;
        $this->set = $set;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setsLabel(): bool
    {
        return $this->set;
    }

    public function removesLabel(): bool
    {
        return !$this->setsLabel();
    }
}