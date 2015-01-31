<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Tests\Helper\Fixtures;

/**
 * Mock instance.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class InstanceMock
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Foo bar';
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @return \DateTime
     */
    public function getValidationDate()
    {
        return new \DateTime();
    }

    /**
     * @return int
     */
    public function getNumberOfTests()
    {
        return 42;
    }

    /**
     * @return string[]
     */
    public function getListOfString()
    {
        return array('foo', 'bar');
    }

    /**
     * @return int[]
     */
    public function getListOfInteger()
    {
        return array(1, 2);
    }

    /**
     * @return \DateTime[]
     */
    public function getListOfDatetime()
    {
        return array($this->getValidationDate(), $this->getValidationDate());
    }

    /**
     * @return self
     */
    public function getInvalidInstance()
    {
        return $this;
    }
}
