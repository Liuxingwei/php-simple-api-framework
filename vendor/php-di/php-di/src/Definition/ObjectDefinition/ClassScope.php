<?php

declare(strict_types=1);

namespace DI\Definition\ObjectDefinition;

class ClassScope
{
    private $value;

    public function __construct($value)
    {
        if (!$value) {
            $this->value = 'singleton';
        } else {
            $this->value = $value;
        }
    }

    public function getValue()
    {
        return $this->value;
    }
}
