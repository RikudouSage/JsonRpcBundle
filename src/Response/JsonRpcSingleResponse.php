<?php

namespace Rikudou\JsonRpcBundle\Response;

use JsonSerializable;
use Rikudou\JsonRpcBundle\Request\JsonRpcSingleRequest;
use stdClass;
use Symfony\Component\HttpFoundation\JsonResponse;

final class JsonRpcSingleResponse extends JsonResponse implements JsonRpcResponse
{
    /**
     * @var array<string, mixed>
     */
    private array $json;

    /**
     * @param int|string|float|JsonSerializable|array<mixed>|JsonRpcError|stdClass|null $response
     */
    public function __construct(
        int|string|null|JsonRpcSingleRequest $id,
        int|string|null|float|JsonSerializable|array|JsonRpcError|stdClass $response
    ) {
        if ($id instanceof JsonRpcSingleRequest) {
            $id = $id->getId();
        }
        $data = [
            'jsonrpc' => '2.0',
            'id' => $id,
        ];
        if ($response instanceof JsonRpcError) {
            $data['error'] = $response->jsonSerialize();
        } else {
            $data['result'] = $response instanceof JsonSerializable ? $response->jsonSerialize() : $response;
        }

        $this->json = $data;
        parent::__construct($data, self::HTTP_OK, []);
    }

    /**
     * @return array<string, mixed>
     */
    public function getJson(): array
    {
        return $this->json;
    }
}
