<?php

namespace Azuriom\Plugin\SkinApi\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Games\Minecraft\MinecraftOfflineGame;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\SkinApi\Cards\ChangeSkinCapeCard;
use Azuriom\Plugin\SkinApi\Http\Middleware\MergeSkinSlimIntoAuthApiResponse;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\Render\AvatarRenderer;
use Azuriom\Plugin\SkinApi\Render\RenderType;
use Illuminate\Contracts\Http\Kernel;
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
            $skin = Skin::forUser($user->id);
            $disk = Storage::disk('public');

            if ($skin === null) {
                return $disk->exists('skins/face/default.png')
                    ? url($disk->url('skins/face/default.png'))
                    : plugin_asset('skin-api', 'img/face_steve.png');
            }

            $faceKey = "skins/face/{$skin->file}";

            if (! $disk->exists($faceKey)) {
                $skinPath = $skin->getDisk()->path($skin->getPath());
                AvatarRenderer::render(RenderType::AVATAR, $skinPath, $skin->file, $skin->slim);
            }

            return url($disk->url($faceKey).'?h='.$skin->updated_at->timestamp);
        });
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $kernel = $this->app->make(Kernel::class);
        $kernel->appendMiddlewareToGroup('api', MergeSkinSlimIntoAuthApiResponse::class);

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerAdminNavigation();

        $this->registerUserNavigation();

        Permission::registerPermissions([
            'skin-api.skin' => 'skin-api::admin.permissions.skin',
            'skin-api.cape' => 'skin-api::admin.permissions.cape',
            'admin.skin-api' => 'skin-api::admin.permissions.manage',
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
                'permission' => 'skin-api.skin',
                'icon' => 'bi bi-person-square',
            ],
        ];
    }
}
