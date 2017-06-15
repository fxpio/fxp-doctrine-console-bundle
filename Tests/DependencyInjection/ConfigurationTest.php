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

use PHPUnit\Framework\TestCase;
use Sonatra\Bundle\DoctrineConsoleBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ConfigurationTest extends TestCase
{
    public function testEmptyConfiguration()
    {
        $process = new Processor();
        $configs = array();
        $validConfig = array(
            'commands' => array(),
        );

        $config = new Configuration();
        $res = $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
        $this->assertEquals($validConfig, $res);
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessageRegExp /The child node "manager_id" at path "([\w\.\-\_]+)" must be configured./
     */
    public function testConfigurationWithMissingRequireFieldForAdapterConfig()
    {
        $process = new Processor();
        $configs = array(
            'root' => array(
                'commands' => array(
                    'FooClass' => array(
                        'service_manager_adapter' => array(),
                    ),
                ),
            ),
        );

        $config = new Configuration();
        $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
    }

    public function testConfiguration()
    {
        $process = new Processor();
        $configs = array(
            'root' => array(
                'commands' => array(
                    'FooClass' => array(
                        'adapter_id' => 'adapter_service.foo_class',
                        'create' => array(
                            'field_options' => array(
                                'test' => array(
                                    'shortcut' => '--A',
                                ),
                            ),
                        ),
                    ),
                    'BarClass' => array(
                        'service_manager_adapter' => array(
                            'manager_id' => 'manager_service.bar_class',
                            'short_name' => 'Short Name',
                            'command_prefix' => 'model:bar',
                            'display_name_method' => 'getName',
                        ),
                    ),
                    'BazClass' => array(
                        'resource_adapter' => array(
                            'command_prefix' => 'model:baz',
                            'display_name_method' => 'getName',
                        ),
                    ),
                ),
            ),
        );
        $validConfig = array(
            'commands' => array(
                'FooClass' => array(
                    'adapter_id' => 'adapter_service.foo_class',
                    'view' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'create' => array(
                        'enabled' => true,
                        'field_arguments' => array(),
                        'field_options' => array(
                            'test' => array(
                                'shortcut' => array('--A'),
                                'mode' => null,
                                'description' => '',
                                'default' => null,
                            ),
                        ),
                    ),
                    'update' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'delete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'undelete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                ),
                'BarClass' => array(
                    'service_manager_adapter' => array(
                        'manager_id' => 'manager_service.bar_class',
                        'short_name' => 'Short Name',
                        'command_prefix' => 'model:bar',
                        'command_description' => 'The "%s" command of <comment>"%s"</comment> class',
                        'identifier_field' => 'id',
                        'identifier_argument' => 'identifier',
                        'identifier_argument_description' => 'The unique identifier of %s',
                        'display_name_method' => 'getName',
                        'new_instance_method' => null,
                        'create_method' => null,
                        'get_method' => null,
                        'update_method' => null,
                        'delete_method' => null,
                        'undelete_method' => null,
                    ),
                    'view' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'create' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'update' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'delete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'undelete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                ),
                'BazClass' => array(
                    'resource_adapter' => array(
                        'command_prefix' => 'model:baz',
                        'command_description' => 'The "%s" command of <comment>"%s"</comment> class',
                        'identifier_field' => 'id',
                        'identifier_argument' => 'identifier',
                        'identifier_argument_description' => 'The unique identifier of %s',
                        'display_name_method' => 'getName',
                    ),
                    'view' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'create' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'update' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'delete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                    'undelete' => array(
                        'enabled' => false,
                        'field_arguments' => array(),
                        'field_options' => array(),
                    ),
                ),
            ),
        );

        $config = new Configuration();
        $res = $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
        $this->assertEquals($validConfig, $res);
    }
}
