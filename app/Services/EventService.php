<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Member;

class EventService
{
    public function getAll(array $data)
    {
        return Event::filter($data)->with(['semester', 'members'])->get();
    }

    public function create(array $data)
    {
        return Event::create($data);
    }

    public function find(string $id)
    {
        return Event::findOrFail($id);
    }

    public function update(string $id, array $data)
    {
        $event = Event::findOrFail($id);
        $event->update($data);
        return $event->load(['semester']);
    }

    public function delete(string $id)
    {
        return Event::findOrFail($id)->delete();
    }

    public function addMemberToEvent(string $eventId, string $memberId)
    {
        $event = Event::findOrFail($eventId);
        $member = Member::findOrFail($memberId);

        // Check if registration is open
        if (!$event->is_registration_open) {
            return new \Exception("Registration is not open for this event", 400);
        }

        // Check if member is already registered
        if ($event->members()->where('member_id', $member->id)->exists()) {
            return new \Exception("Member is already registered for this event", 400);
        }

        // Register the member
        $event->members()->attach($member->id, [
            'registered_at' => now()
        ]);

        return $event;
    }

    public function removeMemberFromEvent(string $eventId, string $memberId)
    {
        $event = Event::findOrFail($eventId);
        $member = Member::findOrFail($memberId);

        // Check if member is registered
        if (! $event->members()->where('member_id', $member->id)->exists()) {
            return new \Exception("Member is not registered for this event", 400);
        }

        // Unregister the member
        $event->members()->detach($member->id);

        return $event;
    }

    public function getMembers(string $eventId, $page = null, $perPage = null)
    {
        $event = Event::findOrFail($eventId);

        if ($page !== null || $perPage !== null) {
            return $event->members()
                ->select('first_name', 'last_name', 'year', 'program')
                ->paginate(
                    perPage: $perPage,
                    page: $page
                )->toResourceCollection();
        }

        return $event->members()
            ->select('first_name', 'last_name', 'year', 'program')
            ->get();
    }
}
