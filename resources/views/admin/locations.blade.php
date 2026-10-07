@extends('layout')

@section('content')
    <h2>Locations</h2>
    <p class="subtitle">A location is a building or area. Each location has its own rooms.</p>

    <form class="box" method="POST" action="{{ $editing ? '/admin/locations/'.$editing->id : '/admin/locations' }}">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <h3>{{ $editing ? 'Edit location' : 'Add a location' }}</h3>
        <div class="form-grid">
            <div>
                <label for="name">Location name <span class="req">*</span></label>
                <input id="name" name="name" value="{{ old('name', optional($editing)->name) }}" placeholder="e.g. Guest Villa" required>
            </div>
            <div>
                <label for="description">Description</label>
                <input id="description" name="description" value="{{ old('description', optional($editing)->description) }}" placeholder="Optional">
            </div>
        </div>
        <br>
        <div class="actions">
            <button type="submit" class="primary">{{ $editing ? 'Save changes' : 'Add location' }}</button>
            @if ($editing)
                <a class="btn" href="/admin/locations">Cancel</a>
            @endif
        </div>
    </form>

    <div class="box">
        <h3>All locations</h3>
        @if ($locations->isEmpty())
            <div class="empty">No locations yet. Add the first one above.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Location</th><th>Description</th><th>Rooms</th><th>Actions</th></tr>
                    @foreach ($locations as $location)
                        <tr>
                            <td><b>{{ $location->name }}</b></td>
                            <td>{{ $location->description ?: '—' }}</td>
                            <td>
                                {{ $location->rooms->count() }}
                                @if ($location->rooms->count() > 0)
                                    <br><small class="muted">{{ $location->rooms->pluck('room_no')->sort(SORT_NATURAL)->implode(', ') }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn small" href="/rooms/create?location={{ $location->id }}">+ Add room</a>
                                    <a class="btn small" href="/admin/locations/{{ $location->id }}/edit">Edit</a>
                                    <form method="POST" action="/admin/locations/{{ $location->id }}" data-confirm="Delete {{ $location->name }}?">
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
