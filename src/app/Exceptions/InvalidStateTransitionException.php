<?php

namespace App\Exceptions;

use RuntimeException;

class InvalidStateTransitionException extends RuntimeException
{
    public function __construct(string $message = 'Transição de estado inválida.')
    {
        parent::__construct($message);
    }
}
