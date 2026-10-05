<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    // Check-in process (from the company flowchart)
    const CHECKIN_STEPS = [
        1 => 'Guest Arrives',
        2 => 'Reception',
        3 => 'Check-In Form',
        4 => 'Verification',
        5 => 'Surrender Valid ID',
        6 => 'Assign Accommodation',
        7 => 'Room Assignment',
        8 => 'Proceed to Room',
    ];

    // Check-out process (from the company flowchart)
    const CHECKOUT_STEPS = [
        1 => 'Reports to Reception',
        2 => 'Check-Out Verification',
        3 => 'Room Inspection',
        4 => 'Damages / Unpaid Charges',
        5 => 'Settle Charges',
        6 => 'Return Surrendered ID',
        7 => 'Record in Guest Log',
        8 => 'Guest Leaves',
    ];

    protected $fillable = [
        'guest_name', 'guest_type', 'company', 'contact_no', 'email', 'room_id', 'no_of_guests',
        'check_in', 'check_in_time', 'check_out', 'check_out_time', 'status', 'remarks',
        'verified', 'id_type', 'id_number', 'id_surrendered',
        'checkout_step', 'inspection_notes', 'damage_notes', 'charges', 'charges_paid', 'id_returned', 'room_after',
        'actual_check_out', 'checkout_notes', 'guest_list', 'address',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'actual_check_out' => 'date',
        'verified' => 'boolean',
        'id_surrendered' => 'boolean',
        'charges_paid' => 'boolean',
        'id_returned' => 'boolean',
        'checkout_step' => 'integer',
        'guest_list' => 'array',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    // css class for the status badge
    public function badge()
    {
        return [
            'Reserved' => 'reserved',
            'Checked In' => 'checked',
            'Checking Out' => 'checking',
            'Checked Out' => 'out',
            'Cancelled' => 'cancelled',
        ][$this->status] ?? 'out';
    }

    // everyone in the group, the main guest first; each row has name, address and contact_no
    public function guests(): array
    {
        $guests = [['name' => $this->guest_name, 'address' => $this->address, 'contact_no' => $this->contact_no]];

        foreach ($this->guest_list ?? [] as $guest) {
            $guests[] = [
                'name' => $guest['name'] ?? $guest,
                'address' => $guest['address'] ?? '',
                'contact_no' => $guest['contact_no'] ?? '',
            ];
        }

        return $guests;
    }

    // a saved time shown the same way on every page, e.g. timeText('check_out_time') = "12:00 PM"
    public function timeText(string $field): string
    {
        return $this->$field ? Carbon::parse($this->$field)->format('h:i A') : '';
    }
}
