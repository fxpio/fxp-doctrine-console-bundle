<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\DoctrineConsoleBundle\Helper;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Helper for display all fields of doctrine object in console.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
abstract class DetailObjectHelper
{
    /**
     * Humanize the camelcase text.
     *
     * @param string $text The camelcase input
     *
     * @return string The humanized input
     */
    public static function humanize($text)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $text, $matches);
        $ret = $matches[0];

        foreach ($ret as &$match) {
            $match = $match == strtoupper($match)
                ? strtolower($match)
                : lcfirst($match);
        }

        return ucfirst(implode(' ', $ret));
    }

    /**
     * Display the detail of object.
     *
     * @param OutputInterface $output   The output console instance
     * @param object          $instance The object instance
     */
    public static function display(OutputInterface $output, $instance)
    {
        $ref = new \ReflectionClass($instance);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PUBLIC);
        $table = new Table($output);
        $table->setStyle('compact');

        foreach ($methods as $method) {
            $methodName = $method->getName();

            if (preg_match('/^get|has|is/', $methodName) && 0 === $method->getNumberOfParameters()) {
                $value = static::getFieldValue($instance, $methodName);
                $methodName = preg_match('/^get/', $methodName) ? substr($methodName, 3) : $methodName;
                $table->addRow(array('<comment>'.static::humanize($methodName).'</comment>', ': '.$value));
            }
        }

        $table->render();
    }

    /**
     * Get the formatted value of field.
     *
     * @param mixed  $instance   The object instance
     * @param string $methodName The method name of field
     *
     * @return string
     */
    protected static function getFieldValue($instance, $methodName)
    {
        try {
            $value = static::formatValue($instance->$methodName());
        } catch (\Exception $e) {
            $value = '<error>format error</error>';
        }

        return $value;
    }

    /**
     * Format the value of field.
     *
     * @param mixed $value The value of field.
     *
     * @return string
     */
    protected static function formatValue($value)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format(\DateTime::ISO8601);
        } elseif (is_bool($value)) {
            $value = $value ? 'True' : 'False';
        } elseif (is_array($value) || $value instanceof \IteratorAggregate) {
            $itValue = $value instanceof \IteratorAggregate ? $value->getIterator() : $value;
            $value = array();
            foreach ($itValue as $key => $arrayValue) {
                $value[$key] = static::formatValue($arrayValue);
            }
            $value = implode("\n  ", $value);
        }

        return (string) $value;
    }
}
