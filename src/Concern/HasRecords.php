<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Support\Utils;

trait HasRecords
{
    protected ?Collection $records = null;

    public function getTreeRecord(?string $key): ?Model
    {
        return $this->resolveTreeRecord($key);
    }

    protected function resolveTreeRecord(?string $key): ?Model
    {
        if ($key === null) {
            return null;
        }

        return $this->getSortedQuery()->find($key);
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
        $query = $this->getTreeQuery();
        if (method_exists($this->getModel(), 'children') && $this->getModel()::has('children')) {
            return $query->with('children');
        }

        return $query;
    }

    protected function getTreeQuery(): Builder
    {
        return $this->getModel()::query();
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

    public function getRecords(): ?Collection
    {
        if ($this->records) {
            return $this->records;
        }

        return $this->records = $this->getSortedQuery()->get();
    }
}
