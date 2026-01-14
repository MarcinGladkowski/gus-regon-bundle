<?php

declare(strict_types=1);

namespace GusBundle;

use GusBundle\Cache\GusCacheStrategy;
use GusBundle\Client\RegonClientInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class GusBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('api_key')
                    ->defaultValue('%env(GUS_REGON_API_KEY)%')
                    ->info('GUS REGON API Key')
                ->end()
                ->enumNode('environment')
                    ->values(['test', 'production'])
                    ->defaultValue('%env(default:GUS_ENVIRONMENT:GUS_ENVIRONMENT)%')
                    ->info('Environment: test or production')
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultTrue()
                        ->end()
                        ->integerNode('ttl')
                            ->defaultValue(86400) // 24 hours
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        // Configure RegonClient
        $definition = $builder->getDefinition(RegonClientInterface::class);
        $definition->replaceArgument('$apiKey', $config['api_key']);
        $definition->replaceArgument('$environment', $config['environment']);

        if (!$config['cache']['enabled']) {
            $nullAdapterDefinition = new Definition(NullAdapter::class);
            $definition->replaceArgument('$cache', $nullAdapterDefinition);
        }

        // Configure GusCacheStrategy
        if ($builder->hasDefinition(GusCacheStrategy::class)) {
            $strategyDefinition = $builder->getDefinition(GusCacheStrategy::class);
            $strategyDefinition->replaceArgument('$ttl', $config['cache']['ttl']);
        }
    }
}
