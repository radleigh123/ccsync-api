<?php

namespace App\Enums;

enum Status: string
{
    case OPEN = 'open';
    case ONGOING = 'ongoing';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';
}
