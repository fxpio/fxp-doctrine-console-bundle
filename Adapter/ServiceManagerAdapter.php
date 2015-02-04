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
 * Command Adapter for service manager.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
class ServiceManagerAdapter extends BaseServiceManagerAdapter
{
    /**
     * Set the class name of object.
     *
     * @param string $classname The class name of object
     */
    public function setClass($classname)
    {
        $this->classname = $classname;
    }

    /**
     * Set the short name of object.
     *
     * @param string $shortName The short name of object
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;
    }

    /**
     * Set the prefix of command name.
     *
     * @param string $commandPrefix The prefix command name
     */
    public function setCommandPrefix($commandPrefix)
    {
        $this->commandPrefix = $commandPrefix;
    }

    /**
     * Set the command description.
     *
     * @param string $commandDescription The command description
     */
    public function setCommandDescription($commandDescription)
    {
        $this->commandDescription = str_replace('{s}', '%s', $commandDescription);
    }

    /**
     * Set the identifier field of object.
     *
     * @param string $identifierField The identifier field of object
     */
    public function setIdentifierField($identifierField)
    {
        $this->identifierField = $identifierField;
    }

    /**
     * Set the command argument name of identifier.
     *
     * @param string $identifierArgument The command argument name of identifier
     */
    public function setIdentifierArgument($identifierArgument)
    {
        $this->identifierArgument = $identifierArgument;
    }

    /**
     * Set the command argument description of identifier.
     *
     * @param string $identifierArgumentDescription The description of the identifier argument
     */
    public function setIdentifierArgumentDescription($identifierArgumentDescription)
    {
        $this->identifierArgumentDescription = str_replace('{s}', '%s', $identifierArgumentDescription);
    }

    /**
     * Set the method name for display the object in console.
     *
     * @param string $displayNameMethod The method name for display the object in console
     */
    public function setDisplayNameMethod($displayNameMethod)
    {
        $this->displayNameMethod = $displayNameMethod;
    }

    /**
     * Set the method name of service manager for create the instance.
     *
     * @param string $createMethod The method name for create the instance
     */
    public function setCreateMethod($createMethod)
    {
        $this->createMethod = $createMethod;
    }

    /**
     * Set the method name of service manager for get the instance.
     *
     * @param string $getMethod The method name for get the instance
     */
    public function setGetMethod($getMethod)
    {
        $this->getMethod = $getMethod;
    }

    /**
     * Set the method name of service manager for update the instance.
     *
     * @param string $updateMethod The method name for update the instance
     */
    public function setUpdateMethod($updateMethod)
    {
        $this->updateMethod = $updateMethod;
    }

    /**
     * Set the method name of service manager for delete the instance.
     *
     * @param string $deleteMethod The method name for delete the instance
     */
    public function setDeleteMethod($deleteMethod)
    {
        $this->deleteMethod = $deleteMethod;
    }

    /**
     * Set the method name of service manager for undelete the instance.
     *
     * @param string $undeleteMethod The method name for undelete the instance
     */
    public function setUndeleteMethod($undeleteMethod)
    {
        $this->undeleteMethod = $undeleteMethod;
    }
}
