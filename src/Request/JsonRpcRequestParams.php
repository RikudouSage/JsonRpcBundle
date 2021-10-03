<?php

namespace Rikudou\JsonRpcBundle\Request;

use ArrayAccess;
use LogicException;

/**
 * @implements ArrayAccess<int|string, int|string|float|bool|array<mixed>>
 */
final class JsonRpcRequestParams implements ArrayAccess
{
    /**
     * @param array<int|string, int|string|float|bool|array<mixed>>|null $params
     */
    public function __construct(private ?array $params)
    {
    }

    /**
     * @phpstan-return array<int|string, int|string|float|bool|array<mixed>>
     */
    public function getParams(): array
    {
        $this->checkParamsAccess();

        assert($this->params !== null);

        return $this->params;
    }

    public function hasParams(): bool
    {
        return $this->params !== null;
    }

    public function offsetExists($offset): bool
    {
        $this->checkParamsAccess();

        assert($this->params !== null);

        return isset($this->params[$offset]);
    }

    public function offsetGet($offset)
    {
        $this->checkParamsAccess();

        assert($this->params !== null);

        return $this->params[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->checkParamsAccess();
        $this->params[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->checkParamsAccess();
        unset($this->params[$offset]);
    }

    private function checkParamsAccess(): void
    {
        if ($this->params === null) {
            throw new LogicException("This call doesn't have any parameters, please use hasParams() before accessing them");
        }
    }
}
