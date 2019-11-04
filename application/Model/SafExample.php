<?php

namespace Application\Model;

use Lib\Core\DB;

/**
 * @Scope("prototype")
 */
class SafExample extends DB
{
    protected $table = 'saf_example';
}
