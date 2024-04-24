<?php

namespace InvadersXX\FilamentNestedList\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Components\NestedList as NestedListComponent;
use InvadersXX\FilamentNestedList\Concern\InteractWithNestedList;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;

class NestedList extends Widget implements HasForms, HasNestedList
{
    use InteractsWithForms;
    use InteractWithNestedList;

    protected static string $view = 'filament-nested-list::widgets.nested-list';

    protected static string $model;

    protected static int $maxDepth = 2;

    protected $listeners = [
        'refreshNestedList' => '$refresh',
    ];

    protected int|string|array $columnSpan = 'full';

    public static function getMaxDepth(): int
    {
        return static::$maxDepth;
    }

    public static function nestedList(NestedListComponent $nestedList): NestedListComponent
    {
        return $nestedList;
    }

    public function makeTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    public function getModel(): string
    {
        return static::$model ?? class_basename(static::class);
    }

    protected function getFormModel(): Model|string|null
    {
        return $this->getModel();
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }
}
