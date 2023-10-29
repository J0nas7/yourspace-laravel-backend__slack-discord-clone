<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Space';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Space_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'Space_CreatedAt';
    const UPDATED_AT = 'Space_UpdatedAt';
    const DELETED_AT = 'Space_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Space_Name',
        'Space_ImageUrl',
        'Space_InviteCode',
        'Space_ProfileID',
        'Space_CreatedAt',
        'Space_UpdatedAt'
    ];

    // /* The model's default values for attributes.
    //  * @var array */
    // protected $attributes = [
    //     'Space_ID' => 0,
    //     'Space_Name' => 0,
    //     'Space_ImageUrl' => 0,
    //     'Space_InviteCode' => 0,
    //     'Space_CreatedAt' => 0,
    //     'Space_UpdatedAt' => 0
    // ];

    protected $appends = [
        
    ];
}
