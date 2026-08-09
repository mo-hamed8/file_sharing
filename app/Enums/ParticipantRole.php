<?php

namespace App\Enums;

enum ParticipantRole: string
{
    case Host = 'host';
    case Participant = 'participant';
}
