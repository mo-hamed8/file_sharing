<?php

namespace App\Exceptions;

class RoomNotJoinableException extends RoomException
{
    public function __construct(string $message = 'This room is no longer active.')
    {
        parent::__construct($message, 410);
    }
}
