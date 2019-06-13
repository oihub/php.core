<?php

namespace oihub\exception;

class OverrideServiceException extends RuntimeException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Cannot override service "%s".', $name));
    }
}
