<?php

namespace InvadersXX\FilamentNestedList\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \InvadersXX\FilamentNestedList\FilamentNestedList
 */
class FilamentNestedList extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \InvadersXX\FilamentNestedList\FilamentNestedList::class;
    }
}
