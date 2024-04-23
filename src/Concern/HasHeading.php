<?php

namespace InvadersXX\FilamentNestedList\Concern;

trait HasHeading
{
    protected ?string $title = null;

    protected bool $enableTitle = false;

    public function nestedListTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function enableNestedListTitle(bool $condition): static
    {
        $this->enableTitle = $condition;

        return $this;
    }

    public function getNestedListTitle(): ?string
    {
        return $this->title;
    }

    public function displayNestedListTitle(): bool
    {
        return $this->enableTitle;
    }
}
