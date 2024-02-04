<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class DirectMessage extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Direct_Messages';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'DM_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'DM_CreatedAt';
    const UPDATED_AT = 'DM_UpdatedAt';
    const DELETED_AT = 'DM_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'DM_Content',
        'DM_FileUrl',
        'DM_MemberID',
        'DM_ConversationID',
    ];
}
