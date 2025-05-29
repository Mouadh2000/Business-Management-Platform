<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Admin;
use App\Models\TaskAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class TaskAssignmentController extends Controller
{
    public function taskAssignments($taskId)
    {
        $task = Task::find($taskId);

        if (!$task) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found'
            ], 404);
        }

        $assignments = $task->assignments()->with(['user', 'assignedBy'])->get();

        return response()->json([
            'success' => true,
            'data' => $assignments
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:tasks,id',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:admins,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $assignments = [];
        foreach ($request->user_ids as $userId) {
            $assignments[] = [
                'task_id' => $request->task_id,
                'user_id' => $userId,
                'assigned_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        TaskAssignment::insert($assignments);

        return response()->json([
            'success' => true,
            'message' => 'Users assigned to task successfully'
        ], 201);
    }

    

    public function destroy($assignmentId)
    {
        $assignment = TaskAssignment::find($assignmentId);

        if (!$assignment) {
            return response()->json([
                'success' => false,
                'message' => 'Assignment not found'
            ], 404);
        }

        $assignment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully'
        ]);
    }
}