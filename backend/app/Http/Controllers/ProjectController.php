<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\Admin;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;



class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }

    public function getProjectWithInfo()
    {
        $projects = Project::with(['client', 'users', 'tasks.assignees'])->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No projects found'
            ], 404);
        }

        $data = $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'start_date' => $project->start_date,
                'deadline' => $project->deadline,
                'status' => $project->status,
                'client' => $project->client ? [
                    'id' => $project->client->id,
                    'company_name' => $project->client->company_name,
                    'email' => $project->client->email,
                    'phone' => $project->client->phone,
                ] : null,
                'users' => $project->users->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->pivot->role ?? null,
                    ];
                }),
                'tasks' => $project->tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'status' => $task->status,
                        'priority' => $task->priority,
                        'start_date' => $task->start_date,
                        'deadline' => $task->deadline,
                        'completed_at' => $task->completed_at,
                        'email' => optional($task->assignees->first())->email,
                    ];
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }


    public function generateBill($projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $pdf = Pdf::loadView('projects.bill', [
            'project' => $project,
            'date' => now()->format('Y-m-d'),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'bill_pdf' => base64_encode($pdf->output()),
                'file_name' => "bill-project-{$project->id}.pdf"
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after:start_date',
            'price' => 'required|numeric',
            'client_id' => 'required|exists:clients,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $project = Project::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $project
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $rules = [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'deadline' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:pending,in_progress,completed,canceled',
            'client_id' => 'required|exists:clients,id',
        ];

        // Require these fields only if status is being set to 'completed'
        if ($request->status === 'completed') {
            $rules['appointment_date'] = 'required|date';
            $rules['start_time'] = 'required|';
            $rules['end_time'] = 'required|';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $project->update($request->only([
            'name', 'description', 'start_date', 'deadline', 'status', 'client_id'
        ]));

        $admin = $request->user();

        if ($request->status === 'completed') {
            CalendarEvent::create([
                'title' => 'Project Completed: ' . $project->name,
                'type' => 'appointment',
                'description' => $project->description,
                'appointment_date' => $request->appointment_date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
                'project_id' => $project->id,
                'client_id' => $project->client_id,
                'admin_id' => $admin->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $project
        ]);
    }


    public function destroy($id)
    {
        $project = Project::find($id);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully'
        ]);
    }

    public function addUserToProject(Request $request, $projectId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:admins,id',
            'role' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($project->users()->where('user_id', $request->user_id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already assigned to this project'
            ], 400);
        }

        $project->users()->attach($request->user_id, [
            'role' => $request->role,
            'joined_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User added to project successfully'
        ]);
    }

    public function removeUserFromProject($projectId, $userId)
    {
        $project = Project::find($projectId);

        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found'
            ], 404);
        }

        $project->users()->detach($userId);

        return response()->json([
            'success' => true,
            'message' => 'User removed from project successfully'
        ]);
    }
    public function countProjects()
    {
        $count = Project::count();
    
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ],
        ]);
    }
    public function countProjectsByMonth()
    {
        $counts = Project::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    }
    public function countClientProjectsByMonth()
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $counts = Project::where('client_id', $client->id)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $counts
        ]);
    }


    public function getProjectsForEachClient(Request $request)
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $projects = Project::where('client_id', $client->id)
            ->select('id', 'name', 'description', 'start_date', 'deadline', 'status')
            ->get();

        if ($projects->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No projects found for this client'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $projects
        ]);
    }


}