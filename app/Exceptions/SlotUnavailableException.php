<?php

namespace App\Exceptions;

use RuntimeException;

class SlotUnavailableException extends RuntimeException
{
    public function __construct(string $message = 'Sorry, that time slot is no longer available. Please choose another time.')
    {
        parent::__construct($message);
    }
}
