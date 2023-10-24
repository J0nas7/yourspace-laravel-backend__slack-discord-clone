<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Member extends Model
{
    use HasFactory, Notifiable;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Member';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Member_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'Profile_CreatedAt';
    const UPDATED_AT = 'Profile_UpdatedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Member_Role',
        'Member_ProfileID',
        'Member_SpaceID'
    ];
}
