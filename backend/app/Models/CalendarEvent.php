<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $table = 'calendar_events';

    protected $fillable = [
        'title',
        'type',
        'admin_id',
        'client_id',
        'project_id',
        'appointment_date',
        'start_time',
        'end_time',
        'description'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    // Relationships
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Accessors
    public function getStartDateTimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d') . ' ' . $this->start_time;
    }

    public function getEndDateTimeAttribute()
    {
        return $this->appointment_date->format('Y-m-d') . ' ' . $this->end_time;
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('appointment_date', '>=', now()->format('Y-m-d'))
                    ->orderBy('appointment_date')
                    ->orderBy('start_time');
    }

    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('admin_id', $adminId);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }
}