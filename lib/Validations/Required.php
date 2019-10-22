<?php

namespace Lib\Validations;

use Lib\Core\ErrorCode;
use Lib\Core\SafException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Required
{
    /**
     * @Required()
     */
    public $value;

    public function check($params)
    {
        if (is_array($this->value)) {
            foreach ($this->value as $value) {
                if (!key_exists($value, $params)) {
                    SafException::throw(ErrorCode::mapError(ErrorCode::PARAM_REQUIRED, ['param' => $value]));
                }
            }
        } else if (!key_exists($this->value, $params)) {
            SafException::throw(ErrorCode::mapError(ErrorCode::PARAM_REQUIRED, ['param' => $this->value]));
        }

        return true;
    }
}
