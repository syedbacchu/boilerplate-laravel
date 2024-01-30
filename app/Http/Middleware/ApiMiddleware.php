<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->header('lang') ?? 'en';
        app()->setLocale($lang);
        $key = env('API_SECRET_KEY', 'ItsanapiSecretKeyForSecuritingTheApi');

        if (!empty($request->header('apisecretkeycheck')) && $request->header('apisecretkeycheck') == $key) {
            return $next($request);
        } else {
            return response()->json(['success' => false, 'message' => __('Invalid key found')]);
        }

    }
}
