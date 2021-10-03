<?php

namespace Rikudou\JsonRpcBundle\Exception;

use Exception;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use Rikudou\JsonRpcBundle\Enum\JsonRpcErrorCode;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

abstract class JsonRpcException extends Exception
{
    public function __construct(string $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $this->getErrorCode(), $previous);
    }

    #[ExpectedValues(valuesFromClass: Response::class)]
    abstract public function getStatusCode(): int;

    #[ExpectedValues(valuesFromClass: JsonRpcErrorCode::class)]
    abstract public function getErrorCode(): int;

    #[Pure]
    public function getDisplayMessage(): string
    {
        return $this->getMessage();
    }
}
