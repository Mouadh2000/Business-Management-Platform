<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Events\ProjectDeadlineSoon;
use Carbon\Carbon;

class CheckProjectDeadlines extends Command
{
    protected $signature = 'check:project-deadlines';
    protected $description = 'Send notification if project deadline is within 24 hours';

    public function handle()
    {
        $now = Carbon::now();
        $threshold = $now->copy()->addHours(24);

        $projects = Project::whereBetween('deadline', [$now, $threshold])->get();

        foreach ($projects as $project) {
            broadcast(new ProjectDeadlineSoon($project));
        }

        $this->info('Checked project deadlines.');
    }
}
