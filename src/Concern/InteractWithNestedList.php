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

    protected NestedList $tree;

    public function bootedInteractWithNestedList(): void
    {
        $tree = $this->getTree();
        $this->tree = $tree->configureUsing(
            Closure::fromCallable([static::class, 'tree']),
            fn (): NestedList => static::tree($tree)->maxDepth(static::getMaxDepth()),
        );

        $this->cacheTreeActions();
        $this->cacheTreeEmptyStateActions();

        $this->tree->actions(array_values($this->getCachedTreeActions()));

        if ($this->hasMounted) {
            return;
        }

        $this->hasMounted = true;
    }

    protected function getTree(): NestedList
    {
        return NestedList::make($this);
    }

    public function mountInteractsWithNestedList(): void
    {
    }

    public function getTreeRecordTitle(?Model $record = null): string
    {
        if (! $record) {
            return '';
        }

        return $record->{(method_exists($record, 'determineTitleColumnName') ? $record->determineTitleColumnName() : 'title')};
    }

    public function getTreeRecordIcon(?Model $record = null): ?string
    {
        if (! $record) {
            return null;
        }

        return $record->{(method_exists($record, 'determineIconColumnName') ? $record->determineIconColumnName() : 'icon')};
    }

    public function getRecordKey(?Model $record): ?string
    {
        return $this->getCachedTree()->getRecordKey($record);
    }

    protected function getCachedTree(): NestedList
    {
        return $this->tree;
    }

    public function getParentKey(?Model $record): ?string
    {
        return $this->getCachedTree()->getParentKey($record);
    }

    public function getNodeCollapsedState(?Model $record = null): bool
    {
        return false;
    }

    /**
     * Update the tree list.
     */
    public function updateTree(?array $list = null): void
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
                ->title(__('filament-actions::edit.single.modal.actions.save.label'))
                ->send();
        }
        if ($needReload) {
            $this->dispatch('refreshNestedList')->self();
        }
    }

    /**
     * Unnesting the tree array.
     */
    private function unnestArray(array &$result, array $current, $parent): void
    {
        foreach ($current as $index => $item) {
            $key = data_get($item, 'id');
            $result[$key] = [
                'parent_id' => $parent,
                'order' => $index + 1,
            ];
            if (isset($item['children']) && count($item['children'])) {
                $this->unnestArray($result, $item['children'], $key);
            }
        }
    }
}
