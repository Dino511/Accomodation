<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'reception@example.com'],
            ['name' => 'Reception', 'password' => Hash::make('password'), 'role' => 'reception']
        );

        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Administrator', 'password' => Hash::make('password'), 'role' => 'admin']
        );

        if (Room::count() > 0) {
            return;
        }

        $rooms = [
            ['A-101', 'Barracks', 8, 500],
            ['A-102', 'Barracks', 8, 500],
            ['A-103', 'Barracks', 6, 450],
            ['A-104', 'Barracks', 4, 400],
            ['Villa 1', 'Guest Villa', 2, 1500],
            ['Villa 2', 'Guest Villa', 4, 2000],
            ['Villa 3', 'Guest Villa', 2, 1500],
        ];

        foreach ($rooms as $r) {
            Room::create(['room_no' => $r[0], 'location_id' => Location::firstOrCreate(['name' => $r[1]])->id, 'capacity' => $r[2], 'rate' => $r[3]]);
        }

        // sample bookings
        Booking::create([
            'guest_name' => 'Juan Dela Cruz', 'company' => 'Sample Corp', 'contact_no' => '09171234567',
            'room_id' => 2, 'no_of_guests' => 3, 'check_in' => now()->subDay(), 'check_out' => now()->addDays(2),
            'status' => 'Checked In', 'guest_type' => 'Contractor',
            'verified' => true, 'id_surrendered' => true, 'id_type' => 'Company ID', 'id_number' => 'SC-00123',
        ]);
        Room::find(2)->update(['status' => 'Occupied']);

        Booking::create([
            'guest_name' => 'Maria Santos', 'company' => 'ABC Trading', 'contact_no' => '09181234567',
            'room_id' => 5, 'no_of_guests' => 2, 'check_in' => now()->addDays(1), 'check_out' => now()->addDays(3),
            'status' => 'Reserved',
        ]);
    }
}
