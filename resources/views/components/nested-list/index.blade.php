@php
    use Filament\Support\Facades\FilamentAsset;
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? -1;
    ray($this->getNestedListData());
@endphp

<div x-data="{}"
     x-load-css="[@js(FilamentAsset::getStyleHref('filament-nested-list', package: 'invaders-xx/filament-nested-list'))]"
>
    <div wire:ignore
         x-ignore
         ax-load
         ax-load-src="{{ FilamentAsset::getAlpineComponentSrc('filament-nested-list', 'invaders-xx/filament-nested-list') }}"
         x-data="nestedList({
             items: @js($this->getNestedListData()),
             maxDepth: {{ $maxDepth }},
             selector: '#nestedList'
         })"
    >
        <x-filament::section :heading="($this->displayNestedListTitle() ?? false) ? $this->getNestedListTitle() : null">
            <menu class="mb-4 flex gap-2" id="nestable-menu">
                <x-filament::button
                        tag="button"
                        x-on:click="save"
                        wire:loading.attr="disabled"
                        wire:loading.class="cursor-wait opacity-70"
                >
                    <x-filament::loading-indicator class="h-4 w-4" wire:loading wire:target="updateNestedList"/>
                    <span wire:loading.remove wire:target="updateNestedList">
                    {{ __('filament-nested-list::filament-nested-list.button.save') }}
                </span>
                </x-filament::button>
            </menu>
            <div id="nestedList"></div>
        </x-filament::section>
    </div>
</div>