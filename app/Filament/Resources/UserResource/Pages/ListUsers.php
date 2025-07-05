<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('All Users'))
                ->icon('phosphor-users-duotone'),

            'admins' => Tab::make(__('Admins'))
                ->icon('phosphor-shield-star-duotone')
                ->modifyQueryUsing(fn ($query) => $query->role(UserRole::ADMIN)),

            'employees' => Tab::make(__('Employees'))
                ->icon('phosphor-storefront-duotone')
                ->modifyQueryUsing(fn ($query) => $query->role(UserRole::EMPLOYEE)),

            'customers' => Tab::make(__('Customers'))
                ->icon('phosphor-user-duotone')
                ->modifyQueryUsing(fn ($query) => $query->role(UserRole::CUSTOMER)),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
