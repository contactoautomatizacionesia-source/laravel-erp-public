<?php

namespace Modules\GeneralSetting\Exceptions;

class DuplicateNotificationSettingException extends \RuntimeException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message ?: __('validation.duplicate_record'), $code, $previous);
    }
}
