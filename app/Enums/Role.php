<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case PRESIDENT = 'president';
    case VICE_PRESIDENT_INT = 'vice-president-internal';
    case VICE_PRESIDENT_EXT = 'vice-president-external';
    case SECRETARY = 'secretary';
    case SECRETARY_ASST = 'assistant-secretary';
    case TREASURER = 'treasurer';
    case TREASURER_ASST = 'assistant-treasurer';
    case AUDIDTOR = 'auditor';
    case PUBLIC_INFORMATION_OFFICER_INT = 'PIO-internal';
    case PUBLIC_INFORMATION_OFFICER_EXT = 'PIO-external';
    case REPRESENTATIVE_1 = 'representative-1';
    case REPRESENTATIVE_2 = 'representative-2';
    case REPRESENTATIVE_3 = 'representative-3';
    case REPRESENTATIVE_4 = 'representative-4';
    case OFFICER = 'officer';
    case STUDENT = 'student';
}
