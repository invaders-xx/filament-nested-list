@php
    $columns = [
        'default' => 1,
    ];
@endphp
<x-filament::page class="filament-nested-list-page">
    <x-filament::grid
            :default="$columns['default']"
            class="gap-4"
    >
        <x-filament::grid.column>
            {{ $this->nestedList }}
        </x-filament::grid.column>

    </x-filament::grid>
</x-filament::page>