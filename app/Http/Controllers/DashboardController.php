<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $rooms = Room::listed();

        $today = Carbon::today();
        $now = Carbon::now();

        // Who is in each room now
        $guests = Booking::whereIn('status', [
            'Checked In',
            'Checking Out',
        ])
            ->get()
            ->keyBy('room_id');

        // Reservations arriving today or earlier and not yet checked in
        $arrivals = Booking::with('room')
            ->where('status', 'Reserved')
            ->whereDate('check_in', '<=', $today)
            ->orderBy('check_in')
            ->get();

        // Guests whose expected check-out is today or overdue
        $departures = Booking::with('room')
            ->whereIn('status', [
                'Checked In',
                'Checking Out',
            ])
            ->whereDate('check_out', '<=', $today)
            ->orderBy('check_out')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | CHECK-OUT ALERTS
        |--------------------------------------------------------------------------
        */

        $checkoutAlerts = Booking::with('room')
            ->whereIn('status', [
                'Checked In',
                'Checking Out',
            ])
            ->whereNotNull('check_out')
            ->where(function ($query) use ($today, $now) {

                // Already overdue from a previous date
                $query->whereDate('check_out', '<', $today)

                    // Due today and already at/past checkout time
                    ->orWhere(function ($q) use ($today, $now) {
                        $q->whereDate('check_out', $today)
                            ->whereNotNull('check_out_time')
                            ->whereTime(
                                'check_out_time',
                                '<=',
                                $now->format('H:i:s')
                            );
                    })

                    // Due today within the next 2 hours
                    ->orWhere(function ($q) use ($today, $now) {
                        $q->whereDate('check_out', $today)
                            ->whereNotNull('check_out_time')
                            ->whereTime(
                                'check_out_time',
                                '>',
                                $now->format('H:i:s')
                            )
                            ->whereTime(
                                'check_out_time',
                                '<=',
                                $now->copy()
                                    ->addHours(2)
                                    ->format('H:i:s')
                            );
                    });
            })
            ->orderBy('check_out')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | PREPARE ALERT DISPLAY DATA
        |--------------------------------------------------------------------------
        */

        foreach ($checkoutAlerts as $booking) {

            // Build the combined checkout date + time.
            $checkoutTime = $booking->check_out_time ?: '12:00:00';

            $booking->checkout_at = Carbon::parse(
                $booking->check_out->format('Y-m-d').' '.$checkoutTime
            );

            // Determine alert type.
            if ($booking->checkout_at->lt($now)) {

                $booking->checkout_alert = 'overdue';

                $booking->checkout_alert_label = 'Check-out overdue';

                $booking->checkout_alert_message =
                    'This guest has passed the expected check-out time.';

            } else {

                $minutesUntilCheckout = $now->diffInMinutes(
                    $booking->checkout_at,
                    false
                );

                if ($minutesUntilCheckout <= 120) {

                    $booking->checkout_alert = 'soon';

                    $booking->checkout_alert_label =
                        'Check-out approaching';

                    $booking->checkout_alert_message =
                        'This guest is approaching the expected check-out time.';

                } else {

                    $booking->checkout_alert = 'upcoming';

                    $booking->checkout_alert_label =
                        'Check-out upcoming';

                    $booking->checkout_alert_message =
                        'This guest is scheduled to check out soon.';
                }
            }
        }

        return view('dashboard', [
            'rooms' => $rooms,

            'guests' => $guests,

            'occupied' => $rooms
                ->where('status', 'Occupied')
                ->count(),

            'available' => $rooms
                ->where('status', 'Available')
                ->count(),

            'turnover' => $rooms
                ->whereIn('status', [
                    'Check-out',
                    'Inspection',
                    'Cleaning',
                    'Maintenance',
                ])
                ->count(),

            'arrivals' => $arrivals,

            'departures' => $departures,

            'checkoutAlerts' => $checkoutAlerts,
        ]);
    }
}
