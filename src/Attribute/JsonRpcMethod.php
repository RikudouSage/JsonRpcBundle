<?php

namespace Rikudou\JsonRpcBundle\Attribute;

use Attribute;

#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS)]
final class JsonRpcMethod
{
    public function __construct(public string $methodName)
    {
    }
}
