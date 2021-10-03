<?php

namespace Rikudou\JsonRpcBundle\Service;

use JetBrains\PhpStorm\Pure;
use JsonException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInternalErrorException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInvalidRequestException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcParseException;
use Rikudou\JsonRpcBundle\Request\JsonRpcBatchRequest;
use Rikudou\JsonRpcBundle\Request\JsonRpcRequest;
use Rikudou\JsonRpcBundle\Request\JsonRpcSingleRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class JsonRpcRequestParser
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * @throws JsonRpcInvalidRequestException
     * @throws JsonRpcParseException
     * @throws JsonRpcInternalErrorException
     */
    public function parse(?Request $request = null): JsonRpcRequest
    {
        $request ??= $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new JsonRpcInternalErrorException('There was no request to parse');
        }
        $rawData = $this->getRawData($request);

        if (!isset($rawData['jsonrpc'])) {
            $requests = [];
            foreach ($rawData as $singleRequestRawData) {
                $requests[] = $this->getSingleRequest($singleRequestRawData);
            }

            return new JsonRpcBatchRequest($requests);
        }

        /** @phpstan-var array{"method": string, "id": int|string|null, "params": array|null} $rawData */
        return $this->getSingleRequest($rawData);
    }

    /**
     * @phpstan-param array{"method": string, "id": int|string|null, "params": array|null} $rawData
     */
    #[Pure]
    private function getSingleRequest(array $rawData): JsonRpcSingleRequest
    {
        return new JsonRpcSingleRequest(
            method: $rawData['method'],
            id: $rawData['id'] ?? null,
            params: $rawData['params'] ?? null,
        );
    }

    /**
     * @throws JsonRpcInvalidRequestException
     * @throws JsonRpcParseException
     * @phpstan-return array{"method": string, "id": int|string|null, "params": array|null}|array{"method": string, "id": int|string|null, "params": array|null}[]
     */
    private function getRawData(Request $request): array
    {
        $data = $request->getContent();
        if (!$data) {
            throw new JsonRpcParseException("Request doesn't have a body");
        }

        try {
            $json = json_decode($data, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new JsonRpcInvalidRequestException('Content is not a valid JSON', previous: $e);
        }

        if (!$this->validate($json)) {
            throw new JsonRpcInvalidRequestException('Failed to validate JSON-RPC data');
        }

        return $json;
    }

    /**
     * @phpstan-param array<mixed> $json
     */
    private function validate(array $json): bool
    {
        $isset = fn (string $key): bool => array_key_exists($key, $json);

        if (!$isset('jsonrpc') && isset($json[0]['jsonrpc'])) {
            $result = true;
            foreach ($json as $singleRequest) {
                $result = $result && $this->validate($singleRequest);
            }

            return $result;
        }

        return $isset('jsonrpc') && $json['jsonrpc'] === '2.0' && $isset('method');
    }
}
