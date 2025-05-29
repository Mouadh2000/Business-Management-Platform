<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class CalendarEventController extends Controller
{
    /**
     * Display a listing of calendar events with project and client information.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = CalendarEvent::with([
                'project:id,name',
                'client:id,company_name,email'
            ])
            ->select([
                'id',
                'appointment_date',
                'start_time',
                'end_time',
                'description',
                'project_id',
                'client_id'
            ])
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'appointment_date' => $event->appointment_date,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'description' => $event->description,
                    'project_name' => $event->project->name ?? null,
                    'client_company' => $event->client
                        ? $event->client->company_name
                        : null,
                    'client_email' => $event->client->email ?? null
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $events
        ], 200);
    }
    public function clientEvents()
    {
        $client = Auth::guard('client')->user();

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $events = CalendarEvent::with('project:id,name')
            ->where('client_id', $client->id)
            ->select([
                'id',
                'appointment_date',
                'start_time',
                'end_time',
                'description',
                'project_id'
            ])
            ->get()
            ->map(function ($event) {
                return [
                    'project_name' => $event->project->name ?? null,
                    'description' => $event->description,
                    'appointment_date' => $event->appointment_date,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $events
        ], 200);
    }
}
