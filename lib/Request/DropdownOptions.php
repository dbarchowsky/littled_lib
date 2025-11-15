<?php

namespace Littled\Request;


class DropdownOptions
{
    public int      $value;
    public string   $label = '';

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;
        return $this;
    }
}