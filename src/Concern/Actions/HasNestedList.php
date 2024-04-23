<?php

namespace InvadersXX\FilamentNestedList\Concern\Actions;

use InvadersXX\FilamentNestedList\Components\NestedList;

interface HasNestedList
{
    public function tree(NestedList $tree): static;
}
