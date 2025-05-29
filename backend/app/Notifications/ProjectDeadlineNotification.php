<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Project;

class ProjectDeadlineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'project_id' => $this->project->id,
            'project_name' => $this->project->name,
            'deadline' => $this->project->deadline->toDateTimeString(),
            'message' => "Project '{$this->project->name}' deadline is approaching",
            'type' => 'project_deadline'
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id' => $this->id,
            'read_at' => null,
            'data' => [
                'project_id' => $this->project->id,
                'project_name' => $this->project->name,
                'deadline' => $this->project->deadline->toDateTimeString(),
                'message' => "Project '{$this->project->name}' deadline is approaching",
                'type' => 'project_deadline'
            ]
        ]);
    }
}