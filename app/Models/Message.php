<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory, Notifiable, SoftDeletes;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Message';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Message_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'Message_CreatedAt';
    const UPDATED_AT = 'Message_UpdatedAt';
    const DELETED_AT = 'Message_DeletedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Message_Content',
        'Message_FileUrl',
        'Message_MemberID',
        'Message_ChannelID',
        'deleted'
    ];
}
