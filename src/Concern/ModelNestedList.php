<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvadersXX\FilamentNestedList\Support\Utils;
use InvalidArgumentException;

trait ModelNestedList
{
    use SupportTranslation {
        SupportTranslation::handleTranslatable as traitHandleTranslatable;
    }

    /**
     * Boot the bootable traits on the model.
     */
    public static function bootModelNestedList()
    {
        static::saving(function (Model $model) {
            if (empty($model->{$model->determineParentColumnName()}) || $model->{$model->determineParentColumnName()} === -1) {
                $model->{$model->determineParentColumnName()} = static::defaultParentKey();
            }
            if (empty($model->{$model->determineOrderColumnName()}) || $model->{$model->determineOrderColumnName()} === 0) {
                $model->setHighestOrderNumber();
            }
        });

        // Delete children
        static::deleting(function (Model $model) {
            static::buildSortQuery()
                ->where($model->determineParentColumnName(), $model->getKey())
                ->get()
                ->each
                ->delete();
        });
    }

    public function determineParentColumnName(): string
    {
        return Utils::parentColumnName();
    }

    public static function defaultParentKey()
    {
        return Utils::defaultParentId();
    }

    public function determineOrderColumnName(): string
    {
        return Utils::orderColumnName();
    }

    public function setHighestOrderNumber(): void
    {
        $this->{$this->determineOrderColumnName()} = $this->getHighestOrderNumber() + 1;
    }

    public function getHighestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->where($this->determineParentColumnName(), $this->{$this->determineParentColumnName()})->max($this->determineOrderColumnName());
    }

    public static function buildSortQuery(): Builder
    {
        return static::query()->ordered();
    }

    /**
     * Get tree nodes options.
     */
    public static function nestedListNodes(): array
    {
        $result = [];

        $model = app(static::class);

        [$primaryKeyName, $titleKeyName, $parentKeyName, $childrenKeyName] = [
            $model->getKeyName(),
            $model->determineTitleColumnName(),
            $model->determineParentColumnName(),
            static::defaultChildrenKeyName(),
        ];

        $nodes = Utils::buildNestedArray(
            nodes: static::allNodes(),
            parentId: static::defaultParentKey(),
            primaryKeyName: $primaryKeyName,
            parentKeyName: $parentKeyName,
            childrenKeyName: $childrenKeyName
        );

        foreach ($nodes as $node) {
            static::buildNestedListNodeItem($result, $node, $primaryKeyName, $titleKeyName, $childrenKeyName);
        }

        return $result;
    }

    public function determineTitleColumnName(): string
    {
        return Utils::titleColumnName();
    }

    public static function defaultChildrenKeyName(): string
    {
        return Utils::defaultChildrenKeyName();
    }

    /**
     * @return static[]|Collection
     */
    public static function allNodes()
    {
        return static::buildSortQuery()->get();
    }

    private static function buildNestedListNodeItem(array &$final, array $item, string $primaryKeyName, string $titleKeyName, string $childrenKeyName): void
    {
        if (! isset($item[$primaryKeyName])) {
            throw new InvalidArgumentException("Unset '{$primaryKeyName}' primary key.");
        }
        $pk = data_get($item, $primaryKeyName);
        $name = data_get($item, $titleKeyName);
        $children = [];

        if (count($item[$childrenKeyName])) {
            foreach ($item[$childrenKeyName] as $child) {
                static::buildNestedListNodeItem($children, $child, $primaryKeyName, $titleKeyName, $childrenKeyName);
            }
        }
        $final[] = [
            $primaryKeyName => $pk,
            $titleKeyName => $name,
            $childrenKeyName => $children,
        ];
    }

    /**
     * Get select array options.
     */
    public static function selectArray(?int $maxDepth = null): array
    {
        $result = [];

        $model = app(static::class);

        [$primaryKeyName, $titleKeyName, $parentKeyName, $childrenKeyName] = [
            $model->getKeyName(),
            $model->determineTitleColumnName(),
            $model->determineParentColumnName(),
            static::defaultChildrenKeyName(),
        ];

        $nodes = Utils::buildNestedArray(
            nodes: static::allNodes(),
            parentId: static::defaultParentKey(),
            primaryKeyName: $primaryKeyName,
            parentKeyName: $parentKeyName,
            childrenKeyName: $childrenKeyName
        );

        $result[static::defaultParentKey()] = __('filament-nested-list::filament-nested-list.root');

        foreach ($nodes as $node) {
            static::buildSelectArrayItem($result, $node, $primaryKeyName, $titleKeyName, $childrenKeyName, 1, $maxDepth);
        }

        return $result;
    }

    private static function buildSelectArrayItem(array &$final, array $item, string $primaryKeyName, string $titleKeyName, string $childrenKeyName, int $depth, ?int $maxDepth = null): void
    {
        if (! isset($item[$primaryKeyName])) {
            throw new InvalidArgumentException("Unset '{$primaryKeyName}' primary key.");
        }

        if ($maxDepth && $depth > $maxDepth) {
            return;
        }

        static::handleTranslatable($item);

        $key = $item[$primaryKeyName];
        $title = isset($item[$titleKeyName]) ? $item[$titleKeyName] : $item[$primaryKeyName];
        if (! is_string($title)) {
            $title = (string) $title;
        }
        $final[$key] = Str::padLeft($title, (str($title)->length() + ($depth * 3)), '-');

        if (count($item[$childrenKeyName])) {
            foreach ($item[$childrenKeyName] as $child) {
                static::buildSelectArrayItem($final, $child, $primaryKeyName, $titleKeyName, $childrenKeyName, $depth + 1, $maxDepth);
            }
        }
    }

    public static function handleTranslatable(array &$final): void
    {
        static::traitHandleTranslatable($final, static::class);
    }

    public function initializeModelTree()
    {
        if (! empty($this->getFillable())) {
            $this->mergeFillable([
                $this->determineOrderColumnName(),
                $this->determineParentColumnName(),
                $this->determineTitleColumnName(),
            ]);
        }
    }

    public function children()
    {
        return $this->hasMany(static::class, $this->determineParentColumnName())->with('children')->orderBy($this->determineOrderColumnName());
    }

    public function isRoot(): bool
    {
        return $this->getAttributeValue($this->determineParentColumnName()) === static::defaultParentKey();
    }

    public function getLowestOrderNumber(): int
    {
        return (int) $this->buildSortQuery()->where($this->determineParentColumnName(), $this->{$this->determineParentColumnName()})->min($this->determineOrderColumnName());
    }

    public function scopeOrdered(Builder $query, string $direction = 'asc')
    {
        return $query->orderBy($this->determineParentColumnName(), 'asc')->orderBy($this->determineOrderColumnName(), $direction);
    }

    public function scopeIsRoot(Builder $query)
    {
        return $query->where($this->determineParentColumnName(), static::defaultParentKey());
    }

    /**
     * Format all nodes as tree.
     *
     * @param  array|Collection|null  $nodes
     */
    public function toTree($nodes = null): array
    {
        if ($nodes === null) {
            $nodes = static::allNodes();
        }

        return Utils::buildNestedArray(
            nodes: $nodes,
            parentId: static::defaultParentKey(),
            primaryKeyName: $this->getKeyName(),
            parentKeyName: $this->determineParentColumnName()
        );
    }
}
