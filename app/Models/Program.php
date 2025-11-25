<?php

namespace App\Models;

use App\Http\Resources\Member\ProgramResource;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseResource(ProgramResource::class)]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory;

    /* 
    Since Eloquent assumes every table has PK names `id`,
    tables with custom PKs must define the following:
     - primaryKey
     - incrementing
     - keyType
    */
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestampped.
     * @var bool
     */
    public $timestamps = false;

    protected $fillable = [
        'code',
        'name',
    ];

    /**
     * Get the members for the program.
     */
    public function members()
    {
        return $this->hasMany(Member::class, 'program', 'code');
    }
}
