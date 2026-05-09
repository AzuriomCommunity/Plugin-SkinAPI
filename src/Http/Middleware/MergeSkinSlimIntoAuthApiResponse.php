<?php

namespace Azuriom\Plugin\SkinApi\Http\Middleware;

use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\SkinAPI;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeSkinSlimIntoAuthApiResponse
{
    private const ROUTES = ['auth.authenticate'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $route = $request->route();
        if ($route === null || ! in_array($route->getName(), self::ROUTES, true)) {
            return $response;
        }

        if ($response->getStatusCode() !== 200) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || $content === '') {
            return $response;
        }

        $data = json_decode($content, true);
        if (! is_array($data) || ! isset($data['id'], $data['access_token'])) {
            return $response;
        }

        $userId = (int) $data['id'];
        $skin = Skin::forUser($userId);

        $data['skin'] = [
            'slim' => $skin?->slim ?? SkinAPI::getUserSkinType($userId),
        ];

        $response->setContent(json_encode($data));

        return $response;
    }
}
