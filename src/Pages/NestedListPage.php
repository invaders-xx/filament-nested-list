<?php

namespace InvadersXX\FilamentNestedList\Pages;

use Filament\Actions\Action as FilamentActionsAction;
use Filament\Actions\CreateAction;
use Filament\Infolists\Components\Component as InfolistsComponent;
use Filament\Pages\Page;
use InvadersXX\FilamentNestedList\Actions;
use InvadersXX\FilamentNestedList\Components\NestedList;
use InvadersXX\FilamentNestedList\Concern\InteractWithNestedList;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;

abstract class NestedListPage extends Page implements HasNestedList
{
    use InteractWithNestedList;

    protected static string $view = 'filament-nested-list::pages.tree';

    protected static string $viewIdentifier = 'tree';

    protected static string $model;

    protected static int $maxDepth = 999;

    public static function getMaxDepth(): int
    {
        return static::$maxDepth;
    }

    public static function tree(NestedList $tree): NestedList
    {
        return $tree;
    }

    protected function configureAction(FilamentActionsAction $action): void
    {
        match (true) {
            $action instanceof CreateAction => $this->configureCreateAction($action),
            default => null,
        };
    }

    protected function configureCreateAction(CreateAction $action): CreateAction
    {
        $action->livewire($this);

        $schema = $this->getCreateFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        $action->model($this->getModel());

        $this->afterConfiguredCreateAction($action);

        return $action;
    }

    protected function getCreateFormSchema(): array
    {
        return [];
    }

    protected function getFormSchema(): array
    {
        return [];
    }

    protected function model(string $model): static
    {
        static::$model = $model;

        return $this;
    }

    public function getModel(): string
    {
        return static::$model ?? class_basename(static::class);
    }

    protected function afterConfiguredCreateAction(CreateAction $action): CreateAction
    {
        return $action;
    }

    protected function configureTreeAction(Actions\Action $action): void
    {
        match (true) {
            $action instanceof Actions\DeleteAction => $this->configureDeleteAction($action),
            $action instanceof Actions\EditAction => $this->configureEditAction($action),
            $action instanceof Actions\ViewAction => $this->configureViewAction($action),
            default => null,
        };
    }

    protected function configureDeleteAction(Actions\DeleteAction $action): Actions\DeleteAction
    {
        $action->nestedList($this->getCachedNestedList());

        $action->iconButton();

        $this->afterConfiguredDeleteAction($action);

        return $action;
    }

    protected function afterConfiguredDeleteAction(Actions\DeleteAction $action): Actions\DeleteAction
    {
        return $action;
    }

    protected function configureEditAction(Actions\EditAction $action): Actions\EditAction
    {
        $action->nestedList($this->getCachedNestedList());

        $action->iconButton();

        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        $action->model($this->getModel());

        $action->mutateFormDataBeforeSaveUsing(fn (array $data) => $this->mutateFormDataBeforeSave($data));

        $this->afterConfiguredEditAction($action);

        return $action;
    }

    protected function getEditFormSchema(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function afterConfiguredEditAction(Actions\EditAction $action): Actions\EditAction
    {
        return $action;
    }

    protected function configureViewAction(Actions\ViewAction $action): Actions\ViewAction
    {
        $action->nestedList($this->getCachedNestedList());

        $action->iconButton();

        $schema = $this->getViewFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($this->getFormSchema());

        $isInfoList = count(array_filter($schema, fn ($component) => $component instanceof InfolistsComponent)) > 0;

        if ($isInfoList) {
            $action->infolist($schema);
        }

        $action->model($this->getModel());

        $this->afterConfiguredViewAction($action);

        return $action;
    }

    protected function getViewFormSchema(): array
    {
        return [];
    }

    protected function afterConfiguredViewAction(Actions\ViewAction $action): Actions\ViewAction
    {
        return $action;
    }

    protected function getTreeActions(): array
    {
        return array_merge(
            ($this->hasEditAction() ? [$this->getEditAction()] : []),
            ($this->hasViewAction() ? [$this->getViewAction()] : []),
            ($this->hasDeleteAction() ? [$this->getDeleteAction()] : []),
        );
    }

    protected function hasEditAction(): bool
    {
        return true;
    }

    protected function getEditAction(): Actions\EditAction
    {
        return Actions\EditAction::make();
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getViewAction(): Actions\ViewAction
    {
        return Actions\ViewAction::make();
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function getDeleteAction(): Actions\DeleteAction
    {
        return Actions\DeleteAction::make();
    }

    protected function getActions(): array
    {
        return array_merge(
            ($this->hasCreateAction() ? [$this->getCreateAction()] : []),
        );
    }

    protected function hasCreateAction(): bool
    {
        return true;
    }

    protected function getCreateAction(): CreateAction
    {
        return CreateAction::make();
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }
}
