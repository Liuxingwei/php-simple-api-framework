<?php

namespace Lib\Validations;

/**
 * @Annotation
 * @Target({"METHOD","PROPERTY"})
 */
class Required
{
    public function __construct(array $values)
    { }
}
