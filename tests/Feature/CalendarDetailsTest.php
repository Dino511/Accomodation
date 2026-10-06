<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_calendar_shows_guest_room_status_and_schedule_for_arrivals_and_departures(): void
    {
        $reception = User::factory()->create(['role' => 'reception']);
        $location = Location::create(['name' => 'Guest Villa']);
        $room = Room::create([
            'room_no' => 'V-101',
            'location_id' => $location->id,
            'capacity' => 4,
            'rate' => 1500,
            'status' => 'Available',
        ]);

        Booking::create([
            'guest_name' => 'Alex Arrival',
            'guest_type' => 'Visitor',
            'contact_no' => '555-0110',
            'room_id' => $room->id,
            'no_of_guests' => 2,
            'check_in' => '2026-10-06',
            'check_in_time' => '14:30',
            'check_out' => '2026-10-08',
            'check_out_time' => '11:00',
            'status' => 'Reserved',
        ]);

        Booking::create([
            'guest_name' => 'Sam Departing',
            'guest_type' => 'Contractor',
            'contact_no' => '555-0111',
            'room_id' => $room->id,
            'no_of_guests' => 1,
            'check_in' => '2026-10-02',
            'check_in_time' => '15:00',
            'check_out' => '2026-10-06',
            'check_out_time' => '10:30',
            'status' => 'Checked In',
        ]);

        $this->actingAs($reception)
            ->get('/calendar?month=2026-10')
            ->assertOk()
            ->assertSee('October 2026')
            ->assertSee('Arrivals / reservations')
            ->assertSee('Reservation')
            ->assertSee('Alex Arrival')
            ->assertSee('V-101')
            ->assertSee('02:30 PM')
            ->assertSee('Expected check-out')
            ->assertSee('Sam Departing')
            ->assertSee('10:30 AM')
            ->assertSee('Checked In');
    }
}
