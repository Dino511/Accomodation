<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Guest Accommodation System</title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body class="login-page">

    <div class="login">

        <div class="login-header">
            <h2>Guest Accommodation System</h2>
            <p>Reception System</p>
        </div>

        <div class="login-body">

            <h3>Welcome Back</h3>
            <p class="login-intro">Please login to continue.</p>

            @if (session('success'))
                <div class="alert success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="error">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="/login">
                @csrf

                <div class="login-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        placeholder="Enter your email"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >
                </div>

                <button type="submit" class="primary login-button">
                    Login
                </button>
            </form>

            <div class="login-footer">
                <a href="{{ route('password.request') }}">Forgot password?</a>
            </div>

            <div class="login-footer">
                Guest Accommodation Reception
            </div>

        </div>

    </div>

</body>
</html>
