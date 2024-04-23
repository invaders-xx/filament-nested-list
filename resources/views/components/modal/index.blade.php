@php use function Filament\Support\prepare_inherited_attributes; @endphp
@php use Filament\Facades\Filament; @endphp
@captureSlots([
'actions',
'content',
'footer',
'header',
'heading',
'subheading',
'trigger',
])

<x-filament::modal
        :attributes="prepare_inherited_attributes($attributes)->merge($slots)"
        :dark-mode="Filament::hasDarkMode()"
        heading-component="filament-nested-list::modal.heading"
        {{-- hr-component="tables::hr" --}}
        subheading-component="filament-nested-list::modal.subheading"
>
    {{ $slot }}
</x-filament::modal>
