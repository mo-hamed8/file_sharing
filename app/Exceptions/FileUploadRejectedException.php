<?php

namespace App\Exceptions;

class FileUploadRejectedException extends RoomException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}
