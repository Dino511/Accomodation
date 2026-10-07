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
        <div class="stat"><b>{{ $totals['stays'] }}</b><span>Total stays</span></div>
        <div class="stat"><b>{{ $totals['inHouse'] }}</b><span>In house now</span></div>
        <div class="stat"><b>{{ $totals['checkedOut'] }}</b><span>Checked out</span></div>
        <div class="stat"><b>{{ $idsHeld }}</b><span>IDs held at reception</span></div>
    </div>

    <div class="box" id="guest-log">
        <h3>Guest log</h3>
        @if ($bookings->isEmpty())
            <div class="empty">No guests yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>ID</th><th class="amount">Bill total</th><th class="amount">Paid</th><th class="amount">Balance</th><th>Payment</th><th>Status</th><th></th></tr>
                    @foreach ($bookings as $b)
                        @php $checkedOutAt = $b->checkout_verified_at ?? $b->actual_check_out; @endphp
                        <tr>
                            <td>
                                {{ $b->guest_name }}
                                <br><small class="muted">{{ $b->guest_type }}{{ $b->company ? ' · '.$b->company : '' }}</small>
                            </td>
                            <td class="nowrap">{{ $b->room->room_no }}</td>
                            <td class="nowrap">
                                {{ $b->billingStart()->format('M d, Y') }}
                                <br><small class="muted">{{ $b->billingStart()->format('h:i A') }}</small>
                            </td>
                            <td class="nowrap">
                                @if ($checkedOutAt)
                                    {{ $checkedOutAt->format('M d, Y') }}
                                    @if ($b->checkout_verified_at)
                                        <br><small class="muted">{{ $b->checkout_verified_at->format('h:i A') }}</small>
                                    @endif
                                @else
                                    <span class="muted">Still in house</span>
                                @endif
                            </td>
                            <td>
                                @if ($b->id_returned)
                                    <span class="badge free">Returned</span>
                                @elseif ($b->id_surrendered)
                                    <span class="badge checkout">Held</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="amount">
                                <b>₱{{ number_format($b->totalAmount(), 2) }}</b>
                                <br><small class="muted">Room ₱{{ number_format($b->accommodationCharge(), 2) }}{{ $b->additionalChargeTotal() > 0 ? ' + extra ₱'.number_format($b->additionalChargeTotal(), 2) : '' }}</small>
                            </td>
                            <td class="amount">₱{{ number_format((float) $b->amount_paid, 2) }}</td>
                            <td class="amount">{{ $b->balance() > 0 ? '₱'.number_format($b->balance(), 2) : '—' }}</td>
                            <td><span class="badge {{ $b->paymentStatus() == 'Paid' ? 'free' : ($b->paymentStatus() == 'Unpaid' ? 'used' : 'checkout') }}">{{ $b->paymentStatus() }}</span></td>
                            <td><span class="badge {{ $b->badge() }}">{{ $b->status }}</span></td>
                            <td><a class="btn small" href="/billing/{{ $b->id }}">View bill</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>

            {{ $bookings->links('partials.pager') }}
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
