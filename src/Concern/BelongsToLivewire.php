<?php

namespace InvadersXX\FilamentNestedList\Concern;

use Filament\Support\Contracts\TranslatableContentDriver;
use InvadersXX\FilamentNestedList\Contract\HasNestedList;

trait BelongsToLivewire
{
    protected HasNestedList $livewire;

    public function livewire(HasNestedList $livewire): static
    {
        $this->livewire = $livewire;

        return $this;
    }

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver
    {
        return $this->getLivewire()->makeFilamentTranslatableContentDriver();
    }

    public function getLivewire(): HasNestedList
    {
        return $this->livewire;
    }
}
