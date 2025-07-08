<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Jeffgreco13\FilamentBreezy\BreezyCore;

class HubPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('hub')
            ->path('hub')
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Teal,
                'success' => Color::hex('#238273'),
                'danger' => Color::Rose,
                'warning' => Color::hex('#ffb347'),
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        hasAvatars: true
                    ),
            ])
            ->font('IBM Plex Sans Arabic')
            ->theme(asset('css/filament/hub/theme.css'))
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label(__('Products Management'))
                    ->icon('phosphor-bookmarks-duotone')
                    ->collapsible(false),

                NavigationGroup::make()
                    ->label(__('Users Management'))
                    ->icon('phosphor-users-duotone')
                    ->collapsible(true),
            ])
            ->navigationItems([
                NavigationItem::make('admins')
                    ->label(__('Admins'))
                    ->isActiveWhen(fn (): bool => 'admins' === request()->get('activeTab'))
                    ->url(fn (): string => UserResource::getUrl('index') . '?activeTab=admins')
                    ->group(fn (): string => __('Users Management'))
                    ->visible(fn (): bool => auth()->user()->can('view-any', User::class)),

                NavigationItem::make('employees')
                    ->label(__('Employees'))
                    ->isActiveWhen(fn (): bool => 'employees' === request()->get('activeTab'))
                    ->url(fn (): string => UserResource::getUrl('index') . '?activeTab=employees')
                    ->group(fn (): string => __('Users Management'))
                    ->visible(fn (): bool => auth()->user()->can('view-any', User::class)),

                NavigationItem::make('customers')
                    ->label(__('Customers'))
                    ->isActiveWhen(fn (): bool => 'customers' === request()->get('activeTab'))
                    ->url(fn (): string => UserResource::getUrl('index') . '?activeTab=customers')
                    ->group(fn (): string => __('Users Management'))
                    ->visible(fn (): bool => auth()->user()->can('view-any', User::class)),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
