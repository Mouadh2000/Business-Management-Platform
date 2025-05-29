<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Admin;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::with([
            'project:id,name,status',
            'assignees:id,first_name,last_name,email',
            'timeLogs'
        ]);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->latest()->get()->map(function ($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'status' => $task->status,
                'priority' => $task->priority,
                'start_date' => $task->start_date,
                'deadline' => $task->deadline,
                'project' => $task->project ? [
                    'id' => $task->project->id,
                    'name' => $task->project->name,
                    'status' => $task->project->status
                ] : null,
                'assignees' => $task->assignees->map(function ($assignee) {
                    return [
                        'id' => $assignee->id,
                        'name' => $assignee->first_name . ' ' . $assignee->last_name,
                        'email' => $assignee->email,
                        'assignment_status' => $assignee->pivot->status,
                        'decline_reason' => $assignee->pivot->decline_reason

                    ];
                }),
                'time_logs' => $task->timeLogs
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }
    public function myTasks(Request $request)
    {
        $tasks = Task::with([
                'project:id,name,status',
                'assignees:id,first_name,last_name,email',
                'timeLogs'
            ])
            ->whereHas('assignees', fn($q) => $q->where('user_id', Auth::id()))
            ->when($request->project_id, fn($q, $projectId) => $q->where('project_id', $projectId))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->latest()
            ->get()
            ->map(function ($task) {
                return [
                    'id'          => $task->id,
                    'title'       => $task->title,
                    'description' => $task->description,
                    'status'      => $task->status,
                    'priority'    => $task->priority,
                    'start_date' => $task->start_date,
                    'deadline'   => $task->deadline,
                    'project'     => $task->project ? [
                        'id'     => $task->project->id,
                        'name'   => $task->project->name,
                        'status' => $task->project->status
                    ] : null,
                    'assignees' => $task->assignees->map(fn($assignee) => [
                        'id'                => $assignee->id,
                        'assignment_status' => $assignee->pivot->status
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $tasks
        ]);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,completed,blocked',
            'priority' => 'nullable|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
            'assignees' => 'required|array',
            'assignees.*' => 'exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'todo',
            'priority' => $request->priority ?? 'medium',
            'start_date' => $request->start_date,
            'deadline' => $request->deadline,
        ]);

        $assignments = [];
        foreach ($request->assignees as $userId) {
            $assignments[] = [
                'task_id' => $task->id,
                'user_id' => $userId,
                'assigned_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TaskAssignment::insert($assignments);

        $task->load(['project', 'assignees']);

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task created and assigned successfully'
        ], 201);
    }

    public function show($id)
    {
        $task = Task::with(['project', 'assignees', 'timeLogs'])->find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:todo,in_progress,completed,blocked',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $task,
            'message' => 'Task updated successfully'
        ]);
    }

    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, $taskId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:todo,in_progress,completed,blocked'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $task = Task::find($taskId);
        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $isAssigned = $task->assignees()->where('user_id', Auth::id())->exists();
        
        if (!Auth::user()->is_admin && !$isAssigned) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this task'
            ], 403);
        }

        $task->update(['status' => $request->status]);

        $task->load(['project', 'assignees']);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => $task
        ]);
    }
    public function countTasks()
    {
        $count = Task::count();
    
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ],
        ]);
    }
    public function countTasksByMonth()
    {
        $counts = Task::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    }
}