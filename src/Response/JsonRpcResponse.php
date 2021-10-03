<?php

namespace Rikudou\JsonRpcBundle\Response;

interface JsonRpcResponse
{
    /**
     * @return array<mixed>
     */
    public function getJson(): array;
}
