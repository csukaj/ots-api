<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler {

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param  Throwable  $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        if ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $error = $e->getMessage();
            
        } else if ($e instanceof ModelNotFoundException) {
            $statusCode = 404;
            $error = $e->getMessage();
            
        } elseif ($e instanceof AuthorizationException) {
            $statusCode = 403;
            $error = $e->getMessage();
        
        } elseif ($e instanceof ValidationException) {
            $statusCode = 400;
            $error = $e->getMessage();
            
        } elseif ($e instanceof UserException) {
            $statusCode = 400;
            $error = $e->getMessage();
            
        } else {
            $statusCode = 500;
            $error = 'Unexpected Exception';
        }

        return new JsonResponse([
            'success' => false,
            'error' => $error,
            'exception' => [
                'class' => get_class($e),
                'code' => $e->getCode(),
                'location' => $e->getFile() . '@' . $e->getLine(),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'previous' => $e->getPrevious()
            ]
        ], $statusCode);
    }

}
