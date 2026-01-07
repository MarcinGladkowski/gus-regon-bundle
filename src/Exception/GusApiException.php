<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle\Exception;

class GusApiException extends \RuntimeException
{
    public function __construct(
        string $message,
        private readonly string $apiErrorCode = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getApiErrorCode(): string
    {
        return $this->apiErrorCode;
    }
}
