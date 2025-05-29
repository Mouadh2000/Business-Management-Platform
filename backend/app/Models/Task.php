<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'title', 'description', 'status', 'priority',
        'start_date', 'deadline', 'completed_at'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignees()
    {
        return $this->belongsToMany(Admin::class, 'task_assignments', 'task_id', 'user_id')
                    ->withPivot('assigned_by', 'assigned_at', 'completed_at')
                    ->withTimestamps();
    }

    public function timeLogs()
    {
        return $this->hasMany(TimeLog::class);
    }
}
