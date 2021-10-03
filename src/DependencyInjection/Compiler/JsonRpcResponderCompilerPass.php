<?php

namespace Rikudou\JsonRpcBundle\DependencyInjection\Compiler;

use LogicException;
use ReflectionClass;
use Rikudou\JsonRpcBundle\JsonRpc\CallableJsonRpcMethod;
use Rikudou\JsonRpcBundle\JsonRpc\JsonRpcMethod;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class JsonRpcResponderCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $result = [];

        foreach ($container->findTaggedServiceIds('rikudou.json_rpc.json_rpc_method') as $id => $tags) {
            $definition = $container->getDefinition($id);
            $class = $definition->getClass();
            if ($class === null || !class_exists($class)) {
                continue;
            }

            $instance = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            if (!is_callable($instance) && !$instance instanceof JsonRpcMethod) {
                throw new LogicException(
                    sprintf(
                        'The JSON-RPC method class: "%s" is neither callable nor instance of %s',
                        $class,
                        JsonRpcMethod::class,
                    )
                );
            }

            if ($instance instanceof JsonRpcMethod) {
                $result[] = new Reference($id);
            } else {
                $name = null;
                foreach ($tags as $tag) {
                    $name ??= $tag['name'] ?? null;
                }

                if ($name === null) {
                    throw new LogicException(
                        sprintf(
                            'The class "%s" does not have a name specified in the attribute',
                            $class,
                        )
                    );
                }

                $decoratorDefinition = new Definition(CallableJsonRpcMethod::class, [
                    $name,
                    new Reference($id),
                ]);
                $container->setDefinition("{$id}.decorated", $decoratorDefinition);
                $result[] = new Reference("{$id}.decorated");
            }
        }

        $callables = $container->getParameter('rikudou.json_rpc.internal.callables');
        assert(is_array($callables));
        foreach ($callables as $callable) {
            $name = $callable['name'] ?? null;
            $callable = $callable['callable'] ?? null;
            $callableToTest = $callable;

            if (!$name || !$callable) {
                throw new LogicException('Both name and callable must be specified');
            }

            if (is_array($callable)) {
                $callableServiceId = $callable[0];
                $callableMethod = $callable[1];

                if (str_starts_with($callableServiceId, '@')) {
                    $callableServiceId = substr($callableServiceId, 1);
                    $callableServiceDefinition = $container->getDefinition($callableServiceId);
                    $callableClass = $callableServiceDefinition->getClass();
                    if ($callableClass === null || !class_exists($callableClass)) {
                        throw new LogicException("No class is configured for service '{$callableServiceId}'");
                    }
                    $callableClassInstance = (new ReflectionClass($callableClass))->newInstanceWithoutConstructor();
                    $callableToTest = [$callableClassInstance, $callableMethod];
                    $callable = [new Reference($callableServiceId), $callableMethod];
                }
            }

            if (!is_callable($callableToTest)) {
                throw new LogicException("'{$this->getCallableStringRepresentation($callable)}' is not a valid callable");
            }

            $decoratorDefinition = new Definition(CallableJsonRpcMethod::class, [
                $name,
                $callable,
            ]);
            $container->setDefinition("rikudou.json_rpc.callable.{$name}", $decoratorDefinition);
            $result[] = new Reference("rikudou.json_rpc.callable.{$name}");
        }

        $container->getDefinition('rikudou.json_rpc.responder')->setArgument(0, $result);
    }

    private function getCallableStringRepresentation(mixed $data): string
    {
        if (is_string($data)) {
            return $data;
        }

        if (is_array($data)) {
            $result = '[';
            foreach ($data as $value) {
                if ($value instanceof Reference) {
                    $value = "@{$value}";
                }
                $result .= "\"{$value}\", ";
            }
            $result = substr($result, 0, -2);
            $result .= ']';

            return $result;
        }

        throw new LogicException('Cannot print callable');
    }
}
