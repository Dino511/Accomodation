@extends('layout')

@php
    // group the guests in house by their expected check-out date
    $overdue = $current->filter(fn ($b) => $b->check_out->lt(today()));
    $dueToday = $current->filter(fn ($b) => $b->check_out->isToday());
    $upcoming = $current->filter(fn ($b) => $b->check_out->gt(today()));
@endphp

@section('content')
    <h2>Check-out</h2>
    <p class="subtitle">Guests who are overdue or due today are highlighted so reception can follow up.</p>

    <div class="stats">
        <div class="stat {{ $overdue->isNotEmpty() ? 'overdue' : '' }}"><b>{{ $overdue->count() }}</b><span>Overdue</span></div>
        <div class="stat {{ $dueToday->isNotEmpty() ? 'due' : '' }}"><b>{{ $dueToday->count() }}</b><span>Due today</span></div>
        <div class="stat"><b>{{ $upcoming->count() }}</b><span>Upcoming</span></div>
    </div>

    @if ($overdue->isNotEmpty())
        <div class="alert error">
            <b>Attention:</b> {{ $overdue->count() }} {{ $overdue->count() == 1 ? 'guest is' : 'guests are' }} past the expected check-out date. Please follow up.
        </div>
    @elseif ($dueToday->isNotEmpty())
        <div class="alert warning">
            {{ $dueToday->count() }} {{ $dueToday->count() == 1 ? 'guest is' : 'guests are' }} expected to check out today.
        </div>
    @endif

    <div class="box">
        <h3>Guests in house</h3>
        @if ($current->isEmpty())
            <div class="empty">No guests are checked in right now.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Guest</th><th>Company</th><th>Room</th><th>Expected check-out</th><th>Status</th><th>Progress</th><th></th></tr>
                    @foreach ($current as $b)
                        @php
                            $isOverdue = $b->check_out->lt(today());
                            $isToday = $b->check_out->isToday();
                        @endphp
                        <tr class="{{ $isOverdue ? 'row-overdue' : ($isToday ? 'row-today' : '') }}">
                            <td>{{ $b->guest_name }}</td>
                            <td>{{ $b->company ?: '—' }}</td>
                            <td>{{ $b->room->room_no }}</td>
                            <td>
                                {{ $b->check_out->format('M d, Y') }}
                                @if ($isOverdue)
                                    <span class="badge used">Overdue</span>
                                @elseif ($isToday)
                                    <span class="badge checkout">Due today</span>
                                @endif
                                @if ($b->check_out_time)
                                    <br><small class="muted">{{ $b->timeText('check_out_time') }}</small>
                                @endif
                            </td>
                            <td><span class="badge {{ $b->badge() }}">{{ $b->status }}</span></td>
                            <td>
                                @if ($b->status == 'Checking Out')
                                    Step {{ $b->checkout_step + 1 }} of 8
                                @else
                                    <span class="muted">Not started</span>
                                @endif
                            </td>
                            <td><a class="btn primary small" href="/checkout/{{ $b->id }}">{{ $b->status == 'Checking Out' ? 'Continue check-out' : 'Check out' }}</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <p class="hint">Guests currently checked in, or already going through the check-out process.</p>
        @endif
    </div>

    <div class="box">
        <h3>Recently checked out</h3>
        @if ($history->isEmpty())
            <div class="empty">No completed check-outs yet.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Checked out</th><th>Charges</th><th></th></tr>
                    @foreach ($history as $b)
                        <tr>
                            <td>{{ $b->guest_name }}</td>
                            <td>{{ $b->room->room_no }}</td>
                            <td>{{ $b->check_in->format('M d, Y') }}</td>
                            <td>{{ ($b->actual_check_out ?? $b->check_out)->format('M d, Y') }}</td>
                            <td>{{ $b->charges > 0 ? '₱'.number_format($b->charges, 2) : 'None' }}</td>
                            <td><a class="btn small" href="/checkout/{{ $b->id }}">View</a></td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>
@endsection
