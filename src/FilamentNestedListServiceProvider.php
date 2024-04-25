<?php

namespace InvadersXX\FilamentNestedList;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Database\Schema\Blueprint;
use InvadersXX\FilamentNestedList\Macros\BlueprintMacros;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentNestedListServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-nested-list';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasTranslations()
            ->hasCommands([
                // Commands\MakeNestedListPageCommand::class,
                Commands\MakeNestedListWidgetCommand::class,
            ]);
    }

    public function boot()
    {
        parent::boot();

        $this->registerBlueprintMacros();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('filament-nested-list', __DIR__ . '/../resources/dist/filament-nested-list.css')->loadedOnRequest(),
            AlpineComponent::make('filament-nested-list', __DIR__ . '/../resources/dist/filament-nested-list.js'),
        ], 'invaders-xx/filament-nested-list');
    }

    protected function registerBlueprintMacros(): void
    {
        Blueprint::mixin(new BlueprintMacros());
    }
}
