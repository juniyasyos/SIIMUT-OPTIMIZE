<?php

namespace App\Filament\Resources\FolderCustomResource\Pages;

use App\Filament\Resources\FolderCustomResource;
use Filament\Actions;
use Illuminate\Support\Facades\Gate;
use Juniyasyos\FilamentMediaManager\Resources\FolderResource\Pages\ListFolders;

class ListFoldersCustom extends ListFolders
{
    protected static string $resource = FolderCustomResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        $user = auth()->user();

        return $user->can('view_all_folder::custom') || $user->can('view_by_unit_kerja_folder::custom');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => Gate::any(['create_folder::custom'])),
        ];
    }

    // protected function getHeaderActions(): array
    // {
    //     dd([
    //         'user_permissions' => \Illuminate\Support\Facades\Auth::user()?->getAllPermissions()->pluck('name'),
    //         'all_permissions' => \Spatie\Permission\Models\Permission::all()->pluck('name'),
    //     ]);

    //     return [
    //         Actions\CreateAction::make()
    //             ->visible(fn () => Gate::any(['create_folder'])),
    //     ];
    // }

    public function getBreadcrumbs(): array
    {
        return [
            url('/') => 'Dashboard',
            'folders',
        ];
    }
}
