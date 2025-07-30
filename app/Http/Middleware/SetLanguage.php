<?php

namespace App\Http\Middleware;

use Closure;

use App\Facades\Config;

class SetLanguage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $url = parse_url(request()->header('Referer', request()->header('Origin')), PHP_URL_HOST);
        $sites = Config::getOrFail('ots.site_languages');
        if (!empty($sites[$url]))
        {
            $language = $sites[$url];
        }
        else
        {
            $language = Config::getOrFail('app.fallback_locale');
        }

        app()->setLocale($language);

        return $next($request);
    }
}
