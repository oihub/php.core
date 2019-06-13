<?php

namespace oihub\exception;

class PermissionException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Permission error: "%s".', $name));
    }
}
