@extends('layout')

@section('content')
    <div class="page-head">
        <div>
            <h2>Calendar</h2>
            <p class="subtitle">See who is arriving or checking out, their room, status and scheduled time.</p>
        </div>
    </div>

    <div class="box">
        <div class="calendar-toolbar">
            <div class="actions">
                <a class="btn" href="/calendar?month={{ $prev }}">‹ Previous</a>
                <a class="btn" href="/calendar">Today</a>
                <a class="btn" href="/calendar?month={{ $next }}">Next ›</a>
            </div>
            <h3>{{ $first->format('F Y') }}</h3>
        </div>

        <div class="calendar-summary">
            <span class="calendar-key arrival-key">Arrivals / reservations <b>{{ $arrivalCount }}</b></span>
            <span class="calendar-key departure-key">Expected check-outs <b>{{ $departureCount }}</b></span>
            <span class="muted">Select a day to review its schedule.</span>
        </div>

        <div class="calendar-wrap">
        <div class="calendar">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="cal-head">{{ $day }}</div>
            @endforeach

            @for ($i = 0; $i < $first->dayOfWeek; $i++)
                <div class="cal-day"></div>
            @endfor

            @for ($day = 1; $day <= $first->daysInMonth; $day++)
                @php
                    $date = $first->format('Y-m-').str_pad($day, 2, '0', STR_PAD_LEFT);
                    $dayEvents = $events[$date] ?? [];
                @endphp
                <div class="cal-day {{ $date == date('Y-m-d') ? 'today' : '' }} {{ $dayEvents ? 'has-events' : '' }}">
                    <div class="cal-number">
                        <span>{{ $day }}</span>
                        @if ($dayEvents)
                            <small>{{ count($dayEvents) }} {{ count($dayEvents) === 1 ? 'activity' : 'activities' }}</small>
                        @endif
                    </div>
                    @foreach ($dayEvents as $event)
                        <div class="cal-event {{ $event['type'] }}" title="{{ $event['label'] }}: {{ $event['guest'] }}, {{ $event['room'] }}, {{ $event['time'] }}">
                            <b>{{ $event['label'] }}</b>
                            <span>{{ $event['guest'] }}</span>
                            <small>{{ $event['room'] }} · {{ $event['time'] }}</small>
                            <small class="cal-status">{{ $event['status'] }}</small>
                        </div>
                    @endforeach
                </div>
            @endfor
        </div>
        </div>
    </div>
@endsection
