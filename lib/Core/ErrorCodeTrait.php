<?php

namespace Lib\Core;

use Lib\Core\ErrorCode;

trait ErrorCodeTrait
{
    /**
     * @var ErrorCode
     */
    protected $errCode;

    /**
     * @Inject
     * @param ErrorCode $errCode
     * @return void
     */
    public function setErrorCode(ErrorCode $errCode)
    {
        $this->errCode = $errCode;
    }
}
