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

use Fxp\Bundle\DoctrineConsoleBundle\DependencyInjection\CommandBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\ExpressionLanguage\Expression;

/**
 * Command Builder Tests.
 *
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class CommandBuilderTest extends TestCase
{
    public function testBuildCommandsForServiceAdapter()
    {
        $container = new ContainerBuilder();
        $this->assertCount(1, $container->getDefinitions());
        $configs = [
            'FooClass' => [
                'adapter_id' => 'service_adapter_id',
                'view' => [
                    'enabled' => true,
                    'field_arguments' => [],
                    'field_options' => [],
                ],
                'create' => [
                    'enabled' => false,
                ],
                'update' => [
                    'enabled' => false,
                ],
                'delete' => [
                    'enabled' => false,
                ],
                'undelete' => [
                    'enabled' => false,
                ],
            ],
        ];

        CommandBuilder::buildCommands($container, $configs);

        $this->assertCount(2, $container->getDefinitions());
        $validCommandDef = new Definition('Fxp\Component\DoctrineConsole\Command\View');
        $validCommandDef
            ->addArgument(new Reference('fxp_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference('service_adapter_id'))
            ->addArgument([])
            ->addArgument([])
            ->addTag('console.command')
        ;

        $valid = array_merge($container->getDefinitions(), [
            'service_adapter_id.view' => $validCommandDef,
        ]);
        $this->assertEquals($valid, $container->getDefinitions());
    }

    public function testBuildCommandsForServiceManagerAdapter()
    {
        $container = new ContainerBuilder();
        $this->assertCount(1, $container->getDefinitions());
        $configs = [
            'FooClass' => [
                'service_manager_adapter' => [
                    'manager_id' => 'service_manager_id',
                    'command_prefix' => 'command:prefix',
                    'short_name' => 'Short Name',
                    'command_description' => 'The command description',
                    'identifier_field' => 'id',
                    'identifier_argument' => 'identifier',
                    'identifier_argument_description' => 'The description of identifier argument of %s',
                    'display_name_method' => 'getId',
                    'new_instance_method' => null,
                    'create_method' => null,
                    'get_method' => null,
                    'update_method' => null,
                    'delete_method' => null,
                    'undelete_method' => null,
                ],
                'view' => [
                    'enabled' => true,
                    'field_arguments' => [],
                    'field_options' => [],
                ],
                'create' => [
                    'enabled' => false,
                ],
                'update' => [
                    'enabled' => false,
                ],
                'delete' => [
                    'enabled' => false,
                ],
                'undelete' => [
                    'enabled' => false,
                ],
            ],
        ];

        CommandBuilder::buildCommands($container, $configs);

        $this->assertCount(3, $container->getDefinitions());
        $validAdapterDef = new Definition('Fxp\Component\DoctrineConsole\Adapter\ServiceManagerAdapter');
        $validAdapterDef
            ->addArgument(new Reference('service_manager_id'))
            ->addArgument(new Reference('validator', ContainerInterface::IGNORE_ON_INVALID_REFERENCE))
            ->addMethodCall('setClass', ['FooClass'])
            ->addMethodCall('setShortName', ['Short Name'])
            ->addMethodCall('setCommandPrefix', ['command:prefix'])
            ->addMethodCall('setCommandDescription', ['The command description'])
            ->addMethodCall('setIdentifierField', ['id'])
            ->addMethodCall('setIdentifierArgument', ['identifier'])
            ->addMethodCall('setIdentifierArgumentDescription', ['The description of identifier argument of {s}'])
            ->addMethodCall('setDisplayNameMethod', ['getId'])
            ->addMethodCall('setNewInstanceMethod', [null])
            ->addMethodCall('setCreateMethod', [null])
            ->addMethodCall('setGetMethod', [null])
            ->addMethodCall('setUpdateMethod', [null])
            ->addMethodCall('setDeleteMethod', [null])
            ->addMethodCall('setUndeleteMethod', [null])
        ;
        $validCommandDef = new Definition('Fxp\Component\DoctrineConsole\Command\View');
        $validCommandDef
            ->addArgument(new Reference('fxp_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference('fxp_doctrine_console.command_adapter.command_prefix'))
            ->addArgument([])
            ->addArgument([])
            ->addTag('console.command')
        ;

        $valid = array_merge($container->getDefinitions(), [
            'fxp_doctrine_console.command_adapter.command_prefix' => $validAdapterDef,
            'fxp_doctrine_console.commands.command_prefix.view' => $validCommandDef,
        ]);
        $this->assertEquals($valid, $container->getDefinitions());
    }

    public function testBuildCommandsForServiceResourceAdapter()
    {
        $container = new ContainerBuilder();
        $this->assertCount(1, $container->getDefinitions());
        $configs = [
            'FooClass' => [
                'resource_adapter' => [
                    'resource_id' => 'service_resource_id',
                    'command_prefix' => 'command:prefix',
                    'command_description' => 'The command description',
                    'identifier_field' => 'id',
                    'identifier_argument' => 'identifier',
                    'identifier_argument_description' => 'The description of identifier argument of %s',
                    'display_name_method' => 'getId',
                ],
                'view' => [
                    'enabled' => true,
                    'field_arguments' => [],
                    'field_options' => [],
                ],
                'create' => [
                    'enabled' => false,
                ],
                'update' => [
                    'enabled' => false,
                ],
                'delete' => [
                    'enabled' => false,
                ],
                'undelete' => [
                    'enabled' => false,
                ],
            ],
        ];

        CommandBuilder::buildCommands($container, $configs);

        $this->assertCount(3, $container->getDefinitions());
        $validAdapterDef = new Definition('Fxp\Component\DoctrineConsole\Adapter\ResourceAdapter');
        $validAdapterDef
            ->addArgument(new Expression('service("fxp_resource.domain_manager").get("FooClass")'))
            ->addMethodCall('setCommandPrefix', ['command:prefix'])
            ->addMethodCall('setCommandDescription', ['The command description'])
            ->addMethodCall('setIdentifierField', ['id'])
            ->addMethodCall('setIdentifierArgument', ['identifier'])
            ->addMethodCall('setIdentifierArgumentDescription', ['The description of identifier argument of {s}'])
            ->addMethodCall('setDisplayNameMethod', ['getId'])
        ;
        $validCommandDef = new Definition('Fxp\Component\DoctrineConsole\Command\View');
        $validCommandDef
            ->addArgument(new Reference('fxp_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference('fxp_doctrine_console.command_adapter.command_prefix'))
            ->addArgument([])
            ->addArgument([])
            ->addTag('console.command')
        ;

        $valid = array_merge($container->getDefinitions(), [
            'fxp_doctrine_console.command_adapter.command_prefix' => $validAdapterDef,
            'fxp_doctrine_console.commands.command_prefix.view' => $validCommandDef,
        ]);
        $this->assertEquals($valid, $container->getDefinitions());
    }

    /**
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     * @expectedExceptionMessage An adapter must be configured on "fxp_doctrine_console.commands.FooClass". Available adapters: "adapter_id", "service_manager_adapter"
     */
    public function testBuildCommandsWithoutAdapter()
    {
        $container = new ContainerBuilder();
        $this->assertCount(1, $container->getDefinitions());
        $configs = [
            'FooClass' => [
                'view' => [
                    'enabled' => true,
                    'field_arguments' => [],
                    'field_options' => [],
                ],
                'create' => [
                    'enabled' => false,
                ],
                'update' => [
                    'enabled' => false,
                ],
                'delete' => [
                    'enabled' => false,
                ],
                'undelete' => [
                    'enabled' => false,
                ],
            ],
        ];

        CommandBuilder::buildCommands($container, $configs);
    }
}
