<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Tests\Adapter;

use Sonatra\Bundle\DoctrineConsoleBundle\Adapter\ServiceManagerAdapter;
use Sonatra\Bundle\DoctrineConsoleBundle\Tests\Adapter\Fixtures\MockManager;

/**
 * Service Manager Adapter Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ServiceManagerAdapterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MockManager
     */
    protected $manager;

    /**
     * @var ServiceManagerAdapter
     */
    protected $adapter;

    protected function setUp()
    {
        $this->manager = new MockManager();
        $this->adapter = new ServiceManagerAdapter($this->manager);
        $this->adapter->setClass('FooBar');
    }

    protected function tearDown()
    {
        $this->manager = null;
        $this->adapter = null;
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The "(\w+)" method for "(\w+)" adapter is does not supported/
     */
    public function testInvalidCreateMethod()
    {
        $this->adapter->create();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The "(\w+)" method for "(\w+)" adapter is does not supported/
     */
    public function testInvalidGetMethod()
    {
        $this->adapter->get('identifier');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The "(\w+)" method for "(\w+)" adapter is does not supported/
     */
    public function testInvalidUpdateMethod()
    {
        $this->adapter->update(new \stdClass());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The "(\w+)" method for "(\w+)" adapter is does not supported/
     */
    public function testInvalidDeleteMethod()
    {
        $this->adapter->delete(new \stdClass());
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessageRegExp /The "(\w+)" method for "(\w+)" adapter is does not supported/
     */
    public function testInvalidUndeleteMethod()
    {
        $this->adapter->undelete('identifier');
    }

    public function testSetterGetters()
    {
        $this->assertSame('FooBar', $this->adapter->getClass());

        $this->adapter->setShortName('Short Name');
        $this->assertSame('Short Name', $this->adapter->getShortName());

        $this->adapter->setCommandPrefix('command:prefix');
        $this->assertSame('command:prefix', $this->adapter->getCommandPrefix());

        $this->adapter->setCommandDescription('Command Description');
        $this->assertSame('Command Description', $this->adapter->getCommandDescription());

        $this->adapter->setIdentifierField('id');
        $this->assertSame('id', $this->adapter->getIdentifierField());

        $this->adapter->setIdentifierArgument('identifier');
        $this->assertSame('identifier', $this->adapter->getIdentifierArgument());

        $this->adapter->setIdentifierArgumentDescription('Identifier Argument Description');
        $this->assertSame('Identifier Argument Description', $this->adapter->getIdentifierArgumentDescription());

        $this->adapter->setDisplayNameMethod('getId');
        $this->assertSame('getId', $this->adapter->getDisplayNameMethod());

        $this->adapter->setCreateMethod('createInstance');
        $this->adapter->setGetMethod('getInstance');
        $this->adapter->setUpdateMethod('updateInstance');
        $this->adapter->setDeleteMethod('deleteInstance');
        $this->adapter->setUndeleteMethod('undeleteInstance');
    }

    public function testCreate()
    {
        $this->adapter->setCreateMethod('createMock');
        $this->assertEquals(new \stdClass(), $this->adapter->create());
    }

    public function testGet()
    {
        $this->adapter->setGetMethod('findMockBy');
        $valid = new \stdClass();
        $valid->id = '42';
        $this->assertEquals($valid, $this->adapter->get('42'));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The ([\w ]+) with the identifier "(\w+)" does not exist/
     */
    public function testGetWithNonexistentModel()
    {
        $this->adapter->setShortName('Foo Bar');
        $this->adapter->setGetMethod('findMockBy');
        $valid = new \stdClass();
        $valid->id = 'invalid';
        $this->assertEquals($valid, $this->adapter->get('invalid'));
    }

    public function testUpdate()
    {
        $this->adapter->setUpdateMethod('updateMock');
        $this->adapter->update(new \stdClass());
    }

    public function testDelete()
    {
        $this->adapter->setDeleteMethod('deleteMock');
        $ins = new \stdClass();

        $this->assertObjectNotHasAttribute('deleted', $ins);
        $this->adapter->delete($ins);
        $this->assertObjectHasAttribute('deleted', $ins);
    }

    public function testUndelete()
    {
        $this->adapter->setUndeleteMethod('undeleteMock');
        $valid = new \stdClass();
        $valid->id = '42';
        $this->assertEquals($valid, $this->adapter->undelete('42'));
    }
}
