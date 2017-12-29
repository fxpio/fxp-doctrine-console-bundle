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

use Fxp\Bundle\DoctrineConsoleBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

/**
 * Configuration Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConfigurationTest extends TestCase
{
    public function testEmptyConfiguration()
    {
        $process = new Processor();
        $configs = [];
        $validConfig = [
            'commands' => [],
        ];

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
        $configs = [
            'root' => [
                'commands' => [
                    'FooClass' => [
                        'service_manager_adapter' => [],
                    ],
                ],
            ],
        ];

        $config = new Configuration();
        $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
    }

    public function testConfiguration()
    {
        $process = new Processor();
        $configs = [
            'root' => [
                'commands' => [
                    'FooClass' => [
                        'adapter_id' => 'adapter_service.foo_class',
                        'create' => [
                            'field_options' => [
                                'test' => [
                                    'shortcut' => '--A',
                                ],
                            ],
                        ],
                    ],
                    'BarClass' => [
                        'service_manager_adapter' => [
                            'manager_id' => 'manager_service.bar_class',
                            'short_name' => 'Short Name',
                            'command_prefix' => 'model:bar',
                            'display_name_method' => 'getName',
                        ],
                    ],
                    'BazClass' => [
                        'resource_adapter' => [
                            'command_prefix' => 'model:baz',
                            'display_name_method' => 'getName',
                        ],
                    ],
                ],
            ],
        ];
        $validConfig = [
            'commands' => [
                'FooClass' => [
                    'adapter_id' => 'adapter_service.foo_class',
                    'view' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'create' => [
                        'enabled' => true,
                        'field_arguments' => [],
                        'field_options' => [
                            'test' => [
                                'shortcut' => ['--A'],
                                'mode' => null,
                                'description' => '',
                                'default' => null,
                            ],
                        ],
                    ],
                    'update' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'delete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'undelete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                ],
                'BarClass' => [
                    'service_manager_adapter' => [
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
                    ],
                    'view' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'create' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'update' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'delete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'undelete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                ],
                'BazClass' => [
                    'resource_adapter' => [
                        'command_prefix' => 'model:baz',
                        'command_description' => 'The "%s" command of <comment>"%s"</comment> class',
                        'identifier_field' => 'id',
                        'identifier_argument' => 'identifier',
                        'identifier_argument_description' => 'The unique identifier of %s',
                        'display_name_method' => 'getName',
                    ],
                    'view' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'create' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'update' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'delete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                    'undelete' => [
                        'enabled' => false,
                        'field_arguments' => [],
                        'field_options' => [],
                    ],
                ],
            ],
        ];

        $config = new Configuration();
        $res = $process->process($config->getConfigTreeBuilder()->buildTree(), $configs);
        $this->assertEquals($validConfig, $res);
    }
}
