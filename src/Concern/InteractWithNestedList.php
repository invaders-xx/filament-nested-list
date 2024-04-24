<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Closure;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Components\NestedList;
use InvadersXX\FilamentNestedList\Support\Utils;

trait InteractWithNestedList
{
    use HasActions;
    use HasEmptyState;
    use HasHeading;
    use HasRecords;

    protected bool $hasMounted = false;

    protected NestedList $nestedList;

    public function bootedInteractWithNestedList(): void
    {
        $nestedList = $this->getNestedList();
        $this->nestedList = $nestedList->configureUsing(
            Closure::fromCallable([static::class, 'nestedList']),
            fn (): NestedList => static::nestedList($nestedList)->maxDepth(static::getMaxDepth()),
        );

        $this->cacheNestedListActions();
        $this->cacheNestedListEmptyStateActions();

        $this->nestedList->actions(array_values($this->getCachedNestedListActions()));

        if ($this->hasMounted) {
            return;
        }

        $this->hasMounted = true;
    }

    public function mountInteractsWithNestedList(): void
    {
    }

    public function getNestedListRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->{(method_exists($record, 'determineTitleColumnName') ? $record->determineTitleColumnName() : 'title')};
    }

    public function getRecordKey(?Model $record): ?string
    {
        return $this->getCachedNestedList()->getRecordKey($record);
    }

    public function getParentKey(?Model $record): ?string
    {
        return $this->getCachedNestedList()->getParentKey($record);
    }

    public function getNodeCollapsedState(?Model $record = null): bool
    {
        return false;
    }

    /**
     * Update the tree list.
     */
    public function updateNestedList(?array $list = null): void
    {
        $needReload = false;
        if ($list) {
            $records = $this->getRecords()->keyBy(fn ($record) => $record->getAttributeValue($record->getKeyName()));
            $defaultParentId = Utils::defaultParentId();
            $unnestedArrData = collect($list)
                ->map(fn (array $data, $id) => ['data' => $data, 'model' => $records->get($data['id'])])
                ->filter(fn (array $arr) => ! is_null($arr['model']));
            foreach ($unnestedArrData as $arr) {
                $model = $arr['model'];
                [$newParentId, $newOrder] = [$arr['data']['parent_id'] ?? $defaultParentId, $arr['data']['order']];
                if ($model instanceof Model) {
                    $parentColumnName = method_exists($model, 'determineParentColumnName') ? $model->determineParentColumnName() : Utils::parentColumnName();
                    $orderColumnName = method_exists($model, 'determineOrderColumnName') ? $model->determineOrderColumnName() : Utils::orderColumnName();
                    $newParentId = $newParentId === $defaultParentId && method_exists($model, 'defaultParentKey') ? $model::defaultParentKey() : $newParentId;

                    $model->{$parentColumnName} = $newParentId;
                    $model->{$orderColumnName} = $newOrder;
                    if ($model->isDirty([$parentColumnName, $orderColumnName])) {
                        $model->save();

                        $needReload = true;
                    }
                }
            }
        }
        if ($needReload) {
            Notification::make()
                ->success()
                ->title(__('filament-actions::edit.single.notifications.saved.title'))
                ->send();
        }
        if ($needReload) {
            $this->dispatch('refreshNestedList')->self();
        }
    }

    protected function getNestedList(): NestedList
    {
        return NestedList::make($this);
    }

    protected function getCachedNestedList(): NestedList
    {
        return $this->nestedList;
    }
}
