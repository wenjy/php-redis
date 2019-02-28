<?php

/**
 * @author: jiangyi
 * @date: 下午10:05 2018/12/13
 */

namespace EasyRedis\Exceptions;

use Throwable;

class RedisException extends \Exception
{
    public $errorInfo = [];

    public function __construct($message = "", $errorInfo = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        return 'Exception';
    }
}
