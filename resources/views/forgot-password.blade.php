<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password - Guest Accommodation System</title>
    <link rel="stylesheet" href="/css/style.css?v={{ filemtime(public_path('css/style.css')) }}">
</head>

<body class="login-page">

    <div class="login">

        <div class="login-header">
            <h2>Guest Accommodation System</h2>
            <p>Reception System</p>
        </div>

        <div class="login-body">

            <h3>Forgot password</h3>
            <p class="login-intro">Enter your email and we will send you a link to set a new password.</p>

            @if (session('success'))
                <div class="alert success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="error">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
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

                <button type="submit" class="primary login-button">
                    Send link
                </button>
            </form>

            <div class="login-footer">
                <a href="{{ route('login') }}">Back to login</a>
            </div>

        </div>

    </div>

</body>
</html>
