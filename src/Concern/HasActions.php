<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Closure;
use Filament\Forms\Form;
use Filament\Support\Exceptions\Cancel;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Actions\Action;
use InvadersXX\FilamentNestedList\Actions\ActionGroup;

/**
 * @property Form $mountedNestedListActionForm
 */
trait HasActions
{
    /**
     * @var null|array<string>
     */
    public ?array $mountedNestedListAction = [];

    /**
     * @var null|array<string, array<string, mixed>>
     */
    public ?array $mountedNestedListActionData = [];

    public int|string|null $mountedNestedListActionRecord = null;

    protected array $cachedNestedListActions;

    protected ?Model $cachedMountedNestedListActionRecord = null;

    protected int|string|null $cachedMountedNestedListActionRecordKey = null;

    public function cacheNestedListActions(): void
    {
        $this->cachedNestedListActions = [];

        $actions = Action::configureUsing(
            Closure::fromCallable([$this, 'configureNestedListAction']),
            fn (): array => $this->getNestedListActions(),
        );

        foreach ($actions as $index => $action) {
            if ($action instanceof ActionGroup) {
                foreach ($action->getActions() as $groupedAction) {
                    $groupedAction->nestedList($this->getCachedNestedList());
                }

                $this->cachedNestedListActions[$index] = $action;

                continue;
            }

            $action->nestedList($this->getCachedNestedList());

            $this->cachedNestedListActions[$action->getName()] = $action;
        }
    }

    public function mountNestedListAction(string $name, ?string $record = null)
    {
        $this->mountedNestedListAction[] = $name;
        $this->mountedNestedListActionData[] = [];

        if (count($this->mountedNestedListAction) === 1) {
            $this->mountedNestedListActionRecord($record);
        }

        $action = $this->getMountedNestedListAction();

        if (! $action) {
            $this->unmountNestedListAction();

            return null;
        }

        if (filled($record) && ($action->getRecord() === null)) {
            return;
        }

        if ($action->isDisabled()) {
            return;
        }

        $this->cacheMountedNestedListActionForm();

        try {
            $hasForm = $this->mountedNestedListActionHasForm();

            if ($hasForm) {
                $action->callBeforeFormFilled();
            }

            $action->mount([
                'form' => $this->getMountedNestedListActionForm(),
            ]);

            if ($hasForm) {
                $action->callAfterFormFilled();
            }
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
            $this->unmountNestedListAction(shouldCancelParentActions: false);

            return null;
        }

        if (! $this->mountedNestedListActionShouldOpenModal()) {
            return $this->callMountedNestedListAction();
        }

        $this->resetErrorBag();

        $this->openNestedListActionModal();

        return null;
    }

    public function mountedNestedListActionRecord($record): void
    {
        $this->mountedNestedListActionRecord = $record;
    }

    public function getMountedNestedListAction(): ?Action
    {
        if (! count($this->mountedNestedListAction ?? [])) {
            return null;
        }

        return $this->getCachedNestedListAction($this->mountedNestedListAction) ?? $this->getCachedNestedListEmptyStateAction($this->mountedNestedListAction);
    }

    /**
     * @param array<string>|string $name
     */
    public function getCachedNestedListAction(string|array $name): ?Action
    {
        if (is_string($name) && str($name)->contains('.')) {
            $name = explode('.', $name);
        }

        if (is_array($name)) {
            $firstName = array_shift($name);

            $name = $firstName;
        }

        return $this->findNestedListAction($name)?->record($this->getMountedNestedListActionRecord());
    }

    public function getCachedNestedListActions(): array
    {
        return $this->cachedNestedListActions;
    }

    public function getMountedNestedListActionRecord(): ?Model
    {
        $recordKey = $this->getMountedNestedListActionRecordKey();

        if ($this->cachedMountedNestedListActionRecord && ($this->cachedMountedNestedListActionRecordKey === $recordKey)) {
            return $this->cachedMountedNestedListActionRecord;
        }

        $this->cachedMountedNestedListActionRecordKey = $recordKey;

        return $this->cachedMountedNestedListActionRecord = $this->getNestedListRecord($recordKey);
    }

    public function getMountedNestedListActionRecordKey(): int|string|null
    {
        return $this->mountedNestedListActionRecord;
    }

    public function unmountNestedListAction(bool $shouldCancelParentActions = true): void
    {
        $action = $this->getMountedNestedListAction();

        if (! ($shouldCancelParentActions && $action)) {
            $this->popMountedNestedListAction();
        } elseif ($action->shouldCancelAllParentActions()) {
            $this->resetMountedNestedListActionProperties();
        } else {
            $parentActionToCancelTo = $action->getParentActionToCancelTo();

            while (true) {
                $recentlyClosedParentAction = $this->popMountedNestedListAction();

                if (
                    blank($parentActionToCancelTo)
                    || ($recentlyClosedParentAction === $parentActionToCancelTo)
                ) {
                    break;
                }
            }
        }

        if (! count($this->mountedNestedListAction)) {
            $this->closeNestedListActionModal();

            $action?->record(null);
            $this->mountedNestedListActionRecord(null);

            return;
        }

        $this->cacheMountedNestedListActionForm();

        $this->resetErrorBag();

        $this->openNestedListActionModal();
    }

    public function getMountedNestedListActionForm()
    {
        $action = $this->getMountedNestedListAction();

        if (! $action) {
            return null;
        }

        if ((! $this->isCachingForms) && $this->hasCachedForm('mountedNestedListActionForm')) {
            return $this->getCachedForm('mountedNestedListActionForm');
        }

        return $action->getForm(
            $this->makeForm()
                ->model($this->getMountedNestedListActionRecord() ?? $this->getNestedListQuery()->getModel()::class)
                ->statePath('mountedNestedListActionData.' . array_key_last($this->mountedNestedListActionData))
                ->operation(implode('.', $this->mountedNestedListAction)),
        );
    }

    public function mountedNestedListActionHasForm(): bool
    {
        return (bool) count($this->getMountedNestedListActionForm()?->getComponents() ?? []);
    }

    public function mountedNestedListActionShouldOpenModal(): bool
    {
        return $this->getMountedNestedListAction()->shouldOpenModal(
            checkForFormUsing: $this->mountedTableActionHasForm(...),
        );
        // $action = $this->getMountedNestedListAction();

        // if ($action->shouldOpenModal()) {
        //     return false;
        // }

        // return $action->getModalDescription() ||
        //     $action->getModalContent() ||
        //     $action->getModalContentFooter() ||
        //     $action->getInfolist() ||
        //     $this->mountedNestedListActionHasForm();
    }

    public function callMountedNestedListAction(?string $arguments = null)
    {
        $action = $this->getMountedNestedListAction();

        if (! $action) {
            return null;
        }

        if (filled($this->mountedNestedListActionRecord) && ($action->getRecord() === null)) {
            return null;
        }

        if ($action->isDisabled()) {
            return null;
        }

        $action->arguments($arguments ? json_decode($arguments, associative: true) : []);

        $form = $this->getMountedNestedListActionForm();

        $result = null;

        try {
            if ($this->mountedNestedListActionHasForm()) {
                $action->callBeforeFormValidated();

                $action->formData($form->getState());

                $action->callAfterFormValidated();
            }

            $action->callBefore();

            $result = $action->call([
                'form' => $form,
            ]);

            $result = $action->callAfter() ?? $result;
        } catch (Halt $exception) {
            return null;
        } catch (Cancel $exception) {
        }

        $action->resetArguments();
        $action->resetFormData();

        $this->unmountNestedListAction();

        return $result;
    }

    /**
     * Action for each record.
     */
    protected function getNestedListActions(): array
    {
        return [];
    }

    protected function findNestedListAction(string $name): ?Action
    {
        $actions = $this->getCachedNestedListActions();

        $action = $actions[$name] ?? null;

        if ($action) {
            return $action;
        }

        foreach ($actions as $action) {
            if (! $action instanceof ActionGroup) {
                continue;
            }

            $groupedAction = $action->getActions()[$name] ?? null;

            if (! $groupedAction) {
                continue;
            }

            return $groupedAction;
        }

        return null;
    }

    protected function popMountedNestedListAction(): ?string
    {
        try {
            return array_pop($this->mountedNestedListAction);
        } finally {
            array_pop($this->mountedNestedListActionData);
        }
    }

    protected function resetMountedNestedListActionProperties(): void
    {
        $this->mountedNestedListAction = [];
        $this->mountedNestedListActionData = [];
    }

    protected function closeNestedListActionModal(): void
    {
        $this->dispatch('close-modal', id: "{$this->getId()}-nested-list-action");
    }

    protected function cacheMountedNestedListActionForm(): void
    {
        $this->cacheForm(
            'mountedNestedListActionForm',
            fn () => $this->getMountedNestedListActionForm(),
        );
    }

    protected function openNestedListActionModal(): void
    {
        $this->dispatch('open-modal', id: "{$this->getId()}-nested-list-action");
    }

    protected function configureNestedListAction(Action $action): void
    {
    }

    protected function getHasActionsForms(): array
    {
        return [
            'mountedNestedListActionData' => $this->getMountedNestedListActionForm(),
        ];
    }

    protected function getNestedListActionsPosition(): ?string
    {
        return null;
    }
}
