<?php

namespace Rikudou\JsonRpcBundle\Response;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use JsonSerializable;
use Rikudou\JsonRpcBundle\Enum\JsonRpcErrorCode;

final class JsonRpcError implements JsonSerializable
{
    public function __construct(
        #[ExpectedValues(valuesFromClass: JsonRpcErrorCode::class)]
        private int $code,
        private ?string $message = null,
    ) {
    }

    /**
     * @phpstan-return array{"code": int, "message": string}
     */
    #[Pure]
    #[ArrayShape(['code' => 'int', 'message' => 'string'])]
    public function jsonSerialize(): array
    {
        return [
            'code' => $this->code,
            'message' => $this->message ?: $this->getDefaultMessage(),
        ];
    }

    private function getDefaultMessage(): string
    {
        return match ($this->code) {
            JsonRpcErrorCode::PARSE_ERROR => 'Error parsing JSON-RPC data',
            JsonRpcErrorCode::INTERNAL_ERROR => 'Internal error',
            JsonRpcErrorCode::INVALID_PARAMS => 'One or more parameters are either missing or incorrect',
            JsonRpcErrorCode::INVALID_REQUEST => 'The request is not a valid request object',
            JsonRpcErrorCode::METHOD_NOT_FOUND => 'The method does not exist',
            default => 'Unknown error',
        };
    }
}
