<?php

namespace App\Enums;

enum Type: string
{
    case DOCUMENT = 'document';
    case PAYMENT = 'payment';
    case OTHER = 'other';
}
