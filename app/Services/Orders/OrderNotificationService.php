<?php

declare(strict_types=1);

namespace App\Services\Orders;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Notifications\Notification;

final class OrderNotificationService
{
    /**
     * Send new order notification to branch employees.
     */
    public function sendNewOrderNotification(string|int $branchId): void
    {
        User::query()
            ->role(UserRole::EMPLOYEE)
            ->where('branch_id', $branchId)
            ->each(function (User $employee): void {
                $employee->notify(
                    Notification::make('new_order')
                        ->success()
                        ->title(__('New order'))
                        ->body(__('A new order has been placed.'))
                        ->toDatabase()
                );
            });
    }
}
