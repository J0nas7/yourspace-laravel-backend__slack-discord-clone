<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class Conversation extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Conversation';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Conversation_ID';

    /** Eloquent columns but with different column names */
    const CREATED_AT = 'Conversation_CreatedAt';
    const UPDATED_AT = 'Conversation_UpdatedAt';
    const DELETED_AT = 'Conversation_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Conversation_MemberOne_ID',
        'Conversation_MemberTwo_ID',

        'Conversation_DeletedAt',
    ];
}
