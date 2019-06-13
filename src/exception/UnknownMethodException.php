<?php

namespace oihub\exception;

class UnknownMethodException extends BadMethodCallException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('Method "%s" is not defined.', $name));
    }
}
