<?php

namespace Rikudou\JsonRpcBundle\Exception;

use JetBrains\PhpStorm\ExpectedValues;
use Rikudou\JsonRpcBundle\Enum\JsonRpcErrorCode;
use Symfony\Component\HttpFoundation\Response;

final class JsonRpcMethodNotFoundException extends JsonRpcException
{
    #[ExpectedValues(valuesFromClass: Response::class)]
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    #[ExpectedValues(valuesFromClass: JsonRpcErrorCode::class)]
    public function getErrorCode(): int
    {
        return JsonRpcErrorCode::METHOD_NOT_FOUND;
    }
}
