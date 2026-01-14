<?php

declare(strict_types=1);

namespace GusBundle\Tests\Unit;

use GusBundle\GusBundle;
use GusBundle\Client\RegonClientInterface;
use GusBundle\Cache\GusCacheStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class GusBundleTest extends TestCase
{
    private GusBundle $bundle;
    private ExtensionInterface $extension;
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        $this->bundle = new GusBundle();
        $this->extension = $this->bundle->getContainerExtension();
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.environment', 'test');
        $this->container->setParameter('kernel.build_dir', sys_get_temp_dir());
        $this->container->setParameter('kernel.project_dir', sys_get_temp_dir());
        $this->container->registerExtension($this->extension);
    }

    public function testLoadWithDefaultConfiguration(): void
    {
        $this->extension->load([], $this->container);

        $this->assertTrue($this->container->hasDefinition(RegonClientInterface::class));
        $definition = $this->container->getDefinition(RegonClientInterface::class);

        // Check defaults
        $this->assertSame('%env(GUS_REGON_API_KEY)%', $definition->getArgument('$apiKey'));
        $this->assertSame('%env(default:GUS_ENVIRONMENT:GUS_ENVIRONMENT)%', $definition->getArgument('$environment'));

        // Cache should be @cache.app by default (from services.yaml)
        $this->assertSame('cache.app', (string) $definition->getArgument('$cache'));
    }

    public function testLoadWithCustomConfiguration(): void
    {
        $config = [
            'gus' => [
                'api_key' => 'custom-key',
                'environment' => 'production',
                'cache' => [
                    'enabled' => false,
                    'ttl' => 3600,
                ],
            ],
        ];

        $this->extension->load($config, $this->container);

        $clientDefinition = $this->container->getDefinition(RegonClientInterface::class);
        $this->assertSame('custom-key', $clientDefinition->getArgument('$apiKey'));
        $this->assertSame('production', $clientDefinition->getArgument('$environment'));

        // Cache enabled = false -> NullAdapter definition
        $cacheArg = $clientDefinition->getArgument('$cache');
        $this->assertInstanceOf(Definition::class, $cacheArg);
        $this->assertSame(NullAdapter::class, $cacheArg->getClass());

        // Check GusCacheStrategy ttl
        $strategyDefinition = $this->container->getDefinition(GusCacheStrategy::class);
        $this->assertSame(3600, $strategyDefinition->getArgument('$ttl'));
    }
}
