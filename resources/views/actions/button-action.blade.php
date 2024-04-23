<x-filament-nested-list::actions.action
        :action="$action"
        dynamic-component="filament::button"
        :outlined="$isOutlined()"
        :labeled-from="$getLabeledFromBreakpoint()"
        :icon-position="$getIconPosition()"
        :icon-size="$getIconSize()"
        class="filament-nested-list-button-action"
>
    {{ $getLabel() }}
</x-filament-nested-list::actions.action>

