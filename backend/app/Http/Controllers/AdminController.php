<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index()
    {
        $admins = Admin::all();
        return response()->json([
            'success' => true,
            'data' => $admins
        ]);
    }

    public function staffOnly($projectId)
    {
        $assignedUserIds = \DB::table('project_user')
            ->where('project_id', $projectId)
            ->pluck('user_id')
            ->toArray();

        $staff = Admin::where('is_staff', true)
                    ->where('is_admin', false)
                    ->whereNotIn('id', $assignedUserIds)
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $staff
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:admins',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:8',
            'is_staff' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin = Admin::create($request->all());

        return response()->json([
            'success' => true,
            'data' => $admin,
            'message' => 'Admin created successfully'
        ], 201);
    }
    
    public function update(Request $request, $id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'username' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('admins')->ignore($admin->id),
            ],
            'password' => 'sometimes|string|min:8',
            'is_staff' => 'sometimes|boolean',
            'is_admin' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $admin->fill($request->all());
        $admin->save();

        return response()->json([
            'success' => true,
            'data' => $admin,
            'message' => 'Admin updated successfully'
        ]);
    }
    
    public function destroy($id)
    {
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found'
            ], 404);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin deleted successfully'
        ]);
    }
    public function countStaff()
    {
        $count = Admin::where('is_staff', true)
                    ->where('is_admin', false)
                    ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ],
        ]);
    }
}