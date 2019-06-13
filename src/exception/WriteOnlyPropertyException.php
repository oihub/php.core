<?php

namespace oihub\exception;

class WriteOnlyPropertyException extends BadMethodCallException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Write-only property: "%s".', $name));
    }
}
