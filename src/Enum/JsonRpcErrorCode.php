<?php

namespace Rikudou\JsonRpcBundle\Enum;

final class JsonRpcErrorCode
{
    public const PARSE_ERROR = -32_700;

    public const INVALID_REQUEST = -32_600;

    public const METHOD_NOT_FOUND = -32_601;

    public const INVALID_PARAMS = -32_602;

    public const INTERNAL_ERROR = -32_603;
}
