<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /* The table associated with the model.
     * @var string */
    protected $table = 'Profile';

    /* The primary key associated with the table.
     * @var string */
    protected $primaryKey = 'Profile_ID';

    /** created_at and updated_at columns but with different column names */
    const CREATED_AT = 'Profile_CreatedAt';
    const UPDATED_AT = 'Profile_UpdatedAt';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Profile_RealName',
        'Profile_DisplayName',
        'Profile_Email',
        'Profile_Password',
        'Profile_ImageUrl',
        'Profile_Birthday'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Profile_Password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'Profile_Password' => 'hashed',
    ];

    // The database field that should be returned on Eloquent's request
    public function getAuthPassword() {
        return $this->Profile_Password;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
