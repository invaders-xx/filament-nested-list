<?php

namespace InvadersXX\FilamentNestedList\Concern\Actions;

use InvadersXX\FilamentNestedList\Components\NestedList;

interface HasNestedList
{
    public function nestedList(NestedList $nestedList): static;
}
