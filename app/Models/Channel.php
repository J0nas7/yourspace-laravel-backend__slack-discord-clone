<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class Channel extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Channel';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Channel_ID';

    /** Eloquent columns but with different column names */
    const CREATED_AT = 'Channel_CreatedAt';
    const UPDATED_AT = 'Channel_UpdatedAt';
    const DELETED_AT = 'Channel_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Channel_Name',
        'Channel_Type',
        'Channel_Access',
        'Channel_ProfileID',
        'Channel_SpaceID'
    ];
}
