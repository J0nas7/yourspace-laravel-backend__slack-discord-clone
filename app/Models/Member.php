<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Member';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Member_ID';

    /** Eloquent columns but with different column names */
    const CREATED_AT = 'Member_CreatedAt';
    const UPDATED_AT = 'Member_UpdatedAt';
    const DELETED_AT = 'Member_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Member_Role',
        'Member_ProfileID',
        'Member_SpaceID',

        'Member_CreatedAt',
        'Member_UpdatedAt',
    ];
}
