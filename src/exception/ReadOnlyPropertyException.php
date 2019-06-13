<?php

namespace oihub\exception;

class ReadOnlyPropertyException extends BadMethodCallException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Read-only property: "%s".', $name));
    }
}
