<?php

declare(strict_types=1);

namespace DI\Annotation;

use InvalidArgumentException;

/**
 * "Scope" annotation.
 *
 *
 * @api
 *
 * @Annotation
 * @Target({"CLASS"})
 */
final class Scope
{
    /**
     * The value of scope
     *
     * @var array
     */
    private $value;

    /**
     * Get annotation's values, and put 'value' to value of scope
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (!isset($values['value'])) {
            $this->value = 'singleton';
            return;
        }

        $value = $values['value'];

        if (in_array($value, ['singleton', 'prototype'])) {
            $this->value = $value;
        } else {
            throw new InvalidArgumentException(sprintf(
                '@Scode({"value"}) expects "value" is "singleton" or "prototype", %s given.',
                json_encode($value)
            ));
        }
    }

    /**
     * Get value of scope
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}
