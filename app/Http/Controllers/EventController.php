<?php

namespace App\Http\Controllers;

use App\Helper\ApiResponse;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EventController extends Controller
{
    use ApiResponse;

    protected EventService $service;

    public function __construct(EventService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of events
     * GET /api/events
     */
    public function index(Request $request)
    {
        $events = $this->service->getAll($request->all());
        return $events->toResourceCollection();
    }

    /**
     * Store a newly created event
     * POST /api/events
     */
    public function store(StoreEventRequest $request)
    {
        $event = $this->service->create($request->validated());
        return $this->success($event->toResource(), 201);
    }

    /**
     * Display the specified event
     * GET /api/events/{id}
     */
    public function show(string $eventId)
    {
        try {
            return $this->success($this->service->find($eventId)->toResource());
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Event does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Update the specified event
     * PUT/PATCH /api/events/{id}
     */
    public function update(UpdateEventRequest $request, $eventId)
    {
        try {
            $event = $this->service->update($eventId, $request->validated());
            return $this->success(
                $event->toResource(),
                201,
                "Successfully updated event {$event->name}"
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Event ID does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Remove the specified event
     * DELETE /api/events/{id}
     */
    public function destroy($id)
    {
        try {
            $this->service->delete($id);
            return $this->success(
                message: 'Succesfully deleted event',
                code: 204, // Change to 200, if want result message
            );
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Register a member to an event
     * POST /api/events/{id}/register
     */
    public function registerMember(Request $request, $eventId)
    {
        $validated = $request->validate(
            [
                'member_id' => 'required|exists:members,id'
            ],
            [
                'member_id.exists' => 'Member is not yet registered to the system.'
            ]
        );

        try {
            $this->service->addMemberToEvent($eventId, $validated['member_id']);
            return $this->success(
                message: 'Member registered successfully',
                code: 201,
            );
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Event ID or Member does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Unregister a member from an event
     * DELETE /api/events/{id}/delete
     */
    public function unregisterMember(Request $request, $eventId)
    {
        $validated = $request->validate(
            [
                'member_id' => 'required|exists:members,id'
            ],
            [
                'member_id.exists' => 'Member is not yet registered to the system.'
            ]
        );

        try {
            $event = $this->service->removeMemberFromEvent($eventId, $validated['member_id']);
            return $this->success(message: 'Member unregistered successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error(message: "Event ID or Member does not exist");
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }

    /**
     * Get all members registered to event
     */
    public function getEventMembers(Request $request, string $eventId)
    {
        $page = $request->input('page');
        $perPage = $request->input('per_page');
        try {
            if ($page === null && $perPage === null) {
                $event = $this->service->getMembers($eventId);
                return $this->success($event);
            }

            return $this->service->getMembers($eventId, $page, $perPage);
        } catch (ModelNotFoundException $e) {
            return $this->error(message: 'Event not found');
        } catch (\Exception $e) {
            return $this->error(
                message: $e->getMessage(),
                code: 500
            );
        }
    }
}
