<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Channel extends Model
{
    use HasFactory, Notifiable;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Channel';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Channel_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'Channel_CreatedAt';
    const UPDATED_AT = 'Channel_UpdatedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Channel_Name',
        'Channel_Type',
        'Channel_ProfileID',
        'Channel_SpaceID'
    ];
}
