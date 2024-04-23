<?php

namespace InvadersXX\FilamentNestedList\Support;

class Utils
{
    public static function orderColumnName(): string
    {
        return config('filament-nested-list.column_name.order', 'order');
    }

    /**
     * @deprecated Since v1.1.0
     */
    public static function depthColumnName(): string
    {
        return config('filament-nested-list.column_name.depth', 'depth');
    }

    public static function titleColumnName(): string
    {
        return config('filament-nested-list.column_name.title', 'title');
    }

    public static function defaultParentId(): int
    {
        return (int) config('filament-nested-list.default_parent_id', -1);
    }

    public static function buildNestedArray(
        array $nodes = [],
        int|string|null $parentId = null,
        ?string $primaryKeyName = null,
        ?string $parentKeyName = null,
        ?string $childrenKeyName = null
    ): array {
        $branch = [];
        $parentId = is_numeric($parentId) ? (int) $parentId : $parentId;
        // if (blank($parentId)) {
        //     $parentId = self::defaultParentId();
        // }
        $primaryKeyName = $primaryKeyName ?: 'id';
        $parentKeyName = $parentKeyName ?: static::parentColumnName();
        $childrenKeyName = $childrenKeyName ?: static::defaultChildrenKeyName();

        $nodeGroups = collect($nodes)->groupBy(fn ($node) => $node[$parentKeyName])->sortKeys();
        foreach ($nodeGroups as $pk => $nodeGroup) {
            $pk = is_numeric($pk) ? intval($pk) : $pk;
            if (
                ($pk === $parentId)
                // Allow parentId is nullable or negative number
                // https://github.com/solutionforest/filament-tree/issues/28
                || (($pk === '' || $pk <= 0) && $parentId <= 0)
            ) {
                foreach ($nodeGroup as $node) {
                    $node = collect($node)->toArray();

                    $branch[] = array_merge($node, [
                        // children
                        $childrenKeyName => static::buildNestedArray(
                            nodes: $nodes,
                            // children's parent id
                            parentId: $node[$primaryKeyName],
                            primaryKeyName: $primaryKeyName,
                            parentKeyName: $parentKeyName,
                            childrenKeyName: $childrenKeyName
                        ),
                    ]);
                }
            }
        }

        return $branch;
    }

    public static function parentColumnName(): string
    {
        return config('filament-nested-list.column_name.parent', 'parent_id');
    }

    public static function defaultChildrenKeyName(): string
    {
        return (string) config('filament-nested-list.default_children_key', 'children');
    }
}
