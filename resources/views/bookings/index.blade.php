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

                                    @if (in_array($b->status, ['Checked In', 'Checking Out', 'Checked Out']) || ($b->status == 'Reserved' && $b->room->rate !== null))
                                        <a class="btn small" href="/billing/{{ $b->id }}">
                                            {{ in_array($b->status, ['Checked In', 'Checking Out', 'Checked Out']) ? 'View bill' : 'Estimate' }}
                                        </a>
                                    @endif

                                    <button
                                        type="button"
                                        class="small"
                                        onclick="showReservationDetails(this)"
                                        data-reservation="{{ json_encode([
                                            'id' => $b->id,
                                            'guest' => $b->guest_name,
                                            'group' => collect($b->guests())->pluck('name')->implode(', '),
                                            'guestCount' => $b->no_of_guests,
                                            'company' => $b->company ?: '—',
                                            'contact' => $b->contact_no ?: '—',
                                            'email' => $b->email ?: '—',
                                            'room' => $b->room->room_no,
                                            'location' => $b->room->location->name,
                                            'hasNightlyRate' => $b->room->rate !== null,
                                            'checkIn' => $b->check_in->format('l, M d, Y').' · '.$b->timeText('check_in_time'),
                                            'checkOut' => $b->check_out->format('l, M d, Y').' · '.$b->timeText('check_out_time'),
                                            'status' => $b->status,
                                            'notes' => $b->remarks ?: '—',
                                        ]) }}"
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

    <div class="modal" id="reservationDetailsModal" aria-hidden="true">
        <div class="modal-box reservation-details-box" role="dialog" aria-modal="true" aria-labelledby="reservationDetailsTitle">
            <div class="modal-header">
                <div>
                    <h3 id="reservationDetailsTitle">Reservation details</h3>
                    <p id="reservationDetailsNumber" class="muted"></p>
                </div>
                <button class="close" type="button" aria-label="Close" onclick="closeReservationDetails()">×</button>
            </div>
            <dl id="reservationDetailsList" class="details reservation-details-list"></dl>
            <div class="actions">
                <a id="reservationDetailsBill" class="btn primary" hidden>View bill</a>
                <a id="reservationDetailsCheckin" class="btn success-btn" hidden>Check in</a>
                <button type="button" onclick="closeReservationDetails()">Close</button>
            </div>
        </div>
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
                                    data-room-name="{{ $room->room_no }}"
                                    data-location="{{ $room->location->name }}"
                                    data-capacity="{{ $room->capacity }}"
                                    data-rates="{{ json_encode($room->rates()) }}"
                                    data-inclusions="{{ json_encode($room->inclusionsList()) }}"
                                    {{ old('room_id') == $room->id ? 'selected' : '' }}
                                >
                                    {{ $room->room_no }} — {{ $room->location->name }}
                                    ({{ $room->capacity }} pax) — {{ $room->rateSummary() }}
                                </option>
                            @endforeach
                        </select>
                        <div id="reservationRoomDetails" class="selected-room-details" hidden>
                            <h4 id="reservationRoomTitle"></h4>
                            <p id="reservationRoomCapacity"></p>
                            <div id="reservationRoomRates" class="room-modal-rates"></div>
                            <div class="room-modal-section">
                                <h4>Inclusions</h4>
                                <div id="reservationRoomInclusions" class="room-inclusion-list"></div>
                            </div>
                        </div>
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

    <script>
        function closeReservationDetails() {
            const modal = document.getElementById('reservationDetailsModal');
            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
        }

        function showReservationDetails(button) {
            const reservation = JSON.parse(button.dataset.reservation);
            const list = document.getElementById('reservationDetailsList');
            const modal = document.getElementById('reservationDetailsModal');
            const billLink = document.getElementById('reservationDetailsBill');
            const checkinLink = document.getElementById('reservationDetailsCheckin');

            document.getElementById('reservationDetailsNumber').textContent = 'Reservation #' + reservation.id;
            list.replaceChildren();

            [
                ['Guest / group', reservation.group + ' (' + reservation.guestCount + (reservation.guestCount == 1 ? ' guest)' : ' guests)')],
                ['Company', reservation.company],
                ['Contact', reservation.contact],
                ['Email', reservation.email],
                ['Room', reservation.room + ' — ' + reservation.location],
                ['Check-in', reservation.checkIn],
                ['Expected check-out', reservation.checkOut],
                ['Status', reservation.status],
                ['Notes', reservation.notes]
            ].forEach(function ([label, value]) {
                const item = document.createElement('div');
                const term = document.createElement('dt');
                const detail = document.createElement('dd');
                term.textContent = label;
                detail.textContent = value;
                item.append(term, detail);
                list.appendChild(item);
            });

            billLink.href = '/billing/' + reservation.id;
            billLink.hidden = reservation.status === 'Cancelled'
                || (reservation.status === 'Reserved' && !reservation.hasNightlyRate);
            checkinLink.href = '/checkin?booking=' + reservation.id;
            checkinLink.hidden = reservation.status !== 'Reserved';
            modal.classList.add('show');
            modal.setAttribute('aria-hidden', 'false');
        }

        document.getElementById('reservationDetailsModal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeReservationDetails();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeReservationDetails();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const roomSelect = document.getElementById('res_room_id');
            const details = document.getElementById('reservationRoomDetails');
            const title = document.getElementById('reservationRoomTitle');
            const capacity = document.getElementById('reservationRoomCapacity');
            const rates = document.getElementById('reservationRoomRates');
            const inclusions = document.getElementById('reservationRoomInclusions');

            function updateReservationRoomDetails() {
                const option = roomSelect.selectedOptions[0];
                details.hidden = !option || !option.value;
                rates.replaceChildren();
                inclusions.replaceChildren();

                if (details.hidden) {
                    return;
                }

                title.textContent = option.dataset.roomName + ' — ' + option.dataset.location;
                capacity.textContent = 'Capacity: ' + option.dataset.capacity + ' guests';

                const roomRates = JSON.parse(option.dataset.rates || '{}');
                if (Object.keys(roomRates).length === 0) {
                    const emptyRate = document.createElement('span');
                    emptyRate.className = 'muted';
                    emptyRate.textContent = 'No rates set';
                    rates.appendChild(emptyRate);
                } else {
                    Object.entries(roomRates).forEach(function ([label, amount]) {
                        const row = document.createElement('div');
                        row.className = 'room-modal-rate';
                        const rateLabel = document.createElement('span');
                        rateLabel.textContent = label;
                        const rateAmount = document.createElement('b');
                        rateAmount.textContent = '₱' + Number(amount).toFixed(2);
                        row.append(rateLabel, rateAmount);
                        rates.appendChild(row);
                    });
                }

                const roomInclusions = JSON.parse(option.dataset.inclusions || '[]');
                if (roomInclusions.length === 0) {
                    const emptyInclusions = document.createElement('span');
                    emptyInclusions.className = 'muted';
                    emptyInclusions.textContent = 'No inclusions listed';
                    inclusions.appendChild(emptyInclusions);
                } else {
                    roomInclusions.forEach(function (item) {
                        const tag = document.createElement('span');
                        tag.className = 'room-inclusion';
                        tag.textContent = item;
                        inclusions.appendChild(tag);
                    });
                }
            }

            roomSelect.addEventListener('change', updateReservationRoomDetails);
            updateReservationRoomDetails();
        });
    </script>
@endsection
