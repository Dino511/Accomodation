@extends('layout')

@section('content')
    <div class="page-head">
        <div>
            <h2>Guest Log</h2>
            <p class="subtitle">Every guest who checked in, with their check-out record.</p>
        </div>
        <a class="btn primary" href="/reports/export">Export to CSV</a>
    </div>

    <div class="stats">
        <div class="stat"><b>{{ $bookings->count() }}</b><span>Total stays</span></div>
        <div class="stat"><b>{{ $bookings->whereIn('status', ['Checked In', 'Checking Out'])->count() }}</b><span>In house now</span></div>
        <div class="stat"><b>{{ $bookings->where('status', 'Checked Out')->count() }}</b><span>Checked out</span></div>
        <div class="stat"><b>{{ $idsHeld }}</b><span>IDs held at reception</span></div>
    </div>

    <div class="box">
        <h3>Guest log</h3>
        @if ($bookings->isEmpty())
            <div class="empty">No guests yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Guest</th><th>Type</th><th>Company</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>ID</th><th>Accommodation</th><th>Additional</th><th>Total</th><th>Paid</th><th>Balance</th><th>Payment</th><th>Status</th><th></th></tr>
                    @foreach ($bookings as $b)
                        <tr>
                            <td>{{ $b->guest_name }}</td>
                            <td>{{ $b->guest_type }}</td>
                            <td>{{ $b->company ?: '—' }}</td>
                            <td>{{ $b->room->room_no }}</td>
                            <td>{{ $b->billingStart()->format('M d, Y h:i A') }}</td>
                            <td>{{ $b->checkout_verified_at ? $b->checkout_verified_at->format('M d, Y h:i A') : ($b->actual_check_out ? $b->actual_check_out->format('M d, Y') : '—') }}</td>
                            <td>{{ $b->id_returned ? 'Returned' : ($b->id_surrendered ? 'Held' : '—') }}</td>
                            <td>₱{{ number_format($b->accommodationCharge(), 2) }}</td>
                            <td>₱{{ number_format($b->additionalChargeTotal(), 2) }}</td>
                            <td>₱{{ number_format($b->totalAmount(), 2) }}</td>
                            <td>₱{{ number_format((float) $b->amount_paid, 2) }}</td>
                            <td>₱{{ number_format($b->balance(), 2) }}</td>
                            <td>{{ $b->paymentStatus() }}</td>
                            <td><span class="badge {{ $b->badge() }}">{{ $b->status }}</span></td>
                            <td><a class="btn small" href="/billing/{{ $b->id }}">View bill</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    <div class="box">
        <h3>Rooms by status</h3>
        <div class="table-wrap">
            <table>
                <tr><th>Status</th><th>Rooms</th><th>Percentage</th></tr>
                @foreach ($utilization as $status => $row)
                    <tr><td>{{ $status }}</td><td>{{ $row[0] }}</td><td>{{ $row[1] }}%</td></tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection
