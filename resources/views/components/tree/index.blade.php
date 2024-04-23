@php
    use Illuminate\Support\Js;
    $containerKey = 'filament_tree_container_' . $this->getId();
    $maxDepth = $getMaxDepth() ?? -1;
    $data = collect($this->getRecords() ?? [])
        ->map(
            fn ($record) => [
                'id' => $record->id,
                'parent_id' => $this->getParentKey($record),
                'text' => $this->getTreeRecordTitle($record),
            ],
        )
        ->toArray();
    ray($data);
@endphp

<div
    wire:ignore
    x-data="{
        items: JSON.parse('{{ json_encode($data) }}'),
        save() {
            $wire.updateTree(this.items)
        },
        init() {
            let nestedSortables = new NestedSort({
                el: '#nestedList',
                data: JSON.parse('{{ json_encode($data) }}'),
                nestingLevels: {{ $maxDepth }},
                propertyMap: {
                    parent: 'parent_id',
                },
                actions: {
                    onDrop: function (data) {
                        $data.items = data
                    },
                },
            })
        },
    }"
>
    <x-filament::section :heading="($this->displayTreeTitle() ?? false) ? $this->getTreeTitle() : null">
        <menu class="mb-4 flex gap-2" id="nestable-menu">
            <x-filament::button
                tag="button"
                x-on:click="save"
                wire:loading.attr="disabled"
                wire:loading.class="cursor-wait opacity-70"
            >
                <x-filament::loading-indicator class="h-4 w-4" wire:loading wire:target="saveTree" />
                <span wire:loading.remove wire:target="saveTree">
                    {{ __('filament-nested-list::filament-nested-list.button.save') }}
                </span>
            </x-filament::button>
        </menu>

        <div id="nestedList"></div>
    </x-filament::section>
</div>

<form wire:submit.prevent="callMountedTreeAction">
    @php
        $action = $this->getMountedTreeAction();
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
        :id="$this->getId() . '-tree-action'"
        :slide-over="$action?->isModalSlideOver()"
        :sticky-footer="$action?->isModalFooterSticky()"
        :sticky-header="$action?->isModalHeaderSticky()"
        :visible="filled($action)"
        :width="$action?->getModalWidth()"
        :wire:key="$action ? $this->getId() . '.tree.actions.' . $action->getName() . '.modal' : null"
        x-on:closed-form-component-action-modal.window="if (($event.detail.id === '{{ $this->getId() }}') && $wire.mountedTreeActions.length) open()"
        x-on:modal-closed.stop="
            const mountedTreeActionShouldOpenModal = {{ Js::from($action && $this->mountedTreeActionShouldOpenModal()) }}

            if (! mountedTreeActionShouldOpenModal) {
                return
            }

            if ($wire.mountedFormComponentActions.length) {
                return
            }

            $wire.unmountTreeAction(false)
        "
        x-on:opened-form-component-action-modal.window="if ($event.detail.id === '{{ $this->getId() }}') close()"
    >
        @if ($action)
            {{ $action->getModalContent() }}

            @if (count(($infolist = $action->getInfolist())?->getComponents() ?? []))
                {{ $infolist }}
            @elseif ($this->mountedTreeActionHasForm())
                {{ $this->getMountedTreeActionForm() }}
            @endif

            {{ $action->getModalContentFooter() }}
        @endif
    </x-filament::modal>
</form>
