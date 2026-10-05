@if ($list->isEmpty())
    <div class="empty">{{ $empty }}</div>
@else
    <table>
        <tr><th>Guest</th><th>Company</th><th>Room</th><th>Date</th><th>Status</th></tr>
        @foreach ($list as $b)
            <tr>
                <td>{{ $b->guest_name }}</td>
                <td>{{ $b->company ?: '—' }}</td>
                <td>{{ $b->room->room_no }}</td>
                <td>{{ $b->$date->format('M d, Y') }}</td>
                <td><span class="badge {{ $b->badge() }}">{{ $b->status }}</span></td>
            </tr>
        @endforeach
    </table>
@endif
