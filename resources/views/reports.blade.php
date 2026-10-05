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
                    <tr><th>Guest</th><th>Type</th><th>Company</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>ID</th><th>Charges</th><th>Status</th></tr>
                    @foreach ($bookings as $b)
                        <tr>
                            <td>{{ $b->guest_name }}</td>
                            <td>{{ $b->guest_type }}</td>
                            <td>{{ $b->company ?: '—' }}</td>
                            <td>{{ $b->room->room_no }}</td>
                            <td>{{ $b->check_in->format('M d, Y') }}</td>
                            <td>{{ $b->actual_check_out ? $b->actual_check_out->format('M d, Y') : '—' }}</td>
                            <td>{{ $b->id_returned ? 'Returned' : ($b->id_surrendered ? 'Held' : '—') }}</td>
                            <td>{{ $b->charges > 0 ? '₱'.number_format($b->charges, 2) : '—' }}</td>
                            <td><span class="badge {{ $b->badge() }}">{{ $b->status }}</span></td>
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
