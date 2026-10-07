@extends('layout')

@section('content')
    <h2>Accounts</h2>
    <p class="subtitle">Create and manage the accounts of the reception staff.</p>

    <form class="box" method="POST" action="{{ $editing ? '/admin/users/'.$editing->id : '/admin/users' }}">
        @csrf
        @if ($editing)
            @method('PUT')
        @endif

        <h3>{{ $editing ? 'Edit account' : 'Add an account' }}</h3>
        <div class="form-grid">
            <div>
                <label for="name">Name <span class="req">*</span></label>
                <input id="name" name="name" value="{{ old('name', optional($editing)->name) }}" required>
            </div>
            <div>
                <label for="email">Email <span class="req">*</span></label>
                <input id="email" name="email" type="email" value="{{ old('email', optional($editing)->email) }}" required>
            </div>
            <div>
                <label for="role">Role <span class="req">*</span></label>
                <select id="role" name="role">
                    <option value="reception" {{ old('role', optional($editing)->role) == 'reception' ? 'selected' : '' }}>Reception</option>
                    <option value="admin" {{ old('role', optional($editing)->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
        </div>
        <p class="hint">
            {{ $editing
                ? 'The password is not changed here. Use "Send password link" in the list below.'
                : 'You do not set the password. A link is emailed to this address so the user can set their own.' }}
        </p>
        <br>
        <div class="actions">
            <button type="submit" class="primary">{{ $editing ? 'Save changes' : 'Create account' }}</button>
            @if ($editing)
                <a class="btn" href="/admin/users">Cancel</a>
            @endif
        </div>
    </form>

    <div class="box">
        <h3>All accounts</h3>
        <div class="table-wrap">
            <table>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }} @if ($user->id == auth()->id())<small class="muted">(you)</small>@endif</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->role == 'admin' ? 'Admin' : 'Reception' }}</td>
                        <td><span class="badge {{ $user->active ? 'free' : 'used' }}">{{ $user->active ? 'Active' : 'Deactivated' }}</span></td>
                        <td>
                            <div class="actions">
                                <a class="btn small" href="/admin/users/{{ $user->id }}/edit">Edit</a>
                                <form method="POST" action="/admin/users/{{ $user->id }}/password-link" data-confirm="Email {{ $user->name }} a link to set a new password?">
                                    @csrf
                                    <button type="submit" class="small">Send password link</button>
                                </form>
                                @if ($user->id != auth()->id())
                                    <form method="POST" action="/admin/users/{{ $user->id }}/toggle" data-confirm="{{ $user->active ? 'Deactivate' : 'Activate' }} {{ $user->name }}?">
                                        @csrf
                                        <button type="submit" class="small {{ $user->active ? 'danger' : 'success-btn' }}">{{ $user->active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <p class="hint">A deactivated account cannot log in. Its records stay in the guest log. A password link works for {{ config('auth.passwords.users.expire') }} minutes.</p>
    </div>
@endsection
