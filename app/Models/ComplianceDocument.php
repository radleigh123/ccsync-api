<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceDocument extends Model
{
    public function compliance(): BelongsTo
    {
        return $this->belongsTo(Compliance::class);
    }
}
