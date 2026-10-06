<?php

namespace App\Exceptions;

use Exception;

class TransitionInterditeException extends Exception
{
    public function __construct(string $message = "Transition de statut non autorisée.")
    {
        parent::__construct($message, 409);
    }
}
