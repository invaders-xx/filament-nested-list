<?php

namespace InvadersXX\FilamentNestedList\Macros;

use Illuminate\Database\Schema\Blueprint;
use InvadersXX\FilamentNestedList\Support\Utils;

/**
 * @see Blueprint
 */
class BlueprintMacros
{
    public function nestedListColumns()
    {
        return function (string $titleType = 'string') {
            $this->{$titleType}(Utils::titleColumnName());
            $this->integer(Utils::parentColumnName())->default(Utils::defaultParentId())->index();
            $this->integer(Utils::orderColumnName())->default(0);
        };
    }
}
