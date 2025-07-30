<?php

namespace App\Http\Middleware;

use App\AdminLog;
use Closure;
use Illuminate\Support\Facades\Auth;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        //Perform action before controller run

        $response = $next($request);

        // Perform action after controller run
        (new AdminLog([
            'user_id' => Auth::user()->id,
            'path' => $request->path(),
            'action' => $request->route()->getActionMethod(),
            'request' => \json_encode($request->all()),
            'response' => \json_encode($response)
        ]))->save();

        return $response;
    }
}
