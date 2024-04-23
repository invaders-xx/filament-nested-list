<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Closure;
use Illuminate\Contracts\View\View;
use InvadersXX\FilamentNestedList\Actions\Action;

trait HasEmptyState
{
    protected array $cachedTreeEmptyStateActions;

    public function cacheTreeEmptyStateActions(): void
    {
        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureTreeAction']),
            fn (): array => $this->getTreeEmptyStateActions(),
        );

        $this->cachedTreeEmptyStateActions = [];

        foreach ($actions as $action) {
            $action->tree($this->getCachedTree());

            $this->cachedTreeEmptyStateActions[$action->getName()] = $action;
        }
    }

    protected function getTreeEmptyStateActions(): array
    {
        return [];
    }

    public function getCachedTreeEmptyStateAction(string $name): ?Action
    {
        return $this->getCachedTreeEmptyStateActions()[$name] ?? null;
    }

    public function getCachedTreeEmptyStateActions(): array
    {
        return $this->cachedTreeEmptyStateActions;
    }

    protected function getTreeEmptyState(): ?View
    {
        return null;
    }

    protected function getTreeEmptyStateDescription(): ?string
    {
        return null;
    }

    protected function getTreeEmptyStateHeading(): ?string
    {
        return null;
    }

    protected function getTreeEmptyStateIcon(): ?string
    {
        return null;
    }
}
