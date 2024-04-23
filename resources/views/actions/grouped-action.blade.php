<x-filament-nested-list::actions.action
        :action="$action"
        dynamic-component="filament::dropdown.list.item"
        :icon="$getGroupedIcon()"
        class="filament-grouped-action"
>
    {{ $getLabel() }}
</x-filament-nested-list::actions.action>
