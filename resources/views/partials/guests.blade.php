{{-- Everyone in the group with the room they checked in to. $booking = the stay --}}
<div class="table-wrap">
    <table>
        <tr><th>#</th><th>Name</th><th>Address</th><th>Contact</th><th>Room</th></tr>
        @foreach ($booking->guests() as $i => $guest)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $guest['name'] }}</td>
                <td>{{ $guest['address'] ?: '—' }}</td>
                <td>{{ $guest['contact_no'] ?: '—' }}</td>
                <td>{{ $booking->room->room_no }}</td>
            </tr>
        @endforeach
    </table>
</div>
