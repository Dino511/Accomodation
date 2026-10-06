@extends('layout')

@section('content')

<div class="page-head"> <div> <h2>Reception Dashboard</h2> <p class="subtitle">{{ date('l, F j, Y') }}</p> </div>
<div class="actions">
    <a class="btn success-btn" href="/checkin">
        + Check in a guest
    </a>

    <a class="btn primary" href="/checkout">
        Check out a guest
    </a>
</div>

</div>
{{-- =====================================================
DASHBOARD STATS
===================================================== --}}

<div class="stats">
<div class="stat">
    <b>{{ $rooms->count() }}</b>
    <span>Total rooms</span>
</div>

<div class="stat">
    <b>{{ $available }}</b>
    <span>Available</span>
</div>

<div class="stat">
    <b>{{ $occupied }}</b>
    <span>Occupied</span>
</div>

<div class="stat">
    <b>{{ $turnover }}</b>
    <span>Check-out / inspection / cleaning</span>
</div>

<div class="stat">
    <b>{{ $arrivals->count() }}</b>
    <span>Guests to arrive</span>
</div>

<div class="stat">
    <b>{{ $departures->count() }}</b>
    <span>Guests due to leave</span>
</div>

</div>
{{-- =====================================================
CHECK-OUT ALERTS
===================================================== --}}

<div class="box checkout-alert-box">
<div class="checkout-alert-header">

    <div class="checkout-alert-heading">
        <h3>Check-out alerts</h3>

        <p class="hint">
            Guests who are approaching or have passed their expected
            check-out time.
        </p>
    </div>

    @if ($checkoutAlerts->isNotEmpty())
        <span class="alert-count">
            {{ $checkoutAlerts->count() }}
            {{ $checkoutAlerts->count() === 1 ? 'guest' : 'guests' }}
        </span>
    @endif

</div>

@if ($checkoutAlerts->isEmpty())

    <div class="empty">
        No guest is currently due or approaching check-out.
    </div>

@else

    <div class="checkout-alert-list">

        @foreach ($checkoutAlerts as $b)

            @php
                $checkoutTime = $b->check_out_time ?: '12:00:00';

                $checkoutDateTime = \Carbon\Carbon::parse(
                    $b->check_out->format('Y-m-d') . ' ' . $checkoutTime
                );

                $now = \Carbon\Carbon::now();

                if ($checkoutDateTime->isPast()) {

                    $alertClass = 'overdue';
                    $alertLabel = 'Check-out overdue';
                    $alertMessage =
                        'This guest has passed the expected check-out time.';

                } else {

                    $alertClass = 'soon';
                    $minutes = $now->diffInMinutes(
                        $checkoutDateTime,
                        false
                    );

                    if ($minutes < 60) {

                        $alertMessage =
                            'Expected check-out is in ' .
                            $minutes .
                            ' minutes.';

                    } else {

                        $hours = floor($minutes / 60);
                        $remainingMinutes = $minutes % 60;

                        if ($remainingMinutes > 0) {

                            $alertMessage =
                                'Expected check-out is in ' .
                                $hours .
                                ' hour' .
                                ($hours == 1 ? '' : 's') .
                                ' and ' .
                                $remainingMinutes .
                                ' minute' .
                                ($remainingMinutes == 1 ? '' : 's') .
                                '.';

                        } else {

                            $alertMessage =
                                'Expected check-out is in ' .
                                $hours .
                                ' hour' .
                                ($hours == 1 ? '' : 's') .
                                '.';
                        }
                    }

                    $alertLabel = 'Check-out approaching';
                }
            @endphp

            <div class="checkout-alert {{ $alertClass }}">

                {{-- Alert icon --}}
                <div class="checkout-alert-icon">
                    !
                </div>

                {{-- Main alert content --}}
                <div class="checkout-alert-content">

                    <div class="checkout-alert-top">

                        <div class="checkout-alert-title">

                            <strong>
                                {{ $alertLabel }}
                            </strong>

                            <p>
                                {{ $alertMessage }}
                            </p>

                        </div>

                        <span class="badge {{ $b->badge() }}">
                            {{ $b->status }}
                        </span>

                    </div>

                    {{-- Guest information --}}
                    <div class="checkout-alert-details">

                        <div class="checkout-detail">

                            <span>Guest</span>

                            <b>
                                {{ $b->guest_name }}
                            </b>

                        </div>

                        <div class="checkout-detail">

                            <span>Room</span>

                            <b>
                                {{ $b->room->room_no }}
                            </b>

                        </div>

                        <div class="checkout-detail">

                            <span>Expected check-out</span>

                            <b>
                                {{ $b->check_out->format('M d, Y') }}
                                at
                                {{ $b->timeText('check_out_time') ?: '12:00 PM' }}
                            </b>

                        </div>

                    </div>

                </div>

                {{-- Action --}}
                <div class="checkout-alert-action">

                    <a
                        class="btn primary small"
                        href="/checkout/{{ $b->id }}"
                    >
                        {{ $b->status === 'Checking Out'
                            ? 'Continue check-out'
                            : 'Check out'
                        }}
                    </a>

                </div>

            </div>

        @endforeach

    </div>

@endif

</div>
{{-- =====================================================
ROOMS
===================================================== --}}

<div class="box">
<h3>Rooms</h3>

<p class="flow" style="margin:0 0 12px">

    Room status flow:

    <span class="badge used">Occupied</span>
    <span class="arrow">→</span>

    <span class="badge checkout">Check-out</span>
    <span class="arrow">→</span>

    <span class="badge inspection">Inspection</span>
    <span class="arrow">→</span>

    <span class="badge clean">
        Cleaning / Maintenance
    </span>

    <span class="arrow">→</span>

    <span class="badge free">Available</span>

</p>

@foreach ($rooms->groupBy('location.name') as $location => $list)

    <p style="margin:12px 0 6px">
        <b>{{ $location }}</b>
    </p>

    <div class="rooms">

        @foreach ($list as $room)

            @php
                $guest = $guests[$room->id] ?? null;
            @endphp

            <button
                type="button"
                class="room {{ $room->css() }}"
                onclick="roomClicked(
                    {{ $room->id }},
                    @js($room->room_no),
                    @js($room->status),
                    {{ $guest ? $guest->id : 'null' }},
                    @js($room->location->name),
                    {{ $room->capacity }},
                    @js($room->rates()),
                    @js($room->inclusionsList()),
                    @js($guest ? $guest->guest_name : null)
                )"
            >

                <div class="room-name">
                    {{ $room->room_no }}
                </div>

                <span class="badge {{ $room->css() }}">
                    {{ $room->status }}
                </span>

                <small>
                    {{ $guest
                        ? $guest->guest_name
                        : 'Good for '.$room->capacity
                    }}
                </small>

                <div class="room-rates" aria-label="Room rates">
                    <span class="room-rates-title">Rates</span>
                    @forelse ($room->rates() as $label => $rate)
                        <span class="room-rate">
                            <span>{{ $label }}</span>
                            <b>₱{{ number_format((float) $rate, 2) }}</b>
                        </span>
                    @empty
                        <span class="room-rate">No rates set</span>
                    @endforelse
                </div>

                @if ($room->inclusionsList())
                    <small class="room-card-inclusions">{{ implode(' · ', $room->inclusionsList()) }}</small>
                @endif

            </button>

        @endforeach

    </div>

@endforeach

</div>
{{-- =====================================================
GUESTS TO ARRIVE
===================================================== --}}

<div class="box">
<h3>Guests to arrive</h3>

@if ($arrivals->isEmpty())

    <div class="empty">
        No reservations to check in today.
    </div>

@else

    <div class="table-wrap">

        <table>

            <tr>
                <th>Guest</th>
                <th>Company</th>
                <th>Room</th>
                <th>Arrival</th>
                <th></th>
            </tr>

            @foreach ($arrivals as $b)

                <tr>

                    <td>
                        {{ $b->guest_name }}
                    </td>

                    <td>
                        {{ $b->company ?: '—' }}
                    </td>

                    <td>
                        {{ $b->room->room_no }}
                    </td>

                    <td>

                        {{ $b->check_in->format('M d, Y') }}

                        @if ($b->check_in_time)

                            <br>

                            <small class="muted">
                                {{ $b->timeText('check_in_time') }}
                            </small>

                        @endif

                    </td>

                    <td>

                        <a
                            class="btn success-btn small"
                            href="/checkin?booking={{ $b->id }}"
                        >
                            Check in
                        </a>

                    </td>

                </tr>

            @endforeach

        </table>

    </div>

@endif

</div>
{{-- =====================================================
GUESTS DUE TO LEAVE
===================================================== --}}

<div class="box">
<h3>Guests due to leave</h3>

@if ($departures->isEmpty())

    <div class="empty">
        No guests are due to check out today.
    </div>

@else

    <div class="table-wrap">

        <table>

            <tr>
                <th>Guest</th>
                <th>Company</th>
                <th>Room</th>
                <th>Check-out</th>
                <th>Status</th>
                <th></th>
            </tr>

            @foreach ($departures as $b)

                <tr>

                    <td>
                        {{ $b->guest_name }}
                    </td>

                    <td>
                        {{ $b->company ?: '—' }}
                    </td>

                    <td>
                        {{ $b->room->room_no }}
                    </td>

                    <td>

                        {{ $b->check_out->format('M d, Y') }}

                        @if ($b->check_out_time)

                            <br>

                            <small class="muted">
                                {{ $b->timeText('check_out_time') }}
                            </small>

                        @endif

                    </td>

                    <td>

                        <span class="badge {{ $b->badge() }}">
                            {{ $b->status }}
                        </span>

                    </td>

                    <td>

                        <a
                            class="btn primary small"
                            href="/checkout/{{ $b->id }}"
                        >
                            {{ $b->status === 'Checking Out'
                                ? 'Continue check-out'
                                : 'Check out'
                            }}
                        </a>

                    </td>

                </tr>

            @endforeach

        </table>

    </div>

@endif

</div>
{{-- =====================================================
ROOMS NEEDING ATTENTION
===================================================== --}}
@php
$attentionRooms = $rooms->whereIn('status', [
'Check-out',
'Inspection',
'Cleaning',
'Maintenance',
]);
@endphp

@if ($attentionRooms->isNotEmpty())

<div class="box">

    <h3>Rooms needing attention</h3>

    <div class="table-wrap">

        <table>

            <tr>
                <th>Room</th>
                <th>Location</th>
                <th>Status</th>
            </tr>

            @foreach ($attentionRooms as $room)

                <tr>
                    <td>{{ $room->room_no }}</td>
                    <td>{{ $room->location->name }}</td>
                    <td>
                        <span class="badge {{ $room->css() }}">
                            {{ $room->status }}
                        </span>
                    </td>
                </tr>

            @endforeach

        </table>

    </div>

    <p class="hint">
        Click a room card above to mark it Available when it is ready.
    </p>

</div>

@endif

{{-- =====================================================
ROOM DETAILS AND STATUS MODAL
===================================================== --}}

<div class="modal" id="roomModal">
    <div class="modal-box room-modal-box">
        <div class="modal-header">
            <div>
                <h3 id="roomTitle">Room</h3>
                <p id="roomLocation" class="muted"></p>
            </div>
            <button class="close" type="button" aria-label="Close" onclick="closeRoomModal()">×</button>
        </div>

        <div class="room-modal-summary">
            <span id="roomStatusBadge" class="badge"></span>
            <span id="roomCapacity"></span>
            <span id="roomGuest"></span>
        </div>

        <div class="room-modal-section">
            <h4>Rates</h4>
            <div id="roomModalRates" class="room-modal-rates"></div>
        </div>

        <div class="room-modal-section">
            <h4>Inclusions</h4>
            <div id="roomModalInclusions" class="room-inclusion-list"></div>
        </div>

        <a id="roomCheckoutLink" class="btn primary" hidden>Open guest check-out</a>

        <form method="POST" id="roomForm">
            @csrf
            <label for="roomStatus">Change status to</label>
            <select name="status" id="roomStatus">
                <option value="Available">Available (ready for guests)</option>
                <option value="Cleaning">Cleaning</option>
                <option value="Maintenance">Maintenance</option>
            </select>
            <p id="roomStatusHint" class="hint"></p>
            <div class="actions">
                <button type="submit" class="primary" id="roomStatusSave">Save status</button>
                <button type="button" onclick="closeRoomModal()">Close</button>
            </div>
        </form>
    </div>
</div>

<script>
    function closeRoomModal() {
        document.getElementById('roomModal').classList.remove('show');
    }

    function roomClicked(id, name, status, bookingId, locationName, capacity, rates, inclusions, guestName) {
        const modal = document.getElementById('roomModal');
        const badge = document.getElementById('roomStatusBadge');
        const rateList = document.getElementById('roomModalRates');
        const inclusionList = document.getElementById('roomModalInclusions');
        const form = document.getElementById('roomForm');
        const statusSelect = document.getElementById('roomStatus');
        const saveButton = document.getElementById('roomStatusSave');
        const statusHint = document.getElementById('roomStatusHint');
        const checkoutLink = document.getElementById('roomCheckoutLink');

        document.getElementById('roomTitle').textContent = name;
        document.getElementById('roomLocation').textContent = locationName;
        document.getElementById('roomCapacity').textContent = 'Good for ' + capacity + ' guests';
        document.getElementById('roomGuest').textContent = guestName ? 'Guest: ' + guestName : '';
        badge.textContent = status;
        badge.className = 'badge ' + ({
            'Available': 'free',
            'Occupied': 'used',
            'Check-out': 'checkout',
            'Inspection': 'inspection',
            'Cleaning': 'clean',
            'Maintenance': 'maintenance'
        }[status] || 'clean');

        rateList.replaceChildren();
        const rateEntries = Object.entries(rates || {});
        if (rateEntries.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'muted';
            empty.textContent = 'No rates set';
            rateList.appendChild(empty);
        } else {
            rateEntries.forEach(function ([label, amount]) {
                const row = document.createElement('div');
                row.className = 'room-modal-rate';
                const rateLabel = document.createElement('span');
                rateLabel.textContent = label;
                const rateAmount = document.createElement('b');
                rateAmount.textContent = '₱' + Number(amount).toLocaleString('en-PH', {minimumFractionDigits: 2});
                row.append(rateLabel, rateAmount);
                rateList.appendChild(row);
            });
        }

        inclusionList.replaceChildren();
        if (!inclusions || inclusions.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'muted';
            empty.textContent = 'No inclusions listed';
            inclusionList.appendChild(empty);
        } else {
            inclusions.forEach(function (item) {
                const tag = document.createElement('span');
                tag.className = 'room-inclusion';
                tag.textContent = item;
                inclusionList.appendChild(tag);
            });
        }

        form.action = '/rooms/' + id + '/status';
        checkoutLink.href = '/checkout/' + bookingId;
        checkoutLink.hidden = !bookingId;
        const statusManagedByCheckout = bookingId || ['Occupied', 'Check-out', 'Inspection'].includes(status);
        if (['Available', 'Cleaning', 'Maintenance'].includes(status)) {
            statusSelect.value = status;
        }
        statusSelect.disabled = statusManagedByCheckout;
        saveButton.hidden = statusManagedByCheckout;
        statusHint.textContent = statusManagedByCheckout
            ? 'Room status is managed by the check-in and check-out process.'
            : 'Update the room status when its condition changes.';

        modal.classList.add('show');
    }

    document.getElementById('roomModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeRoomModal();
        }
    });
</script>
@endsection