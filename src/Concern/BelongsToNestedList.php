<?php

namespace InvadersXX\FilamentNestedList\Concern;

use InvadersXX\FilamentNestedList\Components\NestedList;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;

trait BelongsToNestedList
{
    protected NestedList $nestedList;

    public function nestedList(NestedList $nestedList): static
    {
        $this->nestedList = $nestedList;

        return $this;
    }

    public function getLivewire(): HasNestedList
    {
        return $this->getNestedList()->getLivewire();
    }

    public function getNestedList(): NestedList
    {
        return $this->nestedList;
    }
}
