<?php

namespace InvadersXX\FilamentNestedList\Concern;

use InvadersXX\FilamentNestedList\Components\NestedList;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;

trait BelongsToNestedList
{
    protected NestedList $tree;

    public function tree(NestedList $tree): static
    {
        $this->tree = $tree;

        return $this;
    }

    public function getLivewire(): HasNestedList
    {
        return $this->getTree()->getLivewire();
    }

    public function getTree(): NestedList
    {
        return $this->tree;
    }
}
