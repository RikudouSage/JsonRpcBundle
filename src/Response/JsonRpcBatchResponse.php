<?php

namespace Rikudou\JsonRpcBundle\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

final class JsonRpcBatchResponse extends JsonResponse implements JsonRpcResponse
{
    /**
     * @param iterable<JsonRpcSingleResponse> $responses
     */
    public function __construct(iterable $responses)
    {
        $data = [];
        foreach ($responses as $response) {
            $data[] = $response->getJson();
        }

        parent::__construct($data);
    }
}
