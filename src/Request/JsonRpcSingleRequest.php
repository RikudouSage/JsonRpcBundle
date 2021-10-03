<?php

namespace Rikudou\JsonRpcBundle\Request;

final class JsonRpcSingleRequest implements JsonRpcRequest
{
    /**
     * @param array<mixed>|null $params
     */
    public function __construct(
        private string $method,
        private int|string|null $id,
        private ?array $params = null,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<mixed>|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }
}
