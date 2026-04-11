<?php

namespace App\Exceptions;

use Exception;

class BcaException extends Exception
{
protected array $context;

    public function __construct(string $message, array $context = [], int $code = 0)
    {
        parent::__construct($message, $code);
        $this->context = $context;
    }

    public function context(): array
    {
        return $this->context;
    }
}
