<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof AuthorizationException) {
                $response = [
                    'message' => $e->getMessage(),
                    'status' => Response::HTTP_FORBIDDEN,
                ];
            } elseif ($e instanceof ModelNotFoundException) {
                $response = [
                    'message' => $e->getMessage(),
                    'status' => Response::HTTP_NOT_FOUND,
                ];
            } elseif ($e instanceof AuthenticationException) {
                $response = [
                    'message' => $e->getMessage(),
                    'status' => Response::HTTP_UNAUTHORIZED,
                ];
            } elseif ($e instanceof ValidationException) {
                $response = [
                    'message' => $e->validator->errors()->getMessages(),
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                ];
            } else {
                $response = [
                    'message' => Response::$statusTexts[$e->getStatusCode()],
                    'status' => $e->getStatusCode(),
                ];
            }

            if ((bool) env('APP_DEBUG')) {
                $response['debug'] = [
                    'exception' => get_class($e),
                    'trace' => $e->getTrace(),
                ];
            }

            return response()->json(['error' => $response], $response['status']);
        }

        return parent::render($request, $e);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param \Illuminate\Http\Request                 $request
     * @param \Illuminate\Auth\AuthenticationException $exception
     *
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest('login');
    }
}
