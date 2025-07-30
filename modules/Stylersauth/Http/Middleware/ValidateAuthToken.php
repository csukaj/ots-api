<?php

namespace Modules\Stylersauth\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Events\Dispatcher;
use Illuminate\Routing\ResponseFactory;

class ValidateAuthToken {

    public function __construct(ResponseFactory $response, Dispatcher $events, JWTAuth $auth) {
        $this->response = $response;
        $this->events = $events;
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!$token = $this->auth->setRequest($request)->getToken()) {
            return $this->respond('tymon.jwt.absent', 'token_not_provided', 400);
        }

        try {
            $user = $this->auth->authenticate($token);
        } catch (TokenExpiredException $e) {
            return $this->respond('tymon.jwt.expired', 'token_expired', $e->getStatusCode(), [$e]);
        } catch (TokenBlacklistedException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalidated', $e->getStatusCode(), [$e]);
        } catch (TokenInvalidException $e) {
            return $this->respond('tymon.jwt.invalid', 'token_invalid', $e->getStatusCode(), [$e]);
        } catch (\Exception $e) {
            dd($e);
        }

        if (!$user) {
            return $this->respond('tymon.jwt.user_not_found', 'user_not_found', 404);
        }

        $this->events->fire('tymon.jwt.valid', $user);

        return $next($request);
    }

    protected function respond($event, $error, $status, $payload = []) {
        $response = $this->events->fire($event, $payload, true);

        return $response ? : $this->response->json(['success' => false, 'error' => $error], $status);
    }

}
