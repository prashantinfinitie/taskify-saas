<?php

namespace App\Http\Controllers;

use App\Models\LeadFollowUp;
use App\Services\DeletionService;
use Illuminate\Http\Request;

class LeadFollowUpController extends Controller
{
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $formFields = $request->validate([
                'assigned_to' => 'required|exists:users,id',
                'lead_id' => 'required|exists:leads,id',
                'type' => 'required|in:email,sms,call,meeting,other',
                'status' => 'required|in:pending,completed,rescheduled',
                'follow_up_at' => 'required|date',
                'note' => 'nullable|string|max:255',
            ]);

            if (!empty($formFields['follow_up_at'])) {
                // Convert local time to UTC
                $localDate = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $formFields['follow_up_at'], config('app.timezone'));
                $utcDate = $localDate->copy()->setTimezone('UTC');
                $formFields['follow_up_at'] = $utcDate->format('Y-m-d H:i:s');
            }

            $follow_up = LeadFollowUp::create($formFields);

            return response()->json([
                'message' => 'Follow-up created successfully.',
                'data' => $follow_up
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $follow_up = LeadFollowUp::findOrFail($id);
        $follow_up->load('assignedTo','lead');
        return response()->json([
            'error' => false,
            'message' => 'Follow Up Retrived Successfully',
            'follow_up' => $follow_up
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
{
    try {
        // Find the follow-up record
        $follow_up = LeadFollowUp::findOrFail($request->id);

        // Validate input
        $formFields = $request->validate([
            'assigned_to' => 'required|exists:users,id',
            'type' => 'required|in:email,sms,call,meeting,other',
            'status' => 'required|in:pending,completed,rescheduled',
            'follow_up_at' => 'required|date',
            'note' => 'nullable|string|max:255',
        ]);

        // Handle timezone conversion for follow_up_at
        if (!empty($formFields['follow_up_at'])) {
            $localDate = \Carbon\Carbon::createFromFormat('Y-m-d\TH:i', $formFields['follow_up_at'], config('app.timezone'));
            $utcDate = $localDate->copy()->setTimezone('UTC');
            $formFields['follow_up_at'] = $utcDate->format('Y-m-d H:i:s');
        }

        // Update the record
        $follow_up->update($formFields);

        // ✅ Send success JSON response
        return response()->json([
            'message' => 'Follow-up updated successfully.',
            'data' => $follow_up
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update follow-up.',
            'error' => $e->getMessage()
        ], 500);
    }
}



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = DeletionService::delete(LeadFollowUp::class, $id, 'Lead Follow Up');
        return $response;
    }
}
