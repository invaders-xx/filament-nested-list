@php
    use Filament\Support\Facades\FilamentAsset;
    use Illuminate\Support\Js;
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? -1;
@endphp
<div>
    <div wire:ignore
         x-data="nestedList(JSON.parse('{{ json_encode($this->getNestedListData()) }}'), {{ $maxDepth }})">
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