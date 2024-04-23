<?php

namespace InvadersXX\FilamentNestedList\Commands;

use Illuminate\Console\Command;

class FilamentNestedListCommand extends Command
{
    public $signature = 'filament-nested-list';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
