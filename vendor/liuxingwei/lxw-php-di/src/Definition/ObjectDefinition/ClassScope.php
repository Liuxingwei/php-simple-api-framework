<?php

declare(strict_types=1);

namespace DI\Definition\ObjectDefinition;

/**
 * Descripe an class's inject scope
 *
 * @author liu Xingwei <matchless@163.com>
 */
class ClassScope
{
    /**
     * Scope's value
     *
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    public function __construct(string $value)
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
