<?php

namespace Lib\Core;

use Lib\Core\ErrorCode;

trait ErrorCodeTrait
{
    /**
     * @var ErrorCode
     */
    protected $error;

    /**
     * @Inject
     * @param ErrorCode $error
     * @return void
     */
    public function setErrorCode(ErrorCode $error)
    {
        $this->error = $error;
    }
}
