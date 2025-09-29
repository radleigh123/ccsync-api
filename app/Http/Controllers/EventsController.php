<?php

namespace App\Http\Controllers;

use App\Models\Events;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventsController extends Controller
{
    /**
     * Display a listing of events
     * GET /api/events
     */
    public function index(Request $request)
    {
        try {
            $query = Events::with(['members:id,first_name,last_name,id_school_number']);

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter upcoming events
            if ($request->has('upcoming') && $request->upcoming == 'true') {
                $query->upcoming();
            }

            // Filter open events
            if ($request->has('open') && $request->open == 'true') {
                $query->open();
            }

            $events = $query->orderBy('event_date', 'asc')->get();

            // Add computed attributes for frontend
            $events->transform(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'venue' => $event->venue,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'time_from' => $event->time_from,
                    'time_to' => $event->time_to,
                    'registration_start' => $event->registration_start->format('Y-m-d'),
                    'registration_end' => $event->registration_end->format('Y-m-d'),
                    'max_participants' => $event->max_participants,
                    'status' => $event->status,
                    'available_slots' => $event->available_slots,
                    'is_full' => $event->is_full,
                    'is_registration_open' => $event->is_registration_open,
                    'attendees' => $event->members->count(),
                    'members' => $event->members,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                ];
            });

            return response()->json([
                'message' => 'Events retrieved successfully',
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving events',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Store a newly created event
     * POST /api/events
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'required|string|max:255',
            'event_date' => 'required|date|after:today',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i|after:time_from',
            'registration_start' => 'required|date|before_or_equal:event_date',
            'registration_end' => 'required|date|after_or_equal:registration_start|before_or_equal:event_date',
            'max_participants' => 'required|integer|min:1',
            'status' => 'nullable|in:open,closed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Events::create($validator->validated());

            return response()->json([
                'message' => 'Event created successfully',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating event',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Display the specified event
     * GET /api/events/{id}
     */
    public function show($id)
    {
        try {
            $event = Events::with(['members:id,first_name,last_name,id_school_number,program'])
                ->findOrFail($id);

            $eventData = [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'venue' => $event->venue,
                'event_date' => $event->event_date->format('Y-m-d'),
                'time_from' => $event->time_from,
                'time_to' => $event->time_to,
                'registration_start' => $event->registration_start->format('Y-m-d'),
                'registration_end' => $event->registration_end->format('Y-m-d'),
                'max_participants' => $event->max_participants,
                'status' => $event->status,
                'available_slots' => $event->available_slots,
                'is_full' => $event->is_full,
                'is_registration_open' => $event->is_registration_open,
                'attendees' => $event->members->count(),
                'members' => $event->members,
                'created_at' => $event->created_at,
                'updated_at' => $event->updated_at,
            ];

            return response()->json([
                'message' => 'Event retrieved successfully',
                'data' => $eventData
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found',
                'errors' => ['Event with ID ' . $id . ' does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving event',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Update the specified event
     * PUT/PATCH /api/events/{id}
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'venue' => 'sometimes|string|max:255',
            'event_date' => 'sometimes|date',
            'time_from' => 'sometimes|date_format:H:i',
            'time_to' => 'sometimes|date_format:H:i|after:time_from',
            'registration_start' => 'sometimes|date',
            'registration_end' => 'sometimes|date|after_or_equal:registration_start',
            'max_participants' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:open,closed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Events::findOrFail($id);
            $event->update($validator->validated());

            return response()->json([
                'message' => 'Event updated successfully',
                'data' => $event
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found',
                'errors' => ['Event with ID ' . $id . ' does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating event',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Remove the specified event
     * DELETE /api/events/{id}
     */
    public function destroy($id)
    {
        try {
            $event = Events::findOrFail($id);
            $event->delete();

            return response()->json([
                'message' => 'Event deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event not found',
                'errors' => ['Event with ID ' . $id . ' does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting event',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Register a member to an event
     * POST /api/events/{id}/register
     */
    public function registerMember(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'member_id' => 'required|exists:members,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Events::findOrFail($id);
            $member = Member::findOrFail($request->member_id);

            // Check if registration is open
            if (!$event->is_registration_open) {
                return response()->json([
                    'message' => 'Registration is not open for this event',
                    'errors' => ['Registration period has ended or event is full']
                ], 400);
            }

            // Check if member is already registered
            if ($event->members()->where('member_id', $member->id)->exists()) {
                return response()->json([
                    'message' => 'Member is already registered for this event',
                    'errors' => ['Duplicate registration not allowed']
                ], 400);
            }

            // Register the member
            $event->members()->attach($member->id, [
                'registered_at' => now()
            ]);

            return response()->json([
                'message' => 'Member registered successfully',
                'data' => [
                    'event' => $event->name,
                    'member' => $member->first_name . ' ' . $member->last_name,
                    'registered_at' => now()
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event or member not found',
                'errors' => ['Resource does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error registering member',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Unregister a member from an event
     * DELETE /api/events/{id}/unregister/{memberId}
     */
    public function unregisterMember($id, $memberId)
    {
        try {
            $event = Events::findOrFail($id);
            $member = Member::findOrFail($memberId);

            // Check if member is registered
            if (!$event->members()->where('member_id', $member->id)->exists()) {
                return response()->json([
                    'message' => 'Member is not registered for this event',
                    'errors' => ['Cannot unregister non-registered member']
                ], 400);
            }

            // Unregister the member
            $event->members()->detach($member->id);

            return response()->json([
                'message' => 'Member unregistered successfully',
                'data' => [
                    'event' => $event->name,
                    'member' => $member->first_name . ' ' . $member->last_name
                ]
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Event or member not found',
                'errors' => ['Resource does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error unregistering member',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
