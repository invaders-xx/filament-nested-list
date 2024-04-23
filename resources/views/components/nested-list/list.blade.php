@props([
    'records',
    'tree',
])
<div class="group">
    @foreach ($records ?? [] as $record)
        @php
            $title = $this->getTreeRecordTitle($record);
            $icon = $this->getTreeRecordIcon($record);
        @endphp

        <x-filament-nested-list::tree.item :record="$record" :tree="$tree" :title="$title" :icon="$icon" />
    @endforeach
</div>
