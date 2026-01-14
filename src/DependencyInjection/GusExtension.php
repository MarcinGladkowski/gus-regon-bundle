<?php

declare(strict_types=1);

namespace GusBundle\DependencyInjection;

use GusBundle\Cache\GusCacheStrategy;
use GusBundle\Client\RegonClientInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Cache\Adapter\NullAdapter;

final class GusExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('services.yaml');

        // Configure RegonClient
        $definition = $container->getDefinition(RegonClientInterface::class);
        $definition->replaceArgument('$apiKey', $config['api_key']);
        $definition->replaceArgument('$environment', $config['environment']);

        if (!$config['cache']['enabled']) {
            $nullAdapterDefinition = new Definition(NullAdapter::class);
            $definition->replaceArgument('$cache', $nullAdapterDefinition);
        }

        // Configure GusCacheStrategy
        if ($container->hasDefinition(GusCacheStrategy::class)) {
            $strategyDefinition = $container->getDefinition(GusCacheStrategy::class);
            $strategyDefinition->replaceArgument('$ttl', $config['cache']['ttl']);
        }
    }

    public function getAlias(): string
    {
        return 'gus';
    }
}
