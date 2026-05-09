<?php

namespace Azuriom\Plugin\SkinApi\Middleware;

use Azuriom\Plugin\SkinApi\Models\Cape;
use Azuriom\Plugin\SkinApi\Models\Skin;
use Azuriom\Plugin\SkinApi\Resources\CapeResource;
use Azuriom\Plugin\SkinApi\Resources\SkinResource;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeSkinIntoAuthResponse
{
    private const ROUTES = ['auth.authenticate'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs(self::ROUTES)) {
            return $next($request);
        }

        $response = $next($request);

        if (! ($response instanceof JsonResponse) || ! $response->isSuccessful()) {
            return $response;
        }

        $data = $response->getData(true);

        if (! is_array($data) || ! isset($data['id'], $data['access_token'])) {
            return $response;
        }

        $userId = $data['id'];
        $skin = Skin::forUser($userId);
        $cape = Cape::forUser($userId);

        return $response->setData(array_merge($data, [
            'skin' => $skin !== null ? new SkinResource($skin) : SkinResource::forDefault($userId),
            'cape' => $cape !== null ? new CapeResource($cape) : null,
        ]));
    }
}
