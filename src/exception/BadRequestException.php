<?php

namespace oihub\exception;

class BadRequestException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Request fail: "%s".', $name));
    }
}
