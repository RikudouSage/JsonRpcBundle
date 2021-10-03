<?php

namespace Rikudou\JsonRpcBundle\Exception;

use JetBrains\PhpStorm\ExpectedValues;
use Rikudou\JsonRpcBundle\Enum\JsonRpcErrorCode;
use Symfony\Component\HttpFoundation\Response;

final class JsonRpcParseException extends JsonRpcException
{
    #[ExpectedValues(valuesFromClass: Response::class)]
    public function getStatusCode(): int
    {
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    #[ExpectedValues(valuesFromClass: JsonRpcErrorCode::class)]
    public function getErrorCode(): int
    {
        return JsonRpcErrorCode::PARSE_ERROR;
    }
}
