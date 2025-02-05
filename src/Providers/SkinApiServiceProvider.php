<?php

namespace Azuriom\Plugin\SkinApi\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Games\Minecraft\MinecraftOfflineGame;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Cards\ChangeSkinCapeCard;
use Azuriom\Plugin\SkinApi\Render\RenderType;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class SkinApiServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        MinecraftOfflineGame::setAvatarRetriever(function (User $user, int $size = 64) {
            $userId = $user->id;

            if (! Storage::disk('public')->exists("skins/{$user->id}.png")) {
                if (SkinAPI::defaultSkin() === null) {
                    return plugin_asset('skin-api', 'img/face_steve.png');
                }

                $userId = 'default';
             }

            $lastModified = Storage::disk('public')->lastModified("skins/{$userId}.png");

            // if the avatar does not exist or the skin is more recent than the avatar
            if (! Storage::disk('public')->exists("face/{$userId}.png")
                || $lastModified > Storage::disk('public')->lastModified("face/{$userId}.png")) {
                SkinAPI::makeAvatarWithTypeForUser(RenderType::AVATAR, $userId);
            }

            $hash = $lastModified ? '?h='.substr($lastModified, 4) : '';

            return url(Storage::disk('public')->url("face/{$userId}.png{$hash}"));
        });
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $this->loadViews();

        $this->loadTranslations();

        // $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();

        Permission::registerPermissions([
            'skin-api.cape' => 'skin-api::admin.permissions.cape',
            'admin.skin-api' => 'skin-api::admin.permissions.admin',
        ]);

        View::composer('profile.index', ChangeSkinCapeCard::class);
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     */
    protected function routeDescriptions(): array
    {
        return [
            'skin-api.home' => trans('skin-api::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     */
    protected function adminNavigation(): array
    {
        return [
            'skin-api' => [
                'name' => 'Skin API',
                'type' => 'dropdown',
                'icon' => 'bi bi-person-square',
                'route' => 'skin-api.admin.*',
                'items' => [
                    'skin-api.admin.skins' => trans('skin-api::admin.skins'),
                    'skin-api.admin.capes' => trans('skin-api::admin.capes'),
                ],
                'permission' => 'skin-api.manage',
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     */
    protected function userNavigation(): array
    {
        return [
            'skin' => [
                'route' => 'skin-api.home',
                'name' => trans('skin-api::messages.title'),
                'icon' => 'bi bi-person-square',
            ],
        ];
    }
}
