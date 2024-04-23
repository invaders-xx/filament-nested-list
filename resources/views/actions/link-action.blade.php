<x-filament-nested-list::actions.action
        :action="$action"
        dynamic-component="filament::link"
        :icon-position="$getIconPosition()"
        :icon-size="$getIconSize()"
        class="filament-nested-list-link-action"
>
    {{ $getLabel() }}
</x-filament-nested-list::actions.action>
