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

    private $value;

    public function __construct(array $values)
    {
        if (!isset($values['value'])) {
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

    public function getValue()
    {
        return $this->value;
    }
}
