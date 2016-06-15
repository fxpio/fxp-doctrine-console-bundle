<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Tests\Helper;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Sonatra\Bundle\DoctrineConsoleBundle\Helper\ObjectFieldHelper;
use Sonatra\Bundle\DoctrineConsoleBundle\Tests\Helper\Fixtures\InstanceMock;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Object Field Helper Tests.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ObjectFieldHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $om;

    /**
     * @var ClassMetadata|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $meta;

    /**
     * @var ObjectFieldHelper
     */
    protected $ofh;

    protected function setUp()
    {
        $this->om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')->getMock();
        $this->ofh = new ObjectFieldHelper($this->om);

        $this->meta = $this->getMockBuilder('Doctrine\Common\Persistence\Mapping\ClassMetadata')->getMock();
        $this->meta->expects($this->any())
            ->method('getFieldNames')
            ->will($this->returnValue(array('name', 'roles', 'validationDate')));
        $this->meta->expects($this->any())
            ->method('getAssociationNames')
            ->will($this->returnValue(array('owner')));
        $this->meta->expects($this->any())
            ->method('isIdentifier')
            ->will($this->returnValue(false));
        $this->meta->expects($this->any())
            ->method('isAssociationInverseSide')
            ->will($this->returnValue(false));
        $this->meta->expects($this->any())
            ->method('isSingleValuedAssociation')
            ->will($this->returnValue(true));
        $this->meta->expects($this->any())
            ->method('getAssociationTargetClass')
            ->with($this->equalTo('owner'))
            ->will($this->returnValue('stdClass'));
        $this->meta->expects($this->any())
            ->method('getTypeOfField')
            ->will($this->returnCallback(function ($value) {
                if ('roles' === $value) {
                    return 'array';
                } elseif ('validationDate' === $value) {
                    return 'datetime';
                }

                return 'string';
            }));

        $this->om->expects($this->any())
            ->method('getClassMetadata')
            ->will($this->returnValue($this->meta));
    }

    public function testGetConfigs()
    {
        $configs = $this->ofh->getConfigs(new \stdClass());
        $valid = array(
            array(
                'name'           => 'string',
                'roles'          => 'array',
                'validationDate' => 'datetime',
            ),
            array(
                'owner' => 'stdClass',
            ),
        );
        $this->assertEquals($valid, $configs);
    }

    public function testInjectFieldOptions()
    {
        $def = new InputDefinition();

        $this->assertCount(0, $def->getArguments());
        $this->assertCount(0, $def->getOptions());

        $this->ofh->injectFieldOptions($def, 'stdClass');

        $this->assertCount(0, $def->getArguments());
        $this->assertCount(4, $def->getOptions());

        $valid = array(
            'name',
            'roles',
            'validationDate',
            'owner',
        );
        $this->assertEquals($valid, array_keys($def->getOptions()));
    }

    public function testInjectFieldOptionsWithExistingFields()
    {
        $def = new InputDefinition();
        $def->addArgument(new InputArgument('name', InputArgument::REQUIRED, 'Description'));
        $def->addOption(new InputOption('owner', null, InputOption::VALUE_REQUIRED, 'Description'));

        $this->assertCount(1, $def->getArguments());
        $this->assertCount(1, $def->getOptions());

        $this->ofh->injectFieldOptions($def, 'stdClass');

        $this->assertCount(1, $def->getArguments());
        $this->assertCount(3, $def->getOptions());

        $validArguments = array(
            'name',
        );
        $validOptions = array(
            'owner',
            'roles',
            'validationDate',
        );
        $this->assertEquals($validArguments, array_keys($def->getArguments()));
        $this->assertEquals($validOptions, array_keys($def->getOptions()));
    }

    public function testInjectFieldNewValues()
    {
        $instance = new InstanceMock();
        $def = new InputDefinition();
        $def->addArgument(new InputArgument('name', InputArgument::REQUIRED, 'Description'));
        $this->ofh->injectFieldOptions($def, $instance);

        $this->assertSame('Foo bar', $instance->getName());
        $this->assertSame(array('foo', 'bar'), $instance->getRoles());

        $input = new ArrayInput(array(
            'name'    => 'New Name',
            '--roles' => array('Role1', 'Role 2'),
        ), $def);
        $this->ofh->injectNewValues($input, $instance);

        $this->assertSame('New Name', $instance->getName());
        $this->assertSame(array('Role1', 'Role 2'), $instance->getRoles());
    }

    public function testInjectFieldNewValuesWithForceEmpty()
    {
        $instance = new InstanceMock();
        $def = new InputDefinition();
        $def->addArgument(new InputArgument('name', InputArgument::REQUIRED, 'Description'));
        $this->ofh->injectFieldOptions($def, $instance);

        $this->assertSame('Foo bar', $instance->getName());
        $this->assertSame(array('foo', 'bar'), $instance->getRoles());

        $input = new ArrayInput(array(
            'name' => '{{null}}',
            '--roles' => '{{empty}}',
        ), $def);
        $this->ofh->injectNewValues($input, $instance);

        $this->assertNull($instance->getName());
        $this->assertSame(array(), $instance->getRoles());
    }

     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The setter method "(\w+)" that should be used for property "(\w+)" seems not to exist./
     */
    public function testInjectFieldNewValuesWithInvalidSetter()
    {
        $instance = new InstanceMock();
        $def = new InputDefinition();
        $this->ofh->injectFieldOptions($def, $instance);

        $this->assertInstanceOf('\DateTime', $instance->getValidationDate());

        $date = new \DateTime();
        $input = new ArrayInput(array(
            '--validationDate' => $date->format(\DateTime::ISO8601),
        ), $def);
        $this->ofh->injectNewValues($input, $instance);
    }

    public function testInjectAssociationNewValues()
    {
        $validOwner = new \stdClass();

        /* @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject $targetRepo */
        $targetRepo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($targetRepo));

        $targetRepo->expects($this->any())
            ->method('findBy')
            ->will($this->returnValue(array($validOwner)));

        $instance = new InstanceMock();
        $def = new InputDefinition();
        $this->ofh->injectFieldOptions($def, $instance);

        $this->assertInstanceOf('\stdClass', $instance->getOwner());
        $this->assertNotSame($validOwner, $instance->getOwner());

        $input = new ArrayInput(array(
            '--owner' => 1,
        ), $def);
        $this->ofh->injectNewValues($input, $instance);

        $this->assertSame($validOwner, $instance->getOwner());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The specified mapped field "(\w+)" couldn\'t be found with the Id "(\w+)"./
     */
    public function testInjectAssociationNewValuesWithNonexistentTarget()
    {
        /* @var ObjectRepository|\PHPUnit_Framework_MockObject_MockObject $targetRepo */
        $targetRepo = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectRepository')->getMock();

        $this->om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($targetRepo));

        $instance = new InstanceMock();
        $def = new InputDefinition();
        $this->ofh->injectFieldOptions($def, $instance);

        $input = new ArrayInput(array(
            '--owner' => 1,
        ), $def);
        $this->ofh->injectNewValues($input, $instance);
    }

    public function testInjectNonexistentField()
    {
        $instance = new \stdClass();
        $valid = clone $instance;
        $def = new InputDefinition();
        $def->addOption(new InputOption('test'));

        $input = new ArrayInput(array(
            '--test' => 'New Name',
        ), $def);
        $this->ofh->injectNewValues($input, $instance);

        $this->assertEquals($valid, $instance);
    }

    public function testValidateObjectWithoutValidator()
    {
        $this->ofh->validateObject(new \stdClass());
    }

    public function testValidateObject()
    {
        /* @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();
        $validator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(array()));

        $this->ofh = new ObjectFieldHelper($this->om, $validator);
        $this->ofh->validateObject(new \stdClass());
    }

    /**
     * @expectedException \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function testValidateObjectWithException()
    {
        /* @var ValidatorInterface|\PHPUnit_Framework_MockObject_MockObject $validator */
        $validator = $this->getMockBuilder('Symfony\Component\Validator\Validator\ValidatorInterface')->getMock();
        $constraint = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolationInterface')->getMock();
        $constraint->expects($this->any())
            ->method('getPropertyPath')
            ->will($this->returnValue('property_path'));
        $constraint->expects($this->any())
            ->method('getMessage')
            ->will($this->returnValue('field error message'));

        $validator->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(array($constraint)));

        $this->ofh = new ObjectFieldHelper($this->om, $validator);
        $this->ofh->validateObject(new \stdClass());
    }
}
