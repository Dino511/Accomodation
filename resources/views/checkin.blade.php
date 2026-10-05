@extends('layout')

@section('content')
    <h2>Check-in</h2>
    <p class="subtitle">One step at a time. The guest is at reception (steps 1 and 2 are done). Click a step above to go back to it.</p>

    @include('partials.steps', ['steps' => App\Models\Booking::CHECKIN_STEPS, 'current' => 3])

    <form method="POST" action="/checkin" id="checkinForm">
        @csrf

        {{-- STEP 3 --}}
        <div class="box step" data-step="3">
            <div class="step-title">
                <span class="num">3</span>
                <h3>Fill out the check-in form</h3>
            </div>

            <label for="booking_id">Has a reservation?</label>

            <select
                id="booking_id"
                name="booking_id"
                onchange="location.href = '/checkin' + (this.value ? '?booking=' + this.value : '')"
            >
                <option value="">No reservation (walk-in guest)</option>

                @foreach ($reservations as $r)
                    <option
                        value="{{ $r->id }}"
                        {{ optional($selected)->id == $r->id ? 'selected' : '' }}
                    >
                        {{ $r->guest_name }} — {{ $r->room->room_no }} — {{ $r->check_in->format('M d, Y') }}
                    </option>
                @endforeach
            </select>

            <p class="hint">Choosing a reservation fills in the form below.</p>

            <div class="form-grid">

                <div>
                    <label for="guest_name">
                        Guest name <span class="req">*</span>
                    </label>

                    <input
                        id="guest_name"
                        name="guest_name"
                        value="{{ old('guest_name', optional($selected)->guest_name) }}"
                        placeholder="Full name"
                        required
                    >
                </div>

                <div>
                    <label for="guest_type">
                        Guest type <span class="req">*</span>
                    </label>

                    <select id="guest_type" name="guest_type">
                        @foreach (['Visitor', 'Contractor'] as $type)
                            <option
                                {{ old('guest_type', optional($selected)->guest_type) == $type ? 'selected' : '' }}
                            >
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="company">Company</label>

                    <input
                        id="company"
                        name="company"
                        value="{{ old('company', optional($selected)->company) }}"
                        placeholder="Company name"
                    >
                </div>

                <div>
                    <label for="contact_no">
                        Contact number <span class="req">*</span>
                    </label>

                    <input
                        id="contact_no"
                        name="contact_no"
                        value="{{ old('contact_no', optional($selected)->contact_no) }}"
                        placeholder="09xx xxx xxxx"
                        required
                    >
                </div>

                <div class="full">
                    <label for="address">
                        Address <span class="req">*</span>
                    </label>

                    <input
                        id="address"
                        name="address"
                        value="{{ old('address', optional($selected)->address) }}"
                        placeholder="House no., street, barangay, city"
                        required
                    >
                </div>

                <div>
                    <label for="no_of_guests">
                        Number of guests <span class="req">*</span>
                    </label>

                    <input
                        id="no_of_guests"
                        name="no_of_guests"
                        type="number"
                        min="1"
                        value="{{ old('no_of_guests', optional($selected)->no_of_guests ?? 1) }}"
                        required
                    >
                </div>

                {{-- EXPECTED CHECK-OUT --}}
                <div>
                    <label for="check_out_date">
                        Expected check-out <span class="req">*</span>
                    </label>

                    <div class="input-row">
                        <input
                            id="check_out_date"
                            type="date"
                            min="{{ date('Y-m-d') }}"
                            value="{{ old(
                                'check_out_date',
                                $selected
                                    ? $selected->check_out->format('Y-m-d')
                                    : date('Y-m-d', strtotime('+1 day'))
                            ) }}"
                            required
                        >

                        <input
                            id="check_out_time"
                            type="time"
                            value="{{ old(
                                'check_out_time',
                                $selected && $selected->check_out_time
                                    ? substr($selected->check_out_time, 0, 5)
                                    : '12:00'
                            ) }}"
                            required
                        >
                    </div>

                    <input
                        type="hidden"
                        id="check_out_datetime"
                        name="check_out_datetime"
                        value="{{ old('check_out_datetime') }}"
                    >

                    <p class="hint">
                        Select the expected date and time the guest will leave.
                    </p>
                </div>

                {{-- GUEST LIST (shown when there are 2 or more guests) --}}
                <div class="full" id="guestListBox" hidden>
                    <label>
                        Other guests in the group <span class="req">*</span>
                    </label>

                    <div
                        class="guest-list"
                        id="guestList"
                        data-guests="{{ json_encode(array_values(old('guest_list', optional($selected)->guest_list ?? []))) }}"
                    ></div>

                    <p class="hint">
                        The guest named above is guest 1. Enter the name, address and contact number of each other guest.
                        Everyone is checked in to the room assigned in step 6.
                    </p>
                </div>

                <div class="full">
                    <label for="remarks">Purpose / notes</label>

                    <textarea
                        id="remarks"
                        name="remarks"
                        placeholder="Purpose of stay or other notes"
                    >{{ old('remarks', optional($selected)->remarks) }}</textarea>
                </div>

            </div>
        </div>

        {{-- STEP 4 --}}
        <div class="box step" data-step="4">
            <div class="step-title">
                <span class="num">4</span>
                <h3>Verification</h3>
            </div>

            <p class="muted" style="margin:0">
                Confirm that the guest is expected and the details above are correct.
            </p>

            <label class="check">
                <input
                    type="checkbox"
                    name="verified"
                    required
                    value="1"
                    {{ old('verified') ? 'checked' : '' }}
                >
                I have verified the guest's identity and the details on the form.
            </label>
        </div>

        {{-- STEP 5 --}}
        <div class="box step" data-step="5">
            <div class="step-title">
                <span class="num">5</span>
                <h3>Surrender valid ID</h3>
            </div>

            <div class="form-grid">

                <div>
                    <label for="id_type">
                        Type of ID <span class="req">*</span>
                    </label>

                    <select id="id_type" name="id_type" required>
                        <option value="">Select ID type</option>

                        @foreach (['Company ID', "Driver's License", 'National ID', 'Passport', 'UMID', 'Other Government ID'] as $type)
                            <option {{ old('id_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="id_number">
                        ID number <span class="req">*</span>
                    </label>

                    <input
                        id="id_number"
                        name="id_number"
                        required
                        value="{{ old('id_number') }}"
                        autocomplete="off"
                    >
                </div>

            </div>

            <label class="check">
                <input
                    type="checkbox"
                    name="id_surrendered"
                    required
                    value="1"
                    {{ old('id_surrendered') ? 'checked' : '' }}
                >
                The guest surrendered the ID. Reception keeps it until check-out.
            </label>
        </div>

        {{-- STEP 6 --}}
        <div class="box step" data-step="6">
            <div class="step-title">
                <span class="num">6</span>
                <h3>Assign accommodation (Guest Villa / Barracks)</h3>
            </div>

            <label for="room_id">
                Room <span class="req">*</span>
            </label>

            <select id="room_id" name="room_id" required>
                <option value="">Select an available room</option>

                @foreach ($rooms->groupBy('location.name') as $location => $list)
                    <optgroup label="{{ $location }}">
                        @foreach ($list as $room)
                            <option
                                value="{{ $room->id }}"
                                {{ old('room_id', optional($selected)->room_id) == $room->id ? 'selected' : '' }}
                            >
                                {{ $room->room_no }} (good for {{ $room->capacity }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>

            <p class="hint">
                Only rooms that are Available are listed.

                @if ($selected && $selected->room->status != 'Available')
                    <b>
                        The reserved room {{ $selected->room->room_no }}
                        is {{ $selected->room->status }}, so pick another room.
                    </b>
                @endif
            </p>
        </div>

        <div class="box">
            <div class="actions">
                <button type="button" id="stepBack" hidden>
                    Back
                </button>

                <button type="button" class="primary" id="stepNext" hidden>
                    Next step
                </button>

                <button type="submit" class="success-btn" id="stepFinish">
                    Complete check-in
                </button>

                <a class="btn" href="/dashboard">Cancel</a>
            </div>

            <p class="hint" id="stepHint">
                Next: step 7, the room assignment slip is shown, then the guest proceeds to the room (step 8).
            </p>
        </div>
    </form>

    <div class="box">
        <h3>Currently checked in</h3>

        @if ($current->isEmpty())
            <div class="empty">No guests are currently checked in.</div>
        @else
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Guest</th>
                        <th>Company</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Expected check-out</th>
                        <th></th>
                    </tr>

                    @foreach ($current as $b)
                        <tr>
                            <td>{{ $b->guest_name }}</td>
                            <td>{{ $b->company ?: '—' }}</td>
                            <td>{{ $b->room->room_no }}</td>

                            <td>
                                {{ $b->check_in->format('M d, Y') }}
                                @if ($b->check_in_time)
                                    <br>
                                    <small class="muted">{{ $b->timeText('check_in_time') }}</small>
                                @endif
                            </td>

                            <td>
                                {{ $b->check_out->format('M d, Y') }}
                                @if ($b->check_out_time)
                                    <br>
                                    <small class="muted">{{ $b->timeText('check_out_time') }}</small>
                                @endif
                            </td>

                            <td>
                                <a
                                    class="btn small"
                                    href="/checkin/{{ $b->id }}/slip"
                                >
                                    Room slip
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    <script>
        document.getElementById('checkinForm').addEventListener('submit', function () {
            const date = document.getElementById('check_out_date').value;
            const time = document.getElementById('check_out_time').value;

            document.getElementById('check_out_datetime').value = date + 'T' + time;
        });

        // One row (name, address, contact number) for every guest after the first.
        const guestCount = document.getElementById('no_of_guests');
        const guestListBox = document.getElementById('guestListBox');
        const guestList = document.getElementById('guestList');
        const guestFields = [
            ['name', 'full name', 150],
            ['address', 'address', 255],
            ['contact_no', 'contact number', 50],
        ];
        let guests = JSON.parse(guestList.dataset.guests || '[]');

        function showGuestList() {
            // keep what was already typed
            guestList.querySelectorAll('input').forEach(function (field) {
                guests[field.dataset.row] = guests[field.dataset.row] || {};
                guests[field.dataset.row][field.dataset.field] = field.value;
            });

            const others = Math.min(Math.max((parseInt(guestCount.value) || 1) - 1, 0), 50);

            guestList.innerHTML = '';
            guestListBox.hidden = others === 0;

            for (let i = 0; i < others; i++) {
                const row = document.createElement('div');
                row.className = 'guest-row';

                guestFields.forEach(function ([key, label, max]) {
                    const field = document.createElement('input');

                    field.name = 'guest_list[' + i + '][' + key + ']';
                    field.value = (guests[i] || {})[key] || '';
                    field.placeholder = 'Guest ' + (i + 2) + ' ' + label;
                    field.maxLength = max;
                    field.required = true;
                    field.dataset.row = i;
                    field.dataset.field = key;
                    field.setAttribute('aria-label', 'Guest ' + (i + 2) + ' ' + label);

                    row.appendChild(field);
                });

                guestList.appendChild(row);
            }
        }

        guestCount.addEventListener('input', showGuestList);
        showGuestList();

        // ---- One step on screen at a time (steps 3 to 6) ----
        const form = document.getElementById('checkinForm');
        const formSteps = [3, 4, 5, 6];
        let currentStep = 3;
        let reachedStep = 3;

        function stepBox(n) {
            return form.querySelector('.box.step[data-step="' + n + '"]');
        }

        function stepFields(n) {
            return Array.from(stepBox(n).querySelectorAll('input, select, textarea'));
        }

        function stepIsComplete(n) {
            return stepFields(n).every(function (field) {
                return field.checkValidity();
            });
        }

        // the first step from 3 up to "last" that still has something missing
        function firstIncomplete(last) {
            return formSteps.find(function (n) {
                return n <= last && ! stepIsComplete(n);
            });
        }

        function showStep(n) {
            currentStep = n;
            reachedStep = Math.max(reachedStep, n);

            formSteps.forEach(function (step) {
                stepBox(step).hidden = step !== n;
            });

            document.querySelectorAll('.steps li').forEach(function (item) {
                const step = Number(item.dataset.step);
                const done = step < 3 || (step <= 6 && step !== n && step <= reachedStep && stepIsComplete(step));

                item.classList.toggle('done', done);
                item.classList.toggle('now', step === n);
                item.classList.toggle('clickable', step >= 3 && step <= 6);
                item.querySelector('.num').textContent = done ? '✓' : step;
            });

            document.getElementById('stepBack').hidden = n === 3;
            document.getElementById('stepNext').hidden = n === 6;
            document.getElementById('stepFinish').hidden = n !== 6;
            document.getElementById('stepHint').hidden = n !== 6;
        }

        // show the step with something missing and point at the field
        function askToComplete(n) {
            showStep(n);

            stepFields(n).find(function (field) {
                return ! field.checkValidity();
            }).reportValidity();
        }

        function goToStep(n) {
            const missing = firstIncomplete(n - 1);

            if (missing) {
                askToComplete(missing);
            } else {
                showStep(n);
                window.scrollTo(0, 0);
            }
        }

        document.getElementById('stepNext').addEventListener('click', function () {
            goToStep(currentStep + 1);
        });

        document.getElementById('stepBack').addEventListener('click', function () {
            showStep(currentStep - 1);
            window.scrollTo(0, 0);
        });

        document.querySelectorAll('.steps li').forEach(function (item) {
            const step = Number(item.dataset.step);

            if (step >= 3 && step <= 6) {
                item.addEventListener('click', function () {
                    goToStep(step);
                });
            }
        });

        // a hidden step with something missing would silently block the save
        form.addEventListener('submit', function (event) {
            const missing = firstIncomplete(6);

            if (missing) {
                event.preventDefault();
                askToComplete(missing);
            }
        });

        form.noValidate = true;

        // after a refused save, open the step that needs fixing (or the last step)
        if ({{ $errors->any() || session()->has('error') ? 'true' : 'false' }}) {
            reachedStep = 6;
            showStep(firstIncomplete(6) || 6);
        } else {
            showStep(3);
        }
    </script>
@endsection
