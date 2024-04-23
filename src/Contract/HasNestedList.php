<?php

namespace InvadersXX\FilamentNestedList\Contract;

use Filament\Support\Contracts\TranslatableContentDriver;
use Illuminate\Database\Eloquent\Model;
use InvadersXX\FilamentNestedList\Components\NestedList;

interface HasNestedList
{
    public static function tree(NestedList $tree): NestedList;

    public function getModel(): string;

    public function updateTree(?array $list = null): void;

    public function getTreeRecordTitle(?Model $record = null): string;

    public function getRecordKey(?Model $record): ?string;

    public function makeFilamentTranslatableContentDriver(): ?TranslatableContentDriver;
}