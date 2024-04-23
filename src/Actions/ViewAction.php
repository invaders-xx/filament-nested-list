<?php

namespace InvadersXX\FilamentNestedList\Actions;

use Closure;
use Filament\Actions\StaticAction;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Components\NestedList;

class ViewAction extends Action
{
    protected ?Closure $mutateRecordDataUsing = null;

    public static function getDefaultName(): ?string
    {
        return 'view';
    }

    public function mutateRecordDataUsing(?Closure $callback): static
    {
        $this->mutateRecordDataUsing = $callback;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-actions::view.single.label'));

        $this->modalHeading(fn (): string => __('filament-actions::view.single.modal.heading', ['label' => $this->getRecordTitle()]));

        $this->modalSubmitAction(false);
        $this->modalCancelAction(fn (StaticAction $action) => $action->label(__('filament-actions::view.single.modal.actions.close.label')));

        $this->color('gray');

        $this->icon('heroicon-m-eye');

        $this->disabledForm();

        $this->fillForm(function (Model $record, NestedList $tree): array {
            if ($translatableContentDriver = $tree->makeFilamentTranslatableContentDriver()) {
                $data = $translatableContentDriver->getRecordAttributesToArray($record);
            } else {
                $data = $record->attributesToArray();
            }

            if ($this->mutateRecordDataUsing) {
                $data = $this->evaluate($this->mutateRecordDataUsing, ['data' => $data, 'record' => $record]);
            }

            return $data;
        });

        $this->action(static function (): void {
        });
    }
}
