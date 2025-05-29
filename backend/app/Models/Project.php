<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'description', 'start_date', 'deadline', 'status', 'price', 'client_id'
    ];
    // Or for Laravel 7+, use casts instead:
    protected $casts = [
        'start_date' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function users()
    {
        return $this->belongsToMany(Admin::class, 'project_user', 'project_id', 'user_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }
}
