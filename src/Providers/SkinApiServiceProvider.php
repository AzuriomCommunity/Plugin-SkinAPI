<?php

namespace Azuriom\Plugin\SkinApi\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Azuriom\Plugin\SkinApi\Cards\ChangeSkinViewCard;

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

        // $this->loadMigrations();

        Permission::registerPermissions([
            'skin-api.manage' => 'skin-api::admin.permissions.manage',
        ]);

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();
        
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
                    'skin-api.admin.home' => trans('admin.nav.settings.settings'),
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
        return [
            'skin' => [
                'route' => 'skin-api.home',
                'name' => trans('skin-api::messages.title'),
            ],
        ];
    }
}
