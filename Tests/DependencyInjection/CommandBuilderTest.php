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

use Sonatra\Bundle\DoctrineConsoleBundle\DependencyInjection\CommandBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Command Builder Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class CommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildCommands()
    {
        $container = new ContainerBuilder();
        $this->assertCount(0, $container->getDefinitions());
        $configs = array(
            'FooClass' => array(
                'adapter' => array(
                    'command_prefix' => 'command:prefix',
                    'service_manager' => 'service_manager_id',
                    'short_name' => 'Short Name',
                    'command_description' => 'The command description',
                    'identifier_field' => 'id',
                    'identifier_argument' => 'identifier',
                    'identifier_argument_description' => 'The description of identifier argument of %s',
                    'display_name_method' => 'getId',
                    'create_method' => null,
                    'get_method' => null,
                    'update_method' => null,
                    'delete_method' => null,
                    'undelete_method' => null,
                ),
                'view' => array(
                    'enabled' => true,
                    'field_arguments' => array(),
                    'field_options' => array(),
                ),
                'create' => array(
                    'enabled' => false,
                ),
                'edit' => array(
                    'enabled' => false,
                ),
                'delete' => array(
                    'enabled' => false,
                ),
                'undelete' => array(
                    'enabled' => false,
                ),
            ),
        );

        CommandBuilder::buildCommands($container, $configs);

        $this->assertCount(2, $container->getDefinitions());
        $validAdapterDef = new Definition('Sonatra\Bundle\DoctrineConsoleBundle\Adapter\ServiceManagerAdapter');
        $validAdapterDef
            ->addArgument(new Reference('service_manager_id'))
            ->addMethodCall('setClass', array('FooClass'))
            ->addMethodCall('setShortName', array('Short Name'))
            ->addMethodCall('setCommandPrefix', array('command:prefix'))
            ->addMethodCall('setCommandDescription', array('The command description'))
            ->addMethodCall('setIdentifierField', array('id'))
            ->addMethodCall('setIdentifierArgument', array('identifier'))
            ->addMethodCall('setIdentifierArgumentDescription', array('The description of identifier argument of {s}'))
            ->addMethodCall('setDisplayNameMethod', array('getId'))
            ->addMethodCall('setCreateMethod', array(null))
            ->addMethodCall('setGetMethod', array(null))
            ->addMethodCall('setUpdateMethod', array(null))
            ->addMethodCall('setDeleteMethod', array(null))
            ->addMethodCall('setUndeleteMethod', array(null))
        ;
        $validCommandDef = new Definition('Sonatra\Bundle\DoctrineConsoleBundle\Command\View');
        $validCommandDef
            ->addArgument(new Reference('sonatra_doctrine_console.console.object_field_helper'))
            ->addArgument(new Reference('sonatra_doctrine_console.command_adapter.command_prefix'))
            ->addArgument(array())
            ->addArgument(array())
            ->addTag('console.command')
        ;

        $valid = array(
            'sonatra_doctrine_console.command_adapter.command_prefix' => $validAdapterDef,
            'sonatra_doctrine_console.commands.command_prefix.view' => $validCommandDef,
        );
        $this->assertEquals($valid, $container->getDefinitions());
    }
}
