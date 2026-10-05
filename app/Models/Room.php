<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    // Room status flow: Occupied -> Check-out -> Inspection -> Cleaning / Maintenance -> Available
    const STATUSES = ['Available', 'Occupied', 'Check-out', 'Inspection', 'Cleaning', 'Maintenance'];

    protected $fillable = [
        'room_no',
        'location_id',
        'capacity',
        'rate',
        'rate_hourly',
        'rate_daytour',
        'status',
        'inclusions',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'rate_hourly' => 'decimal:2',
        'rate_daytour' => 'decimal:2',
    ];

    // Every room query also loads its location
    protected $with = ['location'];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    // Rooms sorted by location name, then room number
    public static function listed($status = null)
    {
        return static::when($status, fn ($q) => $q->where('status', $status))
            ->get()
            ->sortBy(
                fn ($room) => $room->location->name.' '.$room->room_no,
                SORT_NATURAL
            )
            ->values();
    }

    // CSS class for the room card and status badge
    public function css()
    {
        return [
            'Available' => 'free',
            'Occupied' => 'used',
            'Check-out' => 'checkout',
            'Inspection' => 'inspection',
            'Cleaning' => 'clean',
            'Maintenance' => 'maintenance',
        ][$this->status] ?? 'clean';
    }
}
