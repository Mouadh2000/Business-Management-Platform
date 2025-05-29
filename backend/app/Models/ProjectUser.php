<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectUser extends Pivot
{
    protected $table = 'project_user';

    protected $fillable = [
        'project_id',
        'user_id',
        'role',
        'joined_at',
    ];

    public $timestamps = true;

    protected $dates = ['joined_at'];

    // Relationships

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(Admin::class, 'user_id');
    }
}
