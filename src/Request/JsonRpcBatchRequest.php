<?php

namespace Rikudou\JsonRpcBundle\Request;

final class JsonRpcBatchRequest implements JsonRpcRequest
{
    /**
     * @param iterable<JsonRpcSingleRequest> $requests
     */
    public function __construct(private iterable $requests)
    {
    }

    /**
     * @return iterable<JsonRpcSingleRequest>
     */
    public function getRequests(): iterable
    {
        return $this->requests;
    }
}
