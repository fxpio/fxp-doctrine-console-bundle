<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Bundle\DoctrineConsoleBundle\Tests\DependencyInjection;

use Fxp\Bundle\DoctrineConsoleBundle\DependencyInjection\FxpDoctrineConsoleExtension;
use Fxp\Bundle\DoctrineConsoleBundle\FxpDoctrineConsoleBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class FxpDoctrineConsoleExtensionTest extends TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().'/fxp_doctrine_console_tests';
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('fxp_doctrine_console.console.object_field_helper'));
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.cache_dir' => $this->cacheDir,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'kernel.bundles' => [],
            'locale' => 'en',
        ]));

        $bundle = new FxpDoctrineConsoleBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new FxpDoctrineConsoleExtension();
        $container->registerExtension($extension);
        $config = [];
        $extension->load([$config], $container);

        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->compile();

        return $container;
    }
}
