<?php

namespace Rikudou\JsonRpcBundle\Listener;

use JetBrains\PhpStorm\ArrayShape;
use Rikudou\JsonRpcBundle\Exception\JsonRpcException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcInvalidRequestException;
use Rikudou\JsonRpcBundle\Exception\JsonRpcParseException;
use Rikudou\JsonRpcBundle\Request\JsonRpcSingleRequest;
use Rikudou\JsonRpcBundle\Response\JsonRpcError;
use Rikudou\JsonRpcBundle\Response\JsonRpcSingleResponse;
use Rikudou\JsonRpcBundle\Service\JsonRpcRequestParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class JsonRpcExceptionListener implements EventSubscriberInterface
{
    public function __construct(private JsonRpcRequestParser $requestParser)
    {
    }

    #[ArrayShape([KernelEvents::EXCEPTION => 'string'])]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'onException',
        ];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (!$exception instanceof JsonRpcException) {
            return;
        }

        try {
            $id = $this->requestParser->parse($event->getRequest());
            if (!$id instanceof JsonRpcSingleRequest) {
                $id = null;
            }
        } catch (JsonRpcInvalidRequestException | JsonRpcParseException) {
            $id = null;
        }

        $event->setResponse(
            (new JsonRpcSingleResponse(
                $id,
                new JsonRpcError($exception->getCode(), $exception->getMessage()),
            ))->setStatusCode($exception->getStatusCode()),
        );
    }
}
