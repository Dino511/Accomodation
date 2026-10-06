@extends('layout')

@section('content')
    <div class="page-head">
        <div>
            <h2>Rooms</h2>
            <p class="subtitle">Every room belongs to a location.</p>
        </div>
        <a class="btn primary" href="/rooms/create{{ $location ? '?location='.$location : '' }}">+ Add room</a>
    </div>

    <div class="box">
        <form class="search-row" method="GET" action="/rooms">
            <select name="location" onchange="this.form.submit()" aria-label="Location">
                <option value="">All locations</option>
                @foreach ($locations as $item)
                    <option value="{{ $item->id }}" {{ $location == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                @endforeach
            </select>
        </form>

        @if ($rooms->isEmpty())
            <div class="empty">No rooms here yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Location</th><th>Room No</th><th>Capacity</th><th>Rates</th><th>Status</th><th>Actions</th></tr>
                    @foreach ($rooms as $room)
                        <tr>
                            <td>{{ $room->location->name }}</td>
                            <td><b>{{ $room->room_no }}</b></td>
                            <td>{{ $room->capacity }}</td>
                            <td>{{ $room->rateSummary() }}</td>
                            <td><span class="badge {{ $room->css() }}">{{ $room->status }}</span></td>
                            <td>
                                <div class="actions">
                                    <a class="btn small" href="/rooms/{{ $room->id }}/edit">Edit</a>
                                    <form method="POST" action="/rooms/{{ $room->id }}" onsubmit="return confirm('Delete {{ addslashes($room->room_no) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="danger small">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
@endsection
