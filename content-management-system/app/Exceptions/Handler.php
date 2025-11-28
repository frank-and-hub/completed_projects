<?php

namespace App\Exceptions;

use App\Helpers\YResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use PDOException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (ValidationException  $e, Request $request) {
            if ($request->expectsJson()) {
                $errors = $e->validator->errors();
                return YResponse::json(message: $errors->first(), data: $errors, status: 422);
            }
        });


        $this->renderable(function (NotFoundHttpException  $e, Request $request) {
            if ($request->expectsJson()) {
                return YResponse::json(message: __("errors.404"), status: $e->getStatusCode());
            } else {
                return response()->view('errors.404page');
                // return App\Exceptions\Inertia::render('errors.', ['status' => 404])
                //     ->toResponse($request)
                //     ->setStatusCode(404);
            }
        });

        $this->renderable(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson()) {
                return YResponse::json(message: __("errors.403"), status: $e->getStatusCode());
                // return response()->json(["message" => __("errors.403")], $e->getStatusCode());
            }
        });

        $this->renderable(function (\Illuminate\Database\QueryException $e, Request $request) {
            if ($request->expectsJson()) {
                return YResponse::json(message: __("errors.query_exception") . " - " . $e->getMessage(), status: 500);
                // return response()->json(["message" => __("errors.query_exception") . " - " . $e->getMessage()], 500);
            }
        });
    }
}
