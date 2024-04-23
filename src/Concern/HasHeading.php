<?php

namespace InvadersXX\FilamentNestedList\Concern;

trait HasHeading
{
    protected ?string $nestedListTitle = null;

    protected bool $enableNestedListTitle = false;

    public function nestedListTitle(string $nestedListTitle): static
    {
        $this->nestedListTitle = $nestedListTitle;

        return $this;
    }

    public function enableNestedListTitle(bool $condition): static
    {
        $this->enableNestedListTitle = $condition;

        return $this;
    }

    public function getNestedListTitle(): ?string
    {
        return $this->nestedListTitle;
    }

    public function displayNestedListTitle(): bool
    {
        return $this->enableNestedListTitle;
    }
}
