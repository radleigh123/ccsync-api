<?php

namespace App\Enums;

enum RequirementStatus: string
{
    case DRAFT = 'Draft';
    case SUBMITTED = 'Submitted';
    case PENDING = 'Pending Progress';
    case APPROVED = 'Approved';
    case EXPIRED = 'Expired';
    case Canceled = 'Canceled';
}
