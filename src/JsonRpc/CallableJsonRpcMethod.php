<?php

namespace Rikudou\JsonRpcBundle\JsonRpc;

use JsonSerializable;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequestParams;
use stdClass;

final class CallableJsonRpcMethod implements JsonRpcMethod
{
    /**
     * @var callable
     */
    private $method;

    public function __construct(private string $name, callable $method)
    {
        $this->method = $method;
    }

    public function getMethodName(): string
    {
        return $this->name;
    }

    public function execute(JsonRpcRequestParams $params): int|string|null|float|JsonSerializable|array|stdClass
    {
        return ($this->method)(...($params->hasParams() ? $params->getParams() : []));
    }
}
