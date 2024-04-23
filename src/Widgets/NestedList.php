<?php

namespace InvadersXX\FilamentNestedList\Widgets;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\Component as InfolistsComponent;
use Filament\Support\Contracts\TranslatableContentDriver;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Actions\Action;
use InvadersXX\FilamentNestedList\Actions\DeleteAction;
use InvadersXX\FilamentNestedList\Actions\EditAction;
use InvadersXX\FilamentNestedList\Actions\ViewAction;
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

    public function makeTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return null;
    }

    protected function getFormModel(): Model|string|null
    {
        return $this->getModel();
    }

    public function getModel(): string
    {
        return static::$model ?? class_basename(static::class);
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
        return false;
    }

    protected function getEditAction(): EditAction
    {
        return EditAction::make();
    }

    protected function hasViewAction(): bool
    {
        return false;
    }

    protected function getViewAction(): ViewAction
    {
        return ViewAction::make();
    }

    protected function hasDeleteAction(): bool
    {
        return false;
    }

    protected function getDeleteAction(): DeleteAction
    {
        return DeleteAction::make();
    }

    protected function configureTreeAction(Action $action): void
    {
        match (true) {
            $action instanceof DeleteAction => $this->configureDeleteAction($action),
            $action instanceof EditAction => $this->configureEditAction($action),
            $action instanceof ViewAction => $this->configureViewAction($action),
            default => null,
        };
    }

    protected function configureDeleteAction(DeleteAction $action): DeleteAction
    {
        $action->nestedList($this->getCachedNestedList());

        $action->iconButton();

        $this->afterConfiguredDeleteAction($action);

        return $action;
    }

    public static function nestedList(NestedListComponent $nestedList): NestedListComponent
    {
        return $nestedList;
    }

    protected function afterConfiguredDeleteAction(DeleteAction $action): DeleteAction
    {
        return $action;
    }

    protected function configureEditAction(EditAction $action): EditAction
    {
        $action->nestedList($this->getCachedNestedList());

        $action->iconButton();

        $schema = $this->getEditFormSchema();

        if (empty($schema)) {
            $schema = $this->getFormSchema();
        }

        $action->form($schema);

        $action->model($this->getModel());

        $this->afterConfiguredEditAction($action);

        return $action;
    }

    protected function getEditFormSchema(): array
    {
        return [];
    }

    protected function getFormSchema(): array
    {
        return [];
    }

    protected function afterConfiguredEditAction(EditAction $action): EditAction
    {
        return $action;
    }

    protected function configureViewAction(ViewAction $action): ViewAction
    {
        $action->tree($this->getCachedTree());

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

    protected function afterConfiguredViewAction(ViewAction $action): ViewAction
    {
        return $action;
    }

    protected function callHook(string $hook): void
    {
        if (! method_exists($this, $hook)) {
            return;
        }

        $this->{$hook}();
    }
}
