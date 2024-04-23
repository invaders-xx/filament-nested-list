<?php

namespace InvadersXX\FilamentNestedList\Concern\TreeRecords;

trait HasActiveLocaleSwitcher
{
    public ?string $activeLocale = null;

    public ?array $translatableLocales = null;

    public function bootHasActiveLocaleSwitcher(): void
    {
        $this->setTranslatableLocales($this->getTranslatableLocales());
    }

    public function getTranslatableLocales(): array
    {
        return $this->translatableLocales ?? (
            method_exists(static::class, 'getResource')
                ? static::getResource()::getTranslatableLocales()
                : (
                    method_exists(static::class, 'getTranslatableLocales')
                        ? $this->getTranslatableLocales()
                        : []
                )
        );
    }

    public function setTranslatableLocales(array $locales): void
    {
        $this->translatableLocales = $locales;
    }
}
