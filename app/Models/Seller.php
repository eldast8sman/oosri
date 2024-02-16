<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Seller extends Model implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'dob',
        'email',
        'password',
        'profile_photo',
        'location',
        'verification_token',
        'verification_token_expiry',
        'token',
        'token_expiry',
        'email_verified',
        'business_id',
        'last_login',
        'prev_login'
    ];

    protected $hidden = [
        'password',
        'verification_token',
        'verification_token_expiry',
        'token',
        'token_expiry'
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'prev_login' => 'datetime'
    ];

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
