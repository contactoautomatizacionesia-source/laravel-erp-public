<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;
use App\Exceptions\CustomHandledException;
use Illuminate\Validation\ValidationException;
use Brian2694\Toastr\Facades\Toastr;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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
    }/**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Manejo de Excepción Personalizada
        if ($exception instanceof CustomHandledException) {
            $exception->log(); // Registro automático en UserActivityLog

            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getFormattedMessage(),
                    'errors' => $exception->getErrors()
                ], $exception->getErrorStatus());
            }

            Toastr::error($exception->getFormattedMessage(), __('common.error'));
            return back()->withInput();
        }

        // Manejo global de validaciones (para limpiar los catch de los controladores)
        if ($exception instanceof ValidationException) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('common.validation_failed'),
                    'errors' => $exception->errors()
                ], 422);
            }
            // El comportamiento por defecto de Laravel para Web es redirigir back() con errores
        }

        // Manejo de errores HTTP existentes
        if ($this->isHttpException($exception)) {
            $statusCode = $exception->getStatusCode();
            $errorViews = [401, 403, 404, 419, 429, 500, 503];
            
            if (in_array($statusCode, $errorViews)) {
                return response()->view('errors.' . $statusCode, [], $statusCode);
            }
        }
        return parent::render($request, $exception);
    }
}
