@php
    use Illuminate\Database\Eloquent\Model;
    use Filament\Facades\Filament;
    use InvadersXX\FilamentNestedList\Components\NestedList;
@endphp

@props([
    'record',
    'containerKey',
    'tree',
    'title' => null,
    'icon' => null,
])
@php
    /** @var $record Model */
    /** @var $containerKey string */
    /** @var $tree NestedList */

    $recordKey = $tree->getRecordKey($record);
    $parentKey = $tree->getParentKey($record);

    $children = $record->children;
    $hasChildren = (bool) count($children);

    $actions = $tree->getActions();
@endphp

@if ($hasChildren)
    <li x-data="{ collapse: false }" data-id="{{ $recordKey }}" data-parent="{{ $parentKey }}">
        <div
            wire:loading.remove.delay
            wire:target="{{ implode(',', NestedList::LOADING_TARGETS) }}"
            @class([
                'h-10 rounded-lg border',
                'mb-2',
                'flex w-full items-center',
                'border-gray-300 bg-white dark:border-white/10 dark:bg-gray-900',
            ])
        >
            <div
                @class([
                    'handle flex h-full items-center hover:cursor-move',
                    'rounded-l-lg border-r px-px rtl:rounded-l rtl:border-l rtl:border-r-0',
                    'border-gray-300 bg-gray-50 dark:border-white/10 dark:bg-white/5',
                ])
            >
                <x-heroicon-m-ellipsis-vertical
                    class="-mr-2 h-4 w-4 text-gray-400 dark:text-gray-500 rtl:-ml-2 rtl:mr-0"
                />
                <x-heroicon-m-ellipsis-vertical class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            </div>

            <div class="content flex gap-1">
                @if ($icon)
                    <div class="w-4">
                        <x-dynamic-component :component="$icon" class="h-4 w-4" />
                    </div>
                @endif

                <span
                    @class([
                        'ml-4 rtl:mr-4' => ! $icon,
                        'font-semibold',
                    ])
                >
                    {{ $title }}
                </span>

                <div @class(['hidden' => ! $hasChildren, 'flex items-center justify-center pl-3'])>
                    <button x-show="!collapse" x-on:click="collapse = !collapse">
                        <x-heroicon-o-chevron-down class="h-4 w-4 text-gray-400" />
                    </button>
                    <button x-show="collapse" x-on:click="collapse = !collapse">
                        <x-heroicon-o-chevron-up class="h-4 w-4 text-gray-400" />
                    </button>
                </div>
            </div>

            @if (count($actions))
                <div class="ml-auto rtl:ml-0 rtl:mr-auto">
                    <x-filament-nested-list::actions :actions="$actions" :record="$record" />
                </div>
            @endif
        </div>
        <ol x-show="collapse" data-id="{{ $recordKey }}" data-parent="{{ $parentKey }}">
            <x-filament-nested-list::tree.list :records="$children" :tree="$tree" />
        </ol>
    </li>
@else
    <li data-id="{{ $recordKey }}" data-parent="{{ $parentKey }}">
        <div
            wire:loading.remove.delay
            wire:target="{{ implode(',', NestedList::LOADING_TARGETS) }}"
            @class([
                'h-10 rounded-lg border',
                'mb-2',
                'flex w-full items-center',
                'border-gray-300 bg-white dark:border-white/10 dark:bg-gray-900',
            ])
        >
            <div
                @class([
                    'handle flex h-full items-center hover:cursor-move',
                    'rounded-l-lg border-r px-px rtl:rounded-l rtl:border-l rtl:border-r-0',
                    'border-gray-300 bg-gray-50 dark:border-white/10 dark:bg-white/5',
                ])
            >
                <x-heroicon-m-ellipsis-vertical
                    class="-mr-2 h-4 w-4 text-gray-400 dark:text-gray-500 rtl:-ml-2 rtl:mr-0"
                />
                <x-heroicon-m-ellipsis-vertical class="h-4 w-4 text-gray-400 dark:text-gray-500" />
            </div>

            <div class="content flex gap-1">
                @if ($icon)
                    <div class="w-4">
                        <x-dynamic-component :component="$icon" class="h-4 w-4" />
                    </div>
                @endif

                <span
                    @class([
                        'ml-4 rtl:mr-4' => ! $icon,
                        'font-semibold',
                    ])
                >
                    {{ $title }}
                </span>
            </div>

            @if (count($actions))
                <div class="ml-auto rtl:ml-0 rtl:mr-auto">
                    <x-filament-nested-list::actions :actions="$actions" :record="$record" />
                </div>
            @endif
        </div>
    </li>
@endif
