<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * The inputs that are never flashed for validation exceptions.
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

        $this->shouldRenderJsonWhen(function ($request, Throwable $e) {
            return $request->is('api/*');
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse(
                    $e->getMessage(),
                    $e->status,
                    $e->errors()
                );
            }
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse('Unauthenticated.', 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse('Forbidden.', 403);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse('Resource not found.', 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse('Resource not found.', 404);
            }
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse('Method not allowed.', 405);
            }
        });

        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if ($request->is('api/*')) {
                return $this->jsonErrorResponse(
                    $e->getMessage() ?: 'HTTP error encountered.',
                    $e->getStatusCode()
                );
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($request->is('api/*')) {
                $message = config('app.debug')
                    ? $e->getMessage()
                    : 'Internal server error.';

                return $this->jsonErrorResponse($message, 500);
            }
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->is('api/*')) {
            return $this->renderApiException($request, $e);
        }

        return parent::render($request, $e);
    }

    protected function renderApiException(Request $request, Throwable $e): JsonResponse
    {
        if ($e instanceof ValidationException) {
            return $this->jsonErrorResponse($e->getMessage(), $e->status, $e->errors());
        }

        if ($e instanceof AuthenticationException) {
            return $this->jsonErrorResponse('Unauthenticated.', 401);
        }

        if ($e instanceof AuthorizationException) {
            return $this->jsonErrorResponse('Forbidden.', 403);
        }

        if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
            return $this->jsonErrorResponse('Resource not found.', 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return $this->jsonErrorResponse('Method not allowed.', 405);
        }

        if ($e instanceof HttpExceptionInterface) {
            return $this->jsonErrorResponse(
                $e->getMessage() ?: 'HTTP error encountered.',
                $e->getStatusCode()
            );
        }

        $message = config('app.debug') ? $e->getMessage() : 'Internal server error.';

        return $this->jsonErrorResponse($message, 500);
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return $this->jsonErrorResponse($exception->getMessage(), $exception->status, $exception->errors());
    }

    private function jsonErrorResponse(string $message, int $status, array $errors = []): JsonResponse
    {
        $payload = [
            'status' => 'error',
            'message' => $message,
            'code' => $status,
        ];

        if (! empty($errors)) {
            $payload['errors'] = $errors;
        }

        return response()->json($payload, $status);
    }
}
