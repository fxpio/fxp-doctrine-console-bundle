<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Tests\Adapter\Fixtures;

/**
 * Mock Manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class MockManager
{
    /**
     * @return \stdClass
     */
    public function createMock()
    {
        return new \stdClass();
    }

    /**
     * @param array $fields
     *
     * @return \stdClass|null
     */
    public function findMockBy(array $fields)
    {
        $key = array_keys($fields);
        $key = $key[0];
        $ins = null;

        if ('invalid' !== $fields[$key]) {
            $ins = $this->createMock();
            $ins->id = $fields[$key];
        }

        return $ins;
    }

    /**
     * @param \stdClass $instance
     */
    public function updateMock($instance)
    {
    }

    /**
     * @param \stdClass $instance
     */
    public function deleteMock($instance)
    {
        $instance->deleted = true;
    }

    /**
     * @param string $identifier
     *
     * @return \stdClass
     */
    public function undeleteMock($identifier)
    {
        $ins = new \stdClass();
        $ins->id = $identifier;

        return $ins;
    }
}
