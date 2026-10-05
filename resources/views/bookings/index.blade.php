@extends('layout')

@section('content')
    <div class="page-head">
        <div>
            <h2>Reservations</h2>
            <p class="subtitle">Advance bookings. A reserved guest is checked in from the Check-in page when they arrive.</p>
        </div>
        <button type="button" class="primary" onclick="document.getElementById('reservationModal').classList.add('show')">+ New reservation</button>
    </div>

    <div class="box">
        <form class="search-row" method="GET" action="/bookings">
            <input name="search" value="{{ $search }}" placeholder="Search guest, company or room...">

            <select name="status" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (['Reserved', 'Checked In', 'Checking Out', 'Checked Out', 'Cancelled'] as $s)
                    <option value="{{ $s }}" {{ $status == $s ? 'selected' : '' }}>
                        {{ $s }}
                    </option>
                @endforeach
            </select>

            <button type="submit">Search</button>
        </form>

        @if ($bookings->isEmpty())
            <div class="empty">No reservations found.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>#</th>
                        <th>Guest</th>
                        <th>Company</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>

                    @foreach ($bookings as $i => $b)
                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td>
                                <strong>{{ $b->guest_name }}</strong><br>
                                <small class="muted">{{ $b->contact_no }}</small>
                            </td>

                            <td>{{ $b->company ?: '—' }}</td>

                            <td>{{ $b->room->room_no }}</td>

                            <td>
                                {{ $b->check_in->format('M d, Y') }}<br>
                                <small class="muted">{{ $b->timeText('check_in_time') }}</small>
                            </td>

                            <td>
                                {{ $b->check_out->format('M d, Y') }}<br>
                                <small class="muted">{{ $b->timeText('check_out_time') }}</small>
                            </td>

                            <td>
                                <span class="badge {{ $b->badge() }}">
                                    {{ $b->status }}
                                </span>
                            </td>

                            <td>
                                <div class="actions">

                                    @if ($b->status == 'Reserved')
                                        <a
                                            class="btn success-btn small"
                                            href="/checkin?booking={{ $b->id }}"
                                        >
                                            Check in
                                        </a>

                                        <form
                                            method="POST"
                                            action="/bookings/{{ $b->id }}/cancel"
                                            onsubmit="return confirm('Cancel reservation for {{ addslashes($b->guest_name) }}?')"
                                        >
                                            @csrf
                                            <button type="submit" class="danger small">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    @if (in_array($b->status, ['Checked In', 'Checking Out']))
                                        <a
                                            class="btn primary small"
                                            href="/checkout/{{ $b->id }}"
                                        >
                                            Check out
                                        </a>
                                    @endif

                                    <button
                                        type="button"
                                        class="small"
                                        onclick="alert(this.dataset.info)"
                                        data-info="Reservation #{{ $b->id }}&#10;&#10;Guest: {{ $b->guest_name }}&#10;Company: {{ $b->company ?: '—' }}&#10;Contact: {{ $b->contact_no ?: '—' }}&#10;Email: {{ $b->email ?: '—' }}&#10;Room: {{ $b->room->room_no }}&#10;Guests: {{ $b->no_of_guests }}{{ $b->guest_list ? ' (with '.implode(', ', array_column(array_slice($b->guests(), 1), 'name')).')' : '' }}&#10;Check-in: {{ $b->check_in->format('M d, Y') }} {{ $b->timeText('check_in_time') }}&#10;Check-out: {{ $b->check_out->format('M d, Y') }} {{ $b->timeText('check_out_time') }}&#10;Status: {{ $b->status }}&#10;Notes: {{ $b->remarks ?: '—' }}"
                                    >
                                        View
                                    </button>

                                    @if (! in_array($b->status, ['Checked In', 'Checking Out']))
                                        <form
                                            method="POST"
                                            action="/bookings/{{ $b->id }}"
                                            onsubmit="return confirm('Delete reservation for {{ addslashes($b->guest_name) }}?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="danger small">
                                                Delete
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    {{-- New reservation --}}
    <div
        class="modal {{ old('guest_name') !== null ? 'show' : '' }}"
        id="reservationModal"
    >
        <div class="modal-box">

            <div class="modal-header">
                <h3>New reservation</h3>

                <button
                    class="close"
                    type="button"
                    onclick="document.getElementById('reservationModal').classList.remove('show')"
                >
                    ×
                </button>
            </div>

            <form method="POST" action="/bookings">
                @csrf

                <div class="form-grid">

                    <div>
                        <label for="res_guest_name">Guest name <span class="req">*</span></label>
                        <input
                            id="res_guest_name"
                            name="guest_name"
                            value="{{ old('guest_name') }}"
                            placeholder="Full name"
                            required
                        >
                    </div>

                    <div>
                        <label>Company</label>
                        <input
                            name="company"
                            value="{{ old('company') }}"
                            placeholder="Company name"
                        >
                    </div>

                    <div>
                        <label>Contact number</label>
                        <input
                            name="contact_no"
                            value="{{ old('contact_no') }}"
                            placeholder="09xx xxx xxxx"
                        >
                    </div>

                    <div>
                        <label>Email</label>
                        <input
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                        >
                    </div>

                    <div>
                        <label for="res_room_id">Room <span class="req">*</span></label>

                        <select id="res_room_id" name="room_id" required>
                            <option value="">Select room</option>

                            @foreach ($rooms as $room)
                                <option
                                    value="{{ $room->id }}"
                                    {{ old('room_id') == $room->id ? 'selected' : '' }}
                                >
                                    {{ $room->room_no }} — {{ $room->location->name }}
                                    ({{ $room->capacity }} pax)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label>Number of guests</label>

                        <input
                            name="no_of_guests"
                            type="number"
                            min="1"
                            value="{{ old('no_of_guests', 1) }}"
                        >
                    </div>

                    <div>
                        <label for="res_check_in">Check-in <span class="req">*</span></label>

                        <input
                            id="res_check_in"
                            name="check_in_datetime"
                            type="datetime-local"
                            value="{{ old('check_in_datetime', date('Y-m-d\TH:i')) }}"
                            required
                        >
                    </div>

                    <div>
                        <label for="res_check_out">Expected check-out <span class="req">*</span></label>

                        <input
                            id="res_check_out"
                            name="check_out_datetime"
                            type="datetime-local"
                            value="{{ old('check_out_datetime', date('Y-m-d\T12:00', strtotime('+1 day'))) }}"
                            required
                        >
                    </div>

                    <div class="full">
                        <label>Notes</label>

                        <textarea name="remarks">{{ old('remarks') }}</textarea>
                    </div>

                </div>

                <br>

                <div class="actions">
                    <button type="submit" class="primary">
                        Save reservation
                    </button>

                    <button
                        type="button"
                        onclick="document.getElementById('reservationModal').classList.remove('show')"
                    >
                        Cancel
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection
