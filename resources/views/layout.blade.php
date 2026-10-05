<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Guest Accommodation – Reception</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <aside>
        <div class="brand">
            <b>⚓ Guest Accommodation</b>
            <span>Mindoro Marine Mfg. Corp.</span>
        </div>

        <nav>
            @if (auth()->user()->role == 'reception')
            <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">Dashboard</a>

            <div class="group">Front desk</div>
            <a href="/checkin" class="{{ request()->is('checkin*') ? 'active' : '' }}">Check-in</a>
            <a href="/checkout" class="{{ request()->is('checkout*') ? 'active' : '' }}">Check-out</a>
            <a href="/bookings" class="{{ request()->is('bookings*') ? 'active' : '' }}">Reservations</a>
            <a href="/calendar" class="{{ request()->is('calendar') ? 'active' : '' }}">Calendar</a>

            <div class="group">Records</div>
            <a href="/reports" class="{{ request()->is('reports') ? 'active' : '' }}">Guest Log</a>

            @else
                <a href="/admin" class="{{ request()->is('admin') ? 'active' : '' }}">Admin Dashboard</a>

                <div class="group">Setup</div>
                <a href="/admin/locations" class="{{ request()->is('admin/locations*') ? 'active' : '' }}">Locations</a>
                <a href="/rooms" class="{{ request()->is('rooms*') ? 'active' : '' }}">Rooms</a>
                <a href="/admin/users" class="{{ request()->is('admin/users*') ? 'active' : '' }}">Accounts</a>
            @endif
        </nav>

        <div class="user">
            {{ auth()->user()->name }}
            <small>{{ auth()->user()->role == 'admin' ? 'Admin' : 'Reception' }} · {{ auth()->user()->email }}</small>
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="small">Logout</button>
            </form>
        </div>
    </aside>

    <main>
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert error">
                <b>Please fix the following:</b><br>
                @foreach ($errors->all() as $e)
                    • {{ $e }}<br>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
