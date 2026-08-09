<?php

namespace App\Enums;

enum FileStatus: string
{
    case Active = 'active';
    case Deleted = 'deleted';
}
