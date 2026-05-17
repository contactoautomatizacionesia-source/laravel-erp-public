<?php

namespace App\Exceptions;

use Exception;
use App\Enums\HttpStatusCode;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Modules\UserActivityLog\Traits\LogActivity;

class CustomHandledException extends Exception
{
    protected mixed $errors;
    protected int $statusCode;
    protected string $context;
    protected array $replacements;
    protected string $exceptionMessage;

    public function __construct(
        Exception $originalException,
        string $context = '',
        array $replacements = [],
    ) {
        $this->exceptionMessage = $originalException->getMessage();
        $this->context = $context;
        $this->replacements = $this->validateReplacements($replacements);

        $this->statusCode = $this->determineStatusCode($originalException);
        $this->errors = $this->extractErrors($originalException);

        parent::__construct(
            $this->exceptionMessage,
            $originalException->getCode(),
            $originalException
        );
    }

    protected function validateReplacements(array $replacements): array
    {
        return [
            'operation' => $replacements['operation'] ?? __('common.unknown'),
            'attribute' => $replacements['attribute'] ?? __('common.unknown'),
            'id'        => $replacements['id'] ?? __('common.unknown'),
        ];
    }

    protected function extractErrors(Exception $exception): mixed
    {
        return match (true) {
            $exception instanceof ValidationException =>
                $exception->errors(),
            $exception instanceof ModelNotFoundException =>
                __('generalsetting::exception_error_messages.not_found', $this->replacements),
            $exception instanceof QueryException =>
                __('generalsetting::exception_error_messages.database_error', $this->replacements),
            default =>
                __('generalsetting::exception_error_messages.unknown', [
                    'error' => $exception->getMessage() ?? __('common.unknown')
                ])
        };
    }

    protected function determineStatusCode(Exception $exception): int
    {
        return match (true) {
            $exception instanceof ValidationException =>
                HttpStatusCode::UNPROCESSABLE_ENTITY->value,
            $exception instanceof ModelNotFoundException =>
                HttpStatusCode::NOT_FOUND->value,
            $exception instanceof QueryException =>
                HttpStatusCode::INTERNAL_SERVER_ERROR->value,
            default =>
                HttpStatusCode::INTERNAL_SERVER_ERROR->value
        };
    }

    public function log(): void
    {
        // Si es un array (validación), lo convertimos a JSON para el log
        $logMessage = is_array($this->errors) ? json_encode($this->errors) : $this->errors;
        LogActivity::errorLog($logMessage);
    }

    public function getErrorStatus(): int
    {
        return $this->statusCode;
    }

    public function getFormattedMessage(?string $customMessageKey = null): string
    {
        if ($customMessageKey) {
            return __($customMessageKey, $this->replacements);
        }

        if (is_string($this->errors)) {
            return $this->errors;
        }

        return __('generalsetting::exception_error_messages.validation_failed');
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }

    public function getOriginalExceptionMessage(): string
    {
        return $this->exceptionMessage;
    }
}
