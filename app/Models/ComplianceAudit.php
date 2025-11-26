<?php

namespace App\Models;

use App\Http\Resources\Requirement\ComplianceAuditCollection;
use App\Http\Resources\Requirement\ComplianceAuditResource;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseResource(ComplianceAuditResource::class)]
#[UseResourceCollection(ComplianceAuditCollection::class)]
class ComplianceAudit extends Model
{
    protected $fillable = [
        'compliance_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

    protected $with = [
        'member:id,last_name,id_school_number,program,user_id',
    ];

    protected $hidden = [];

    protected $casts = [];

    public function compliance(): BelongsTo
    {
        return $this->belongsTo(Compliance::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'changed_by');
    }
}
