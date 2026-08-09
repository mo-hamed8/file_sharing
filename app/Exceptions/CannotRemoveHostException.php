<?php

namespace App\Exceptions;

class CannotRemoveHostException extends RoomException
{
    public function __construct(string $message = 'The host cannot leave the room. End the room instead.')
    {
        parent::__construct($message, 422);
    }
}
