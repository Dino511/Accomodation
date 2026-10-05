<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set password - Guest Accommodation System</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="login-page">

    <div class="login">

        <div class="login-header">
            <h2>Guest Accommodation System</h2>
            <p>Reception System</p>
        </div>

        <div class="login-body">

            <h3>Set your password</h3>
            <p class="login-intro">Choose the password you will use to log in.</p>

            @if (session('error'))
                <div class="error">
                    {{ session('error') }}
                    <a href="{{ route('password.request') }}">Get a new link</a>
                </div>
            @endif

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="login-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $email) }}"
                        autocomplete="email"
                        required
                        readonly
                    >
                </div>

                <div class="login-field">
                    <label for="password">New password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="At least 8 characters"
                        autocomplete="new-password"
                        minlength="8"
                        required
                        autofocus
                    >
                </div>

                <div class="login-field">
                    <label for="password_confirmation">Repeat the password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        minlength="8"
                        required
                    >
                </div>

                <button type="submit" class="primary login-button">
                    Save password
                </button>
            </form>

            <div class="login-footer">
                <a href="{{ route('login') }}">Back to login</a>
            </div>

        </div>

    </div>

</body>
</html>
