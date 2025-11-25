<?php

namespace App\Enums;

enum Role: string
{
    case STUDENT = 'student';
    case OFFICER = 'officer';
    case ADMIN = 'admin';

    case SECRETARY = 'secretary';
    case TREASURER = 'treasurer';
    case AUDIDTOR = 'auditor';
    case REPRESENTATIVE = 'representative';
    case PRESIDENT = 'president';
    case VICE_PRESIDENT = 'vice-president';
}
