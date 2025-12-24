<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class OptionalSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        if ($token = $request->bearerToken()) {
            $accessToken = PersonalAccessToken::findToken($token);

            if ($accessToken) {
                // ✅ SPRÁVNĚ: nastavíme usera pro request
                $request->setUserResolver(fn() => $accessToken->tokenable);
            }
        }

        return $next($request);
    }
}
