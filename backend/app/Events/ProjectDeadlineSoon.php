<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\Project;

class ProjectDeadlineSoon implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function broadcastOn()
    {
        return new Channel('project.deadline');
    }

    public function broadcastAs()
    {
        return 'ProjectDeadlineSoon';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->project->id,
            'name' => $this->project->name,
            'deadline' => $this->project->deadline ? $this->project->deadline->toDateTimeString() : null,
            'message' => "Project deadline is approaching: {$this->project->name}"

        ];
    }
}
