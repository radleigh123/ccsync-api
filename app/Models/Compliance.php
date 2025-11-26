<?php

namespace App\Models;

use App\Http\Resources\Requirement\ComplianceResource;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[UseResource(ComplianceResource::class)]
class Compliance extends Model
{
    protected $fillable = [
        'offering_id',
        'member_id',
        'status',
        'verified_at',
        'verified_by',
        'attempt',
        'notes',
    ];

    /* protected $with = [
        'offering:id,active,requirement_id',
        'member:id,last_name,id_school_number,program,year,user_id',
        'audits:id,compliance_id,new_status,changed_by',
        'documents:id,compliance_id,file_name',
    ]; */

    protected $hidden = [];

    protected $casts = [];

    public function offering(): BelongsTo
    {
        return $this->belongsTo(Offering::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ComplianceDocument::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(ComplianceAudit::class);
    }
}
