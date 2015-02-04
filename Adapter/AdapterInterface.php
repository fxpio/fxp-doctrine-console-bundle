<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Adapter;

/**
 * Interface of Command Adapter.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
interface AdapterInterface
{
    /**
     * Create the object instance.
     *
     * @return object The object instance
     *
     * @throws \RuntimeException When the create method is not accessible
     */
    public function create();

    /**
     * Get the object instance.
     *
     * @param string $identifier The unique identifier
     *
     * @return object The object instance
     *
     * @throws \RuntimeException When the get method is not accessible
     */
    public function get($identifier);

    /**
     * Update the object instance.
     *
     * @param object $instance The object instance
     *
     * @throws \RuntimeException When the update method is not accessible
     */
    public function update($instance);

    /**
     * Delete the object instance.
     *
     * @param object $instance The object instance
     *
     * @throws \RuntimeException When the delete method is not accessible
     */
    public function delete($instance);

    /**
     * Undelete the object instance.
     *
     * @param string $identifier The unique identifier
     *
     * @return object The object instance
     *
     * @throws \RuntimeException When the undelete method is not accessible
     */
    public function undelete($identifier);

    /**
     * Get the class name of object.
     *
     * @return string
     */
    public function getClass();

    /**
     * Get the short name of the object (used in output console and arguments/options).
     *
     * @return string
     */
    public function getShortName();

    /**
     * Get the command prefix.
     *
     * @return string
     */
    public function getCommandPrefix();

    /**
     * Get the command description.
     *
     * @return string
     */
    public function getCommandDescription();

    /**
     * Get the name of field dedicated for the object identifier.
     *
     * @return string
     */
    public function getIdentifierField();

    /**
     * Get the name of the argument dedicated for the object identifier.
     *
     * @return string
     */
    public function getIdentifierArgument();

    /**
     * Get the description of the argument dedicated for the object identifier.
     *
     * @return string
     */
    public function getIdentifierArgumentDescription();

    /**
     * Get the method name for get the field can be used in output console.
     *
     * @return string
     */
    public function getDisplayNameMethod();
}
