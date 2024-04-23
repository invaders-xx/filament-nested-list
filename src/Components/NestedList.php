<?php

namespace InvadersXX\FilamentNestedList\Components;

use Filament\Forms\ComponentContainer;
use Filament\Support\Components\ViewComponent;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Concern\BelongsToLivewire;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;
use InvadersXX\FilamentNestedList\Support\Utils;

class NestedList extends ViewComponent
{
    use BelongsToLivewire;

    public const LOADING_TARGETS = ['activeLocale'];

    protected string $view = 'filament-nested-list::components.tree.index';

    protected string $viewIdentifier = 'tree';

    protected int $maxDepth = 999;

    protected array $actions = [];

    public function __construct(HasNestedList $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasNestedList $livewire): static
    {
        $result = app(static::class, ['livewire' => $livewire]);

        $result->configure();

        return $result;
    }

    public function maxDepth(int $maxDepth): static
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    public function actions(array $actions): static
    {
        $this->actions = $actions;

        return $this;
    }

    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    public function getActions(): array
    {
        return $this->actions;
    }

    public function getModel(): string
    {
        return $this->getLivewire()->getModel();
    }

    public function getRecordKey(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->getAttributeValue($record->getKeyName());
    }

    public function getParentKey(?Model $record): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->getAttributeValue((method_exists($record, 'determineParentKey') ? $record->determineParentColumnName() : Utils::parentColumnName()));
    }

    public function getMountedActionForm(): ?ComponentContainer
    {
        return $this->getLivewire()->getMountedTreeActionForm();
    }
}
