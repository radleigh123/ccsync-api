<?php

namespace App\Models;

use App\Http\Resources\Requirement\CompliancDocumentResource;
use App\Http\Resources\Requirement\ComplianceDocumentCollection;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Attributes\UseResourceCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseResource(CompliancDocumentResource::class)]
#[UseResourceCollection(ComplianceDocumentCollection::class)]
class ComplianceDocument extends Model
{
    protected $fillable = [
        'compliance_id',
        'file_path',
        'file_name',
        'mime',
        'uploaded_by',
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
        return $this->belongsTo(Member::class, 'uploaded_by');
    }
}
