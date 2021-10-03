<?php

namespace Rikudou\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class RikudouJsonRpcExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resource/config'));
        $loader->load('services.yaml');
        $loader->load('aliases.yaml');

        $configs = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('rikudou.json_rpc.internal.callables', $configs['callables']);
    }
}
