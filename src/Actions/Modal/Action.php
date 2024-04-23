<?php

namespace InvadersXX\FilamentNestedList\Actions\Modal;

use Filament\Actions\StaticAction;
use InvadersXX\FilamentNestedList\Concern\Actions\HasNestedList;
use InvadersXX\FilamentNestedList\Concern\BelongsToNestedList;

/**
 * @deprecated Use `\Filament\Actions\StaticAction` instead.
 */
class Action extends StaticAction implements HasNestedList
{
    use BelongsToNestedList;
}
