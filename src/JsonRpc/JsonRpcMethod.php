<?php

namespace Rikudou\JsonRpcBundle\JsonRpc;

use JsonSerializable;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequestParams;
use stdClass;

interface JsonRpcMethod
{
    public function getMethodName(): string;

    /**
     * @param JsonRpcRequestParams $params
     *
     * @return int|string|float|JsonSerializable|array<mixed>|stdClass|null
     */
    public function execute(JsonRpcRequestParams $params): int|string|null|float|JsonSerializable|array|stdClass;
}
