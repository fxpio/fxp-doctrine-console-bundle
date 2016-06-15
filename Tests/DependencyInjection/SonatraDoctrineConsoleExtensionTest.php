<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Tests\DependencyInjection;

use Sonatra\Bundle\DoctrineConsoleBundle\DependencyInjection\SonatraDoctrineConsoleExtension;
use Sonatra\Bundle\DoctrineConsoleBundle\SonatraDoctrineConsoleBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Bundle Extension Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class SonatraDoctrineConsoleExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $cacheDir;

    protected function setUp()
    {
        $this->cacheDir = sys_get_temp_dir().'/sonatra_doctrine_console_tests';
    }

    protected function tearDown()
    {
        $fs = new Filesystem();
        $fs->remove($this->cacheDir);
    }

    public function testCompileContainerWithExtension()
    {
        $container = $this->getContainer();
        $this->assertTrue($container->hasDefinition('sonatra_doctrine_console.console.object_field_helper'));
    }

    /**
     * Gets the container.
     *
     * @return ContainerBuilder
     */
    protected function getContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => $this->cacheDir,
            'kernel.debug' => false,
            'kernel.environment' => 'test',
            'kernel.name' => 'kernel',
            'kernel.root_dir' => __DIR__,
            'kernel.charset' => 'UTF-8',
            'assetic.debug' => false,
            'kernel.bundles' => array(),
            'locale' => 'en',
        )));

        $bundle = new SonatraDoctrineConsoleBundle();
        $bundle->build($container); // Attach all default factories

        $extension = new SonatraDoctrineConsoleExtension();
        $container->registerExtension($extension);
        $config = array();
        $extension->load(array($config), $container);

        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();

        return $container;
    }
}
