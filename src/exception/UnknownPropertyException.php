<?php

namespace oihub\exception;

class UnknownPropertyException extends BadMethodCallException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Property "%s" is not defined.', $name));
    }
}
