<?php

namespace Azuriom\Plugin\SkinApi\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Azuriom\Plugin\SkinApi\Cards\ChangeSkinViewCard;
use Azuriom\Support\SettingsRepository;

class SkinApiServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     *
     * @return void
     */
    public function register()
    {
        // Due to the "random" order of ServiceProvider boot
        // we need to make sure that the GameServiceProvider has booted
        // thus after the app is booted.
        $this->app->booted(function ($app): void {
            if (game()->id() === 'mc-offline') {
                game()->setAvatarRetriever(function (User $user, int $size = 64) {
                    if (! Storage::disk('public')->exists("skins/{$user->id}.png")) {
                        return plugin_asset('skin-api', 'img/face_steve.png');
                    }

                    // if the avatar does not exist or the skin is more recent than the avatar
                    if (! Storage::disk('public')->exists("face/{$user->id}.png")
                        || Storage::disk('public')->lastModified("skins/{$user->id}.png") > Storage::disk('public')->lastModified("face/{$user->id}.png")) {
                        SkinAPI::makeAvatarWithTypeForUser('face', $user->id);
                    }

                    return url(Storage::disk('public')->url("face/{$user->id}.png"));
                });
            }
        });
    }

    /**
     * Bootstrap any plugin services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();

        // Initialize default settings if not set
        $settings = app(SettingsRepository::class);
        
        if (!$settings->has('skin.width')) {
            $settings->set('skin.width', 64);
        }
        if (!$settings->has('skin.height')) {
            $settings->set('skin.height', 64);
        }
        if (!$settings->has('skin.scale')) {
            $settings->set('skin.scale', 1);
        }

        Permission::registerPermissions([
            'admin.skin-api' => 'skin-api::admin.permissions.admin',
        ]);

        // Register the skin change card in user profile
        View::composer('profile.index', ChangeSkinViewCard::class);
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array
     */
    protected function routeDescriptions()
    {
        return [
            'skin-api.home' => trans('skin-api::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array
     */
    protected function adminNavigation()
    {
        return [
            'skin-api' => [
                'name' => 'Skin-Api',
                'type' => 'dropdown',
                'icon' => 'bi bi-images',
                'route' => 'skin-api.admin.*',
                'items' => [
                    'skin-api.admin.skins' => 'Skins',
                    'skin-api.admin.capes' => 'Capes',
                ],
                'permission' => 'skin-api.manage',
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     *
     * @return array
     */
    protected function userNavigation()
    {
        $navigation = [];

        // Add skin navigation if enabled
        if (setting('skin.show_nav_icon', true)) {
            $navigation['skin'] = [
                'route' => 'skin-api.home',
                'name' => trans('skin-api::messages.title'),
                'icon' => setting('skin.navigation_icon', ' '),
            ];
        }

        // Add cape navigation if enabled
        if (setting('skin.cape_show_nav_button', true)) {
            $navigation['cape'] = [
                'route' => 'skin-api.capes',
                'name' => trans('skin-api::messages.capes'),
                'icon' => setting('skin.cape_nav_icon', ' '),
            ];
        }

        return $navigation;
    }
}
