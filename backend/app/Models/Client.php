<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;

class Client extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'company_name', 'first_name', 'last_name', 'email', 'password',
        'phone', 'status'
    ];

    protected $hidden = ['password'];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
