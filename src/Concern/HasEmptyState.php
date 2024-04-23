<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Closure;
use Illuminate\Contracts\View\View;
use InvadersXX\FilamentNestedList\Actions\Action;

trait HasEmptyState
{
    protected array $cachedNestedListEmptyStateActions;

    public function cacheNestedListEmptyStateActions(): void
    {
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureNestedListAction']),
            fn (): array => $this->getNestedListEmptyStateActions(),
        );

        $this->cachedNestedListEmptyStateActions = [];

        foreach ($actions as $action) {
            $action->nestedList($this->getCachedNestedList());

            $this->cachedNestedListEmptyStateActions[$action->getName()] = $action;
        }
    }

    public function getCachedNestedListEmptyStateAction(string $name): ?Action
    {
        return $this->getCachedNestedListEmptyStateActions()[$name] ?? null;
    }

    public function getCachedNestedListEmptyStateActions(): array
    {
        return $this->cachedNestedListEmptyStateActions;
    }

    protected function getNestedListEmptyStateActions(): array
    {
        return [];
    }

    protected function getNestedListEmptyState(): ?View
    {
        return null;
    }

    protected function getNestedListEmptyStateDescription(): ?string
    {
        return null;
    }

    protected function getNestedListEmptyStateHeading(): ?string
    {
        return null;
    }

    protected function getNestedListEmptyStateIcon(): ?string
    {
        return null;
    }
}
