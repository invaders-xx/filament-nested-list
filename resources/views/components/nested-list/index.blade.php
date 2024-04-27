@php
    use Filament\Support\Facades\FilamentAsset;
    use Illuminate\Support\Js;
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? -1;
    $data = collect($this->getRecords() ?? [])
        ->map(function ($record) {
            $data = [
                'id' => $record->id,
                'order' => $record->order,
                'text' => $this->getNestedListRecordTitle($record),
            ];
            $parent = $this->getParentKey($record);
            if ($parent > 0) {
                $data['parent_id'] = $parent;
            }
            return $data;
        })
        ->toArray();
@endphp
<div>
    <div wire:ignore
         x-data="nestedList(JSON.parse('{{ json_encode($data) }}'), {{ $maxDepth }})">
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
    <form wire:submit.prevent="callMountedNestedListAction">
        @php
            $action = $this->getMountedNestedListAction();
        @endphp

        <x-filament::modal
                :alignment="$action?->getModalAlignment()"
                :close-button="$action?->hasModalCloseButton()"
                :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
                :description="$action?->getModalDescription()"
                display-classes="block"
                :footer-actions="$action?->getVisibleModalFooterActions()"
                :footer-actions-alignment="$action?->getModalFooterActionsAlignment()"
                :heading="$action?->getModalHeading()"
                :icon="$action?->getModalIcon()"
                :icon-color="$action?->getModalIconColor()"
                :id="$this->getId() . '-nested-list-action'"
                :slide-over="$action?->isModalSlideOver()"
                :sticky-footer="$action?->isModalFooterSticky()"
                :sticky-header="$action?->isModalHeaderSticky()"
                :visible="filled($action)"
                :width="$action?->getModalWidth()"
                :wire:key="$action ? $this->getId() . '.nested-list.actions.' . $action->getName() . '.modal' : null"
                x-on:closed-form-component-action-modal.window="if (($event.detail.id === '{{ $this->getId() }}') && $wire.mountedNestedListActions.length) open()"
                x-on:modal-closed.stop="
    const mountedNestedListActionShouldOpenModal = {{ Js::from($action && $this->mountedNestedListActionShouldOpenModal()) }}

    if (! mountedNestedListActionShouldOpenModal) {
        return
    }

    if ($wire.mountedFormComponentActions.length) {
        return
    }

    $wire.unmountNestedListAction(false)
"
                x-on:opened-form-component-action-modal.window="if ($event.detail.id === '{{ $this->getId() }}') close()"
        >
            @if ($action)
                {{ $action->getModalContent() }}

                @if (count(($infolist = $action->getInfolist())?->getComponents() ?? []))
                    {{ $infolist }}
                @elseif ($this->mountedNestedListActionHasForm())
                    {{ $this->getMountedNestedListActionForm() }}
                @endif

                {{ $action->getModalContentFooter() }}
            @endif
        </x-filament::modal>
    </form>
</div>