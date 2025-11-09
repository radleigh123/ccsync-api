<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends Controller
{
    /**
     * Display a listing of events
     * GET /api/events
     */
    public function index(Request $request)
    {
        try {
            $query = Event::query();

            // TODO: finalize ERD
            // TODO: coordinate with DB for updated status
            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter upcoming events
            if ($request->has('upcoming') && $request->upcoming == 'true') {
                $query->upcoming();
            }

            // Filter events on current month
            if ($request->has('current') && $request->current == 'true') {
                $query->thisMonth();
            }

            // Filter open events
            // hmmmmm iz redudant since we have status, but here lng sa for testing purposes
            if ($request->has('open') && $request->open == 'true') {
                $query->open();
            }

            $events = $query->orderBy('event_date', 'asc')->get();

            // TODO: Use maybe RESOURCES
            // Add computed attributes for frontend
            $events->transform(function ($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'venue' => $event->venue,
                    'event_date' => $event->event_date->format('Y-m-d'),
                    'time_from' => $event->time_from->format('H:i:s'),
                    'time_to' => $event->time_to->format('H:i:s'),
                    'registration_start' => $event->registration_start->format('Y-m-d'),
                    'registration_end' => $event->registration_end->format('Y-m-d'),
                    'status' => $event->status,
                    'is_full' => $event->is_full,
                    'is_registration_open' => $event->is_registration_open,
                    'due_days' => $event->registration_due,
                    'max_participants' => $event->max_participants,
                    'attendees' => $event->members->count(),
                    'available_slots' => $event->available_slots,
                    'created_at' => $event->created_at,
                    'updated_at' => $event->updated_at,
                ];
            });

            return response()->json(['events' => $events]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving events',
                'error' => $e->getMessage()
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
            $event = Event::create($validator->validated());

            return response()->json([
                'message' => 'Event created successfully',
                'event' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating event',
                'error' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Display the specified event
     * GET /api/events/{id}
     */
    public function show(string $id)
    {
        try {
            $query = Event::findOrFail($id);

            $eventData = [
                'id' => $query->id,
                'name' => $query->name,
                'description' => $query->description,
                'venue' => $query->venue,
                'event_date' => $query->event_date->format('Y-m-d'),
                'time_from' => $query->time_from->format('H:i:s'),
                'time_to' => $query->time_to->format('H:i:s'),
                'registration_start' => $query->registration_start->format('Y-m-d'),
                'registration_end' => $query->registration_end->format('Y-m-d'),
                'status' => $query->status,
                'is_full' => $query->is_full,
                'is_registration_open' => $query->is_registration_open,
                'due_days' => $query->registration_due,
                'max_participants' => $query->max_participants,
                'attendees' => $query->members->count(),
                'available_slots' => $query->available_slots,
                'created_at' => $query->created_at->format('Y-m-d H:m:s'),
                'updated_at' => $query->updated_at->format('Y-m-d H:m:s'),
            ];

            return response()->json(['event' => $eventData]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Event not found',
                'errors' => ['Event with ID ' . $id . ' does not exist']
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
            'time_from' => 'sometimes|date_format:H:i:s',
            'time_to' => 'sometimes|date_format:H:i:s|after:time_from',
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
            $event = Event::findOrFail($id);
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
            $event = Event::findOrFail($id);
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
            $event = Event::findOrFail($id);
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
            $event = Event::findOrFail($id);
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

    /**
     * Get all members registered to event
     */
    public function getEventMembers(Request $request, string $id)
    {
        try {
            $perPage = $request->input('per_page', 10);

            $event = Event::findOrFail($id);
            // $query = Events::with(['members:id,user_id,first_name,last_name,id_school_number,program']);
            // $event = Events::with(['members:id,user_id,program'])->findOrFail($id);
            $members = $event->members()
                ->select('first_name', 'last_name', 'year', 'program')
                ->get();

            if ($request->has('page') && $request->has('per_page')) {
                $members = $event->members()
                    ->select('first_name', 'last_name', 'year', 'program')
                    ->paginate($perPage);
            }

            return response()->json([
                'success' => true, // Redudant since response status is enough
                'message' => 'Successfully retrieved registered members',
                'registered_members' => $members
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving registered members',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
