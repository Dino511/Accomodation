<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReceptionController extends Controller
{
    // ================= CHECK-IN PROCESS =================
    // 1 Guest arrives -> 2 Reception -> 3 Check-in form -> 4 Verification
    // -> 5 Surrender valid ID -> 6 Assign accommodation -> 7 Room assignment -> 8 Proceed to room

    public function checkinForm(Request $request)
    {
        $selected = Booking::where('status', 'Reserved')->find($request->query('booking'));

        return view('checkin', [
            'reservations' => Booking::with('room')
                ->where('status', 'Reserved')
                ->orderBy('check_in')
                ->get(),

            'rooms' => Room::listed('Available'),

            'selected' => $selected,

            'current' => Booking::with('room')
                ->where('status', 'Checked In')
                ->orderBy('check_out')
                ->get(),
        ]);
    }

    public function checkin(Request $request)
    {
        $data = $request->validate([
            'booking_id' => 'nullable|exists:bookings,id',

            // Step 3: check-in form
            'guest_name' => 'required|max:150',
            'guest_type' => 'required|in:Visitor,Contractor',
            'company' => 'nullable|max:150',
            'contact_no' => 'required|max:50',
            'address' => 'required|max:255',
            'no_of_guests' => 'required|integer|min:1',

            // Names of the other guests when the group is 2 or more
            'guest_list' => 'nullable|array',
            'guest_list.*.name' => 'required|max:150',
            'guest_list.*.address' => 'required|max:255',
            'guest_list.*.contact_no' => 'required|max:50',

            // Combined expected check-out date + time
            'check_out_datetime' => 'required|date|after:now',

            'remarks' => 'nullable|max:255',

            // Step 4: verification
            'verified' => 'accepted',

            // Step 5: surrender valid ID
            'id_type' => 'required|max:50',
            'id_number' => 'required|max:50',
            'id_surrendered' => 'accepted',

            // Step 6: assign accommodation
            'room_id' => 'required|exists:rooms,id',
        ], [
            'verified.accepted' => 'Step 4: Please verify the guest first.',
            'id_surrendered.accepted' => 'Step 5: The guest must surrender a valid ID.',
            'id_type.required' => 'Step 5: Select the type of ID.',
            'id_number.required' => 'Step 5: Enter the ID number.',
            'room_id.required' => 'Step 6: Assign a room or barracks.',
            'check_out_datetime.required' => 'Step 3: Enter the expected check-out date and time.',
            'check_out_datetime.after' => 'Step 3: Expected check-out must be in the future.',
            'guest_list.*.*.required' => 'Step 3: Enter the name, address and contact number of every guest in the group.',
        ], [
            'guest_name' => 'guest name',
            'contact_no' => 'contact number',
        ]);

        // Convert the combined expected check-out date/time
        // into the existing database fields.
        $checkOut = Carbon::parse($data['check_out_datetime']);

        $data['check_out'] = $checkOut->format('Y-m-d');
        $data['check_out_time'] = $checkOut->format('H:i');

        unset($data['check_out_datetime']);

        // A group of 2 or more needs one name for every other guest.
        $data['guest_list'] = array_values($data['guest_list'] ?? []);

        if (count($data['guest_list']) != $data['no_of_guests'] - 1) {
            return back()
                ->with('error', 'Step 3: Enter the name, address and contact number of every guest in the group.')
                ->withInput();
        }

        $room = Room::find($data['room_id']);

        if ($room->status != 'Available') {
            return back()
                ->with('error', $room->room_no.' is not available ('.$room->status.').')
                ->withInput();
        }

        if ($data['no_of_guests'] > $room->capacity) {
            return back()
                ->with('error', 'Too many guests. '.$room->room_no.' is good for '.$room->capacity.' only.')
                ->withInput();
        }

        $bookingId = $data['booking_id'] ?? null;

        unset($data['booking_id']);

        $data['verified'] = true;
        $data['id_surrendered'] = true;
        $data['status'] = 'Checked In';

        if ($bookingId) {
            // Existing reservation.
            // Keep its original scheduled check-in date/time.
            $booking = Booking::find($bookingId);

            $booking->update($data);
        } else {
            // Walk-in guest.
            // Actual check-in date and time are recorded automatically
            // when the receptionist completes the check-in.
            $data['check_in'] = date('Y-m-d');
            $data['check_in_time'] = date('H:i');

            $booking = Booking::create($data);
        }

        $room->update(['status' => 'Occupied']);

        // Step 7: show the room assignment slip.
        return redirect('/checkin/'.$booking->id.'/slip')
            ->with('success', 'Check-in complete.');
    }

    // ================= ROOM ASSIGNMENT =================

    // Step 7 and 8: room / barracks assignment slip
    public function slip(Booking $booking)
    {
        return view('slip', [
            'booking' => $booking->load('room'),
        ]);
    }

    // ================= CHECK-OUT PROCESS =================
    // 1 Reports to reception -> 2 Check-out verification -> 3 Room inspection
    // -> 4 Damages / unpaid charges -> 5 Settle charges -> 6 Return ID
    // -> 7 Record check-out in guest log -> 8 Guest leaves

    public function checkoutList()
    {
        return view('checkout', [
            'current' => Booking::with('room')
                ->whereIn('status', ['Checked In', 'Checking Out'])
                ->orderBy('check_out')
                ->get(),

            'history' => Booking::with('room')
                ->where('status', 'Checked Out')
                ->orderByDesc('actual_check_out')
                ->limit(10)
                ->get(),
        ]);
    }

    public function checkoutProcess(Booking $booking)
    {
        if (! in_array($booking->status, ['Checked In', 'Checking Out', 'Checked Out'])) {
            return redirect('/checkout')
                ->with('error', 'This guest is not checked in.');
        }

        return view('checkout-process', [
            'booking' => $booking->load('room'),
        ]);
    }

    // Step 2: check-out verification
    // Room goes to "Check-out"
    public function verifyCheckout(Request $request, Booking $booking)
    {
        if ($booking->status != 'Checked In') {
            return back()->with('error', 'This guest is not checked in.');
        }

        $booking->update([
            'status' => 'Checking Out',
            'checkout_step' => 2,
        ]);

        $booking->room->update([
            'status' => 'Check-out',
        ]);

        return back()->with('success', 'Verified. Next: inspect the room.');
    }

    // Step 3 and 4: room inspection, damages and unpaid charges
    // Room goes to "Inspection"
    public function inspect(Request $request, Booking $booking)
    {
        if ($booking->status != 'Checking Out' || $booking->checkout_step != 2) {
            return back()->with('error', 'Do the check-out verification first.');
        }

        $data = $request->validate([
            'inspection_notes' => 'nullable|max:255',
            'damage_notes' => 'nullable|max:255',
            'charges' => 'required|numeric|min:0',
            'room_after' => 'required|in:Cleaning,Maintenance',
        ]);

        $data['checkout_step'] = 4;

        // Nothing to pay, so step 5 is already done.
        if ($data['charges'] == 0) {
            $data['charges_paid'] = true;
            $data['checkout_step'] = 5;
        }

        $booking->update($data);

        $booking->room->update([
            'status' => 'Inspection',
        ]);

        return back()->with('success', 'Inspection saved.');
    }

    // Step 5: settle charges
    public function settle(Booking $booking)
    {
        if ($booking->checkout_step != 4) {
            return back()->with('error', 'Nothing to settle at this step.');
        }

        $booking->update([
            'charges_paid' => true,
            'checkout_step' => 5,
        ]);

        return back()->with('success', 'Charges settled. Next: return the ID.');
    }

    // Step 6: return the surrendered ID
    public function returnId(Booking $booking)
    {
        if ($booking->checkout_step != 5) {
            return back()->with('error', 'Settle the charges first.');
        }

        $booking->update([
            'id_returned' => true,
            'checkout_step' => 6,
        ]);

        return back()->with('success', 'ID returned. Next: record the check-out.');
    }

    // Step 7 and 8: record check-out in the guest log
    // Room goes to Cleaning / Maintenance
    public function recordCheckout(Request $request, Booking $booking)
    {
        if ($booking->checkout_step != 6) {
            return back()->with('error', 'Return the ID first.');
        }

        $booking->update([
            'status' => 'Checked Out',
            'checkout_step' => 8,
            'actual_check_out' => date('Y-m-d'),
            'checkout_notes' => $request->input('checkout_notes'),
        ]);

        $booking->room->update([
            'status' => $booking->room_after ?: 'Cleaning',
        ]);

        return back()->with(
            'success',
            'Check-out recorded in the guest log. The guest may leave.'
        );
    }

    // ================= CALENDAR =================

    public function calendar(Request $request)
    {
        $month = preg_match(
            '/^\d{4}-\d{2}$/',
            (string) $request->query('month')
        )
            ? $request->query('month')
            : date('Y-m');

        $first = Carbon::parse($month.'-01');
        $last = $first->copy()->endOfMonth();

        $bookings = Booking::where('status', '!=', 'Cancelled')
            ->where(function ($q) use ($first, $last) {
                $q->whereBetween(
                    'check_in',
                    [$first->toDateString(), $last->toDateString()]
                )
                    ->orWhereBetween(
                        'check_out',
                        [$first->toDateString(), $last->toDateString()]
                    );
            })
            ->get();

        // Events per day: "IN: name" / "OUT: name"
        $events = [];

        foreach ($bookings as $b) {
            $events[$b->check_in->toDateString()][] =
                'IN: '.$b->guest_name;

            $events[$b->check_out->toDateString()][] =
                'OUT: '.$b->guest_name;
        }

        return view('calendar', [
            'first' => $first,
            'events' => $events,
            'prev' => $first->copy()->subMonth()->format('Y-m'),
            'next' => $first->copy()->addMonth()->format('Y-m'),
        ]);
    }

    // ================= GUEST LOG / REPORTS =================

    public function reports()
    {
        $rooms = Room::all();

        $bookings = Booking::with('room')
            ->where('status', '!=', 'Reserved')
            ->where('status', '!=', 'Cancelled')
            ->orderByDesc('check_in')
            ->get();

        $utilization = [];

        foreach (Room::STATUSES as $status) {
            $count = $rooms->where('status', $status)->count();

            $utilization[$status] = [
                $count,
                $rooms->count()
                    ? round($count / $rooms->count() * 100)
                    : 0,
            ];
        }

        return view('reports', [
            'bookings' => $bookings,
            'utilization' => $utilization,

            'idsHeld' => Booking::where('id_surrendered', true)
                ->where('id_returned', false)
                ->whereIn('status', ['Checked In', 'Checking Out'])
                ->count(),
        ]);
    }

    public function export()
    {
        $bookings = Booking::with('room')
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($bookings) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'ID',
                'Guest',
                'Type',
                'Company',
                'Contact',
                'Room',
                'Guests',
                'Check-in',
                'Expected Check-out',
                'Actual Check-out',
                'ID Type',
                'ID Number',
                'ID Returned',
                'Charges',
                'Status',
            ]);

            foreach ($bookings as $b) {
                fputcsv($out, [
                    $b->id,
                    $b->guest_name,
                    $b->guest_type,
                    $b->company,
                    $b->contact_no,
                    $b->room->room_no,
                    $b->no_of_guests,
                    $b->check_in->toDateString().' '.$b->check_in_time,
                    $b->check_out->toDateString().' '.$b->check_out_time,
                    optional($b->actual_check_out)->toDateString(),
                    $b->id_type,
                    $b->id_number,
                    $b->id_returned ? 'Yes' : 'No',
                    $b->charges,
                    $b->status,
                ]);
            }

            fclose($out);
        }, 'guest-log-'.date('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
