@extends('layout')

@section('content')
    <h2>Calendar</h2>
    <p class="subtitle">Arrivals (IN) and departures (OUT) for each day of the month.</p>

    <div class="box">
        <div class="actions" style="margin-bottom:12px">
            <a class="btn" href="/calendar?month={{ $prev }}">‹ Previous</a>
            <a class="btn" href="/calendar">Today</a>
            <a class="btn" href="/calendar?month={{ $next }}">Next ›</a>
            <strong style="padding:8px">{{ $first->format('F Y') }}</strong>
        </div>

        <div class="calendar">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="cal-head">{{ $day }}</div>
            @endforeach

            @for ($i = 0; $i < $first->dayOfWeek; $i++)
                <div class="cal-day"></div>
            @endfor

            @for ($day = 1; $day <= $first->daysInMonth; $day++)
                @php $date = $first->format('Y-m-').str_pad($day, 2, '0', STR_PAD_LEFT); @endphp
                <div class="cal-day {{ $date == date('Y-m-d') ? 'today' : '' }}">
                    <div class="cal-number">{{ $day }}</div>
                    @foreach ($events[$date] ?? [] as $event)
                        <div class="cal-event" title="{{ $event }}">{{ $event }}</div>
                    @endforeach
                </div>
            @endfor
        </div>
    </div>
@endsection
