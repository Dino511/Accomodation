@extends('layout')

@php
    // the step reception is doing now (1 to 8, 9 = all done)
    $done = $booking->checkout_step;
    $current = $booking->status == 'Checked Out' ? 9 : max(2, $done + 1);

    // box style for a step: finished, doing now, or not yet
    $state = fn ($from, $to = null) => $current > ($to ?? $from) ? 'done' : ($current >= $from ? '' : 'locked');
@endphp

@section('content')
    <div class="page-head">
        <div>
            <h2>Check-out: {{ $booking->guest_name }}</h2>
            <p class="subtitle">Follow the steps in order.</p>
        </div>
        <a class="btn" href="/checkout">Back to list</a>
    </div>

    @include('partials.steps', ['steps' => App\Models\Booking::CHECKOUT_STEPS, 'current' => $current])

    <div class="box">
        <dl class="details">
            <div><dt>Guest</dt><dd>{{ $booking->guest_name }}</dd></div>
            <div><dt>Company</dt><dd>{{ $booking->company ?: '—' }}</dd></div>
            <div><dt>Room</dt><dd>{{ $booking->room->room_no }} ({{ $booking->room->location->name }})</dd></div>
            <div><dt>Room status</dt><dd><span class="badge {{ $booking->room->css() }}">{{ $booking->room->status }}</span></dd></div>
            <div><dt>Checked in</dt><dd>{{ $booking->check_in->format('M d, Y') }}</dd></div>
            <div><dt>ID held</dt><dd>{{ $booking->id_type ?: '—' }} {{ $booking->id_number }}</dd></div>
        </dl>
    </div>

    <div class="box">
        <h3>Guests in this room</h3>
        @include('partials.guests')
    </div>

    {{-- STEP 2 --}}
    <div class="box step {{ $state(2) }}">
        <div class="step-title"><span class="num">{{ $current > 2 ? '✓' : 2 }}</span><h3>Check-out verification</h3></div>
        @if ($current == 2)
            <form method="POST" action="/checkout/{{ $booking->id }}/verify">
                @csrf
                <p style="margin-top:0">Confirm that this is <b>{{ $booking->guest_name }}</b>, staying in <b>{{ $booking->room->room_no }}</b>, and they are checking out now.</p>
                <button type="submit" class="primary">Confirm and continue</button>
                <p class="hint">The room status changes to Check-out.</p>
            </form>
        @else
            <p class="muted" style="margin:0">Guest and room verified.</p>
        @endif
    </div>

    {{-- STEP 3 and 4 --}}
    <div class="box step {{ $state(3, 4) }}">
        <div class="step-title"><span class="num">{{ $current > 4 ? '✓' : '3' }}</span><h3>Room inspection, damages and unpaid charges</h3></div>
        @if ($current == 3)
            <form method="POST" action="/checkout/{{ $booking->id }}/inspect">
                @csrf
                <div class="form-grid">
                    <div class="full">
                        <label for="inspection_notes">Step 3: Inspection notes</label>
                        <input id="inspection_notes" name="inspection_notes" value="{{ old('inspection_notes') }}" placeholder="e.g. Room in good condition, key returned">
                    </div>
                    <div>
                        <label for="damage_notes">Step 4: Damages or unpaid charges</label>
                        <input id="damage_notes" name="damage_notes" value="{{ old('damage_notes') }}" placeholder="Leave empty if none">
                    </div>
                    <div>
                        <label for="charges">Amount to pay (₱) <span class="req">*</span></label>
                        <input id="charges" name="charges" type="number" min="0" step="0.01" value="{{ old('charges', 0) }}" required>
                        <p class="hint">Enter 0 if there is nothing to pay.</p>
                    </div>
                    <div>
                        <label for="room_after">After check-out, the room needs <span class="req">*</span></label>
                        <select id="room_after" name="room_after">
                            <option value="Cleaning">Cleaning</option>
                            <option value="Maintenance">Maintenance (repair needed)</option>
                        </select>
                    </div>
                </div>
                <br>
                <button type="submit" class="primary">Save inspection</button>
                <p class="hint">The room status changes to Inspection.</p>
            </form>
        @elseif ($current > 4)
            <dl class="details">
                <div><dt>Inspection</dt><dd>{{ $booking->inspection_notes ?: 'No notes' }}</dd></div>
                <div><dt>Damages / unpaid</dt><dd>{{ $booking->damage_notes ?: 'None' }}</dd></div>
                <div><dt>Amount</dt><dd>₱{{ number_format($booking->charges, 2) }}</dd></div>
                <div><dt>Room needs</dt><dd>{{ $booking->room_after }}</dd></div>
            </dl>
        @else
            <p class="muted" style="margin:0">Waiting for the verification.</p>
        @endif
    </div>

    {{-- STEP 5 --}}
    <div class="box step {{ $state(5) }}">
        <div class="step-title"><span class="num">{{ $current > 5 ? '✓' : 5 }}</span><h3>Settle charges (if any)</h3></div>
        @if ($current == 5)
            <p style="margin-top:0">Amount to collect: <b>₱{{ number_format($booking->charges, 2) }}</b> ({{ $booking->damage_notes ?: 'charges' }})</p>
            <form method="POST" action="/checkout/{{ $booking->id }}/settle" onsubmit="return confirm('Mark ₱{{ number_format($booking->charges, 2) }} as paid?')">
                @csrf
                <button type="submit" class="success-btn">Mark as paid</button>
            </form>
        @elseif ($current > 5)
            <p class="muted" style="margin:0">{{ $booking->charges > 0 ? '₱'.number_format($booking->charges, 2).' paid.' : 'No charges to settle.' }}</p>
        @else
            <p class="muted" style="margin:0">Waiting for the inspection.</p>
        @endif
    </div>

    {{-- STEP 6 --}}
    <div class="box step {{ $state(6) }}">
        <div class="step-title"><span class="num">{{ $current > 6 ? '✓' : 6 }}</span><h3>Return surrendered ID</h3></div>
        @if ($current == 6)
            <p style="margin-top:0">Return to the guest: <b>{{ $booking->id_type ?: 'ID' }} {{ $booking->id_number }}</b></p>
            <form method="POST" action="/checkout/{{ $booking->id }}/return-id">
                @csrf
                <button type="submit" class="primary">ID returned to guest</button>
            </form>
        @elseif ($current > 6)
            <p class="muted" style="margin:0">ID returned to the guest.</p>
        @else
            <p class="muted" style="margin:0">The ID is returned after the charges are settled.</p>
        @endif
    </div>

    {{-- STEP 7 and 8 --}}
    <div class="box step {{ $state(7, 8) }}">
        <div class="step-title"><span class="num">{{ $current > 8 ? '✓' : 7 }}</span><h3>Record check-out in guest log</h3></div>
        @if ($current == 7)
            <form method="POST" action="/checkout/{{ $booking->id }}/record" onsubmit="return confirm('Record the check-out of {{ addslashes($booking->guest_name) }}?')">
                @csrf
                <label for="checkout_notes">Final notes</label>
                <input id="checkout_notes" name="checkout_notes" placeholder="Optional">
                <br><br>
                <button type="submit" class="success-btn big">Record check-out</button>
                <p class="hint">The guest may then leave the facility (step 8). The room goes to {{ $booking->room_after }}.</p>
            </form>
        @elseif ($current > 8)
            <p style="margin:0">
                <b>Checked out on {{ $booking->actual_check_out->format('M d, Y') }}.</b> The guest has left the facility.
                {{ $booking->checkout_notes }}
            </p>
            <div class="actions" style="margin-top:12px">
                <a class="btn" href="/reports">Open guest log</a>
                <a class="btn" href="/dashboard">Back to dashboard</a>
            </div>
        @else
            <p class="muted" style="margin:0">Last step, after the ID is returned.</p>
        @endif
    </div>
@endsection
