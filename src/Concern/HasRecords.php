<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Support\Utils;

trait HasRecords
{
    protected ?Collection $records = null;

    public function getNestedListData(): array
    {
        return collect($this->getRecords() ?? [])
            ->map(function ($record) {
                $data = [
                    'id' => $record->id,
                    'order' => $record->order,
                    'text' => $this->getNestedListRecordTitle($record),
                ];
                $parent = $this->getParentKey($record);
                if ($parent > 0) {
                    $data['parent_id'] = $parent;
                }

                return $data;
            })
            ->toArray();
    }

    public function getRecords(): ?Collection
    {
        if ($this->records) {
            return $this->records;
        }

        return $this->records = $this->getSortedQuery()->get();
    }

    protected function getSortedQuery(): Builder
    {
        $query = $this->getWithRelationQuery();
        if (method_exists($this->getModel(), 'scopeOrdered')) {
            return $this->getWithRelationQuery()->ordered();
        }

        return $query;
    }

    protected function getWithRelationQuery(): Builder
    {
        $query = $this->getNestedListQuery();
        if (method_exists($this->getModel(), 'children') && $this->getModel()::has('children')) {
            return $query->with('children');
        }

        return $query;
    }

    protected function getNestedListQuery(): Builder
    {
        return $this->getModel()::query();
    }

    public function getNestedListRecord(?string $key): ?Model
    {
        return $this->resolveNestedListRecord($key);
    }

    protected function resolveNestedListRecord(?string $key): ?Model
    {
        if (null === $key) {
            return null;
        }

        return $this->getSortedQuery()->find($key);
    }

    public function getRootLayerRecords(): \Illuminate\Support\Collection
    {
        return collect($this->getRecords() ?? [])
            ->filter(function (Model $record) {
                if (method_exists($record, 'isRoot')) {
                    return $record->isRoot();
                }
                if (method_exists($record, 'determineParentColumnName')) {
                    return $record->getAttributeValue($record->determineParentColumnName()) === Utils::defaultParentId();
                }

                return $record->getAttributeValue('parent') === Utils::defaultParentId();
            });
    }
}
