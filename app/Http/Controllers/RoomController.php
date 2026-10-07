<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Location;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomController extends Controller
{
    /**
     * Admin dashboard.
     */
    public function admin()
    {
        $rooms = Room::listed();
        $today = date('Y-m-d');

        $statuses = [];

        foreach (Room::STATUSES as $status) {
            $statuses[$status] = $rooms
                ->where('status', $status)
                ->count();
        }

        return view('admin.home', [
            'locations' => Location::with('rooms')
                ->orderBy('name')
                ->get(),

            'rooms' => $rooms,

            'statuses' => $statuses,

            'capacity' => $rooms->sum('capacity'),

            'guestsInHouse' => Booking::whereIn(
                'status',
                ['Checked In', 'Checking Out']
            )->sum('no_of_guests'),

            'checkedInToday' => Booking::whereDate(
                'check_in',
                $today
            )->whereIn(
                'status',
                ['Checked In', 'Checking Out', 'Checked Out']
            )->count(),

            'checkedOutToday' => Booking::whereDate(
                'actual_check_out',
                $today
            )->count(),

            'reserved' => Booking::where(
                'status',
                'Reserved'
            )->count(),

            'idsHeld' => Booking::where(
                'id_surrendered',
                true
            )
                ->where('id_returned', false)
                ->whereIn(
                    'status',
                    ['Checked In', 'Checking Out']
                )
                ->count(),

            'chargesThisMonth' => Booking::where(
                'charges_paid',
                true
            )
                ->whereMonth(
                    'actual_check_out',
                    date('m')
                )
                ->whereYear(
                    'actual_check_out',
                    date('Y')
                )
                ->sum('charges'),

            'recent' => Booking::with('room')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get(),

            'users' => User::orderBy('role')
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Display rooms.
     */
    public function index(Request $request)
    {
        $location = $request->query('location');

        $rooms = Room::listed()
            ->when($location, fn ($rooms) => $rooms->where('location_id', $location));

        // 10 rooms per page, with Previous / Next under the table
        $page = LengthAwarePaginator::resolveCurrentPage();

        return view('rooms.index', [
            'rooms' => new LengthAwarePaginator(
                $rooms->forPage($page, 10)->values(),
                $rooms->count(),
                10,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            ),

            'locations' => Location::orderBy('name')->get(),

            'location' => $location,
        ]);
    }

    /**
     * Show add room form.
     */
    public function create(Request $request)
    {
        if (Location::count() === 0) {
            return redirect('/admin/locations')
                ->with(
                    'error',
                    'Add a location first. Rooms belong to a location.'
                );
        }

        return view('rooms.form', [
            'room' => new Room([
                'location_id' => $request->query('location'),
            ]),

            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new room.
     */
    public function store(Request $request)
    {
        Room::create(
            $this->validated($request)
        );

        return redirect('/rooms')
            ->with('success', 'Room added successfully.');
    }

    /**
     * Show edit room form.
     */
    public function edit(Room $room)
    {
        return view('rooms.form', [
            'room' => $room,
            'locations' => Location::orderBy('name')->get(),
        ]);
    }

    /**
     * Update room.
     */
    public function update(Request $request, Room $room)
    {
        $room->update(
            $this->validated($request)
        );

        return redirect('/rooms')
            ->with('success', 'Room updated successfully.');
    }

    /**
     * Delete room.
     */
    public function destroy(Room $room)
    {
        if ($room->bookings()->count() > 0) {
            return redirect('/rooms')
                ->with(
                    'error',
                    'Cannot delete this room because it has reservations.'
                );
        }

        $room->delete();

        return redirect('/rooms')
            ->with('success', 'Room deleted successfully.');
    }

    /**
     * Change room status from the dashboard.
     *
     * Reception can change:
     * Available
     * Cleaning
     * Maintenance
     *
     * Occupied, Check-out and Inspection are controlled
     * by the check-in/check-out process.
     */
    public function status(Request $request, Room $room)
    {
        $request->validate([
            'status' => 'required|in:Available,Cleaning,Maintenance',
        ]);

        if (
            in_array(
                $room->status,
                ['Occupied', 'Check-out', 'Inspection']
            )
        ) {
            return back()->with(
                'error',
                $room->room_no.
                ' is '.
                $room->status.
                '. Finish the check-out process first.'
            );
        }

        $room->update([
            'status' => $request->status,
        ]);

        return back()->with(
            'success',
            $room->room_no.
            ' is now '.
            $request->status.
            '.'
        );
    }

    /**
     * Validate room form data.
     */
    private function validated(Request $request): array
    {
        return $request->validate(
            [
                'room_no' => [
                    'required',
                    'string',
                    'max:50',
                ],

                'location_id' => [
                    'required',
                    'exists:locations,id',
                ],

                'capacity' => [
                    'required',
                    'integer',
                    'min:1',
                ],

                'rate' => [
                    'nullable',
                    'required_without_all:rate_hourly,rate_daytour',
                    'numeric',
                    'min:0',
                ],

                'rate_hourly' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'rate_daytour' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'status' => [
                    'required',
                    'in:'.implode(',', Room::STATUSES),
                ],

                'inclusions' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ],
            [],
            [
                'location_id' => 'location',
                'room_no' => 'room number',
                'capacity' => 'room capacity',
                'rate' => 'nightly rate',
                'rate_hourly' => 'hourly rate',
                'rate_daytour' => 'daily rate',
                'inclusions' => 'room inclusions',
            ]
        );
    }
}
