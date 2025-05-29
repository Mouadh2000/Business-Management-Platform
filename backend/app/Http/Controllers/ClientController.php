<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Mail\ClientCredentialsMail;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        
        return response()->json([
            'success' => true,
            'data' => $clients
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8',
            'status' => 'sometimes|in:active,inactive,pending'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->all();
        
        $client = Client::create($data);

        $this->sendClientCredentials($request->email, $request->password);

        return response()->json([
            'success' => true,
            'data' => $client
        ], 201);
    }

    protected function sendClientCredentials($email, $password)
    {
        Mail::to($email)->send(new ClientCredentialsMail($email, $password));
    }

    public function show($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function update(Request $request, $id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_name' => 'sometimes|string|max:255',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:clients,email,'.$client->id,
            'phone' => 'sometimes|string|max:20',
            'status' => 'sometimes|in:active,inactive,pending'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $client->update($request->all());

        return response()->json([
            'success' => true,
            'data' => $client
        ]);
    }

    public function destroy($id)
    {
        $client = Client::find($id);

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found'
            ], 404);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully'
        ]);
    }
    public function countClients()
    {
        $count = Client::count();
    
        return response()->json([
            'success' => true,
            'data' => [
                'count' => $count
            ],
        ]);
    }
}