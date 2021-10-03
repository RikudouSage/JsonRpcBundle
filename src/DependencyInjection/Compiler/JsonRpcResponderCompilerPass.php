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

        $container->getDefinition('rikudou.json_rpc.responder')->setArgument(0, $result);
    }
}
