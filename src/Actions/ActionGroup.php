<?php

namespace InvadersXX\FilamentNestedList\Actions;

use Filament\Actions\ActionGroup as BaseActionGroup;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Actions\Contracts\HasRecord;
use InvadersXX\FilamentNestedList\Components\NestedList;
use InvadersXX\FilamentNestedList\Concern\Actions\HasNestedList;

class ActionGroup extends BaseActionGroup implements HasNestedList, HasRecord
{
    use InteractsWithRecord;

    protected string $view = 'filament-nested-list::actions.group';

    public function getActions(): array
    {
        $actions = [];

        foreach ($this->actions as $action) {
            $actions[$action->getName()] = $action->grouped()->record($this->getRecord());
        }

        return $actions;
    }

    public function tree(NestedList $tree): static
    {
        foreach ($this->actions as $action) {
            if (! $action instanceof HasNestedList) {
                continue;
            }

            $action->tree($tree);
        }

        return $this;
    }
}
