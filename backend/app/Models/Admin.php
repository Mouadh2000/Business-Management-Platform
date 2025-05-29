<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;


class Admin extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'first_name', 'last_name', 'username', 'email',
        'password', 'is_staff', 'is_admin'
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
        return $this->belongsToMany(Project::class, 'project_user', 'user_id', 'project_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }
    public function receivesBroadcastNotificationsOn()
    {
        return 'App.Models.Admin.' . $this->id;
    }
}