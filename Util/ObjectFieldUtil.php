<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Util;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Helper for manipulate and validate the doctrine objects in console.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class ObjectFieldUtil
{
    /**
     * Add options in input definition.
     *
     * @param InputDefinition $definition  The input definition
     * @param array           $fields      The fields
     * @param string          $description The option description
     */
    public static function addOptions(InputDefinition $definition, array $fields, $description)
    {
        foreach ($fields as $field => $type) {
            $mode = 'array' === $type
                ? InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY
                : InputOption::VALUE_REQUIRED;

            if (!$definition->hasOption($field) && !$definition->hasArgument($field)) {
                $definition->addOption(new InputOption($field, null, $mode, sprintf($description, $field, $type)));
            }
        }
    }

    /**
     * Get field value in console input.
     *
     * @param InputInterface $input     The console input
     * @param string         $fieldName The field name
     *
     * @return mixed|null
     */
    public static function getFieldValue(InputInterface $input, $fieldName)
    {
        $value = null;

        if ($input->hasArgument($fieldName)) {
            $value = $input->getArgument($fieldName);
        } elseif ($input->hasOption($fieldName)) {
            $value = $input->getOption($fieldName);
        }

        return $value;
    }

    /**
     * Convert the magic "{{null}}" to null value and "{{empty}}" to array value.
     *
     * @param mixed $value
     *
     * @return mixed|null
     */
    public static function convertEmptyValue($value)
    {
        if ('{{null}}' === $value) {
            $value = null;
        } elseif ('{{empty}}' === $value) {
            $value = array();
        }

        return $value;
    }

    /**
     * Set the field value.
     *
     * @param mixed      $instance  The object instance
     * @param string     $fieldName The field name
     * @param mixed|null $value     The field value
     */
    public static function setFieldValue($instance, $fieldName, $value)
    {
        $setterMethodName = "set".ucfirst($fieldName);

        try {
            $ref = new \ReflectionClass($instance);
            $ref->getMethod($setterMethodName);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(sprintf('The setter method "%s" that should be used for property "%s" seems not to exist. Please check your spelling in the command option or in your implementation class.', $setterMethodName, $fieldName));
        }

        $instance->$setterMethodName($value);
    }
}
