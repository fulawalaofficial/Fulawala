<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delete Account | Fulawala</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(255, 118, 145, .16), transparent 34%),
                radial-gradient(circle at bottom right, rgba(76, 175, 80, .12), transparent 32%),
                #fffaf8;
            color: #2d2d2d;
        }

        .page {
            width: min(100% - 32px, 760px);
            margin: 0 auto;
            padding: 48px 0;
        }

        .brand {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 24px;
            font-size: 30px;
            font-weight: 800;
            color: #c52f5b;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6f91, #d12c60);
            color: #fff;
            box-shadow: 0 10px 25px rgba(209, 44, 96, .22);
        }

        .card {
            background: rgba(255, 255, 255, .96);
            border: 1px solid #f1dfe4;
            border-radius: 24px;
            box-shadow: 0 22px 60px rgba(77, 33, 45, .10);
            overflow: hidden;
        }

        .card-header {
            padding: 30px 32px 22px;
            border-bottom: 1px solid #f4e6ea;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 7px 12px;
            border-radius: 999px;
            background: #fff0f4;
            color: #b1244f;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        h1 {
            margin: 14px 0 8px;
            font-size: clamp(28px, 5vw, 40px);
            line-height: 1.15;
            color: #28191e;
        }

        .subtitle {
            margin: 0;
            color: #6c5d61;
            line-height: 1.7;
        }

        .content {
            padding: 28px 32px 34px;
        }

        .warning {
            display: flex;
            gap: 14px;
            padding: 18px;
            margin-bottom: 24px;
            border-radius: 16px;
            border: 1px solid #ffd2d2;
            background: #fff5f5;
            color: #7e2525;
            line-height: 1.55;
        }

        .warning strong {
            display: block;
            margin-bottom: 4px;
        }

        .field {
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #3f3437;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            height: 50px;
            padding: 0 14px;
            border: 1px solid #d9cdd1;
            border-radius: 12px;
            outline: none;
            font-size: 15px;
            transition: border-color .2s ease, box-shadow .2s ease;
            background: #fff;
        }

        input:focus {
            border-color: #d12c60;
            box-shadow: 0 0 0 4px rgba(209, 44, 96, .09);
        }

        .hint {
            display: block;
            margin-top: 6px;
            color: #8a7a7f;
            font-size: 12px;
        }

        .check-row {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            margin: 20px 0;
            padding: 14px;
            border-radius: 12px;
            background: #faf7f8;
        }

        .check-row input {
            margin-top: 3px;
        }

        .check-row label {
            margin: 0;
            font-weight: 600;
            line-height: 1.5;
        }

        .btn {
            width: 100%;
            border: 0;
            border-radius: 13px;
            min-height: 52px;
            padding: 12px 18px;
            background: linear-gradient(135deg, #d7284f, #a30f38);
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            box-shadow: 0 12px 28px rgba(174, 25, 62, .24);
        }

        .btn:hover {
            filter: brightness(.97);
        }

        .alert {
            padding: 14px 16px;
            margin-bottom: 18px;
            border-radius: 12px;
            line-height: 1.5;
        }

        .alert-success {
            border: 1px solid #bce4c3;
            background: #effbf1;
            color: #216d2d;
        }

        .alert-error {
            border: 1px solid #f0c1c1;
            background: #fff3f3;
            color: #9b2a2a;
        }

        .error-text {
            margin-top: 6px;
            color: #c32745;
            font-size: 13px;
        }

        .back {
            display: block;
            margin: 22px auto 0;
            width: fit-content;
            color: #8a5262;
            text-decoration: none;
            font-weight: 700;
        }

        .back:hover {
            color: #c52f5b;
        }

        @media (max-width: 640px) {
            .page {
                width: min(100% - 20px, 760px);
                padding: 24px 0;
            }

            .card-header,
            .content {
                padding-left: 20px;
                padding-right: 20px;
            }

            .brand {
                font-size: 25px;
            }
        }
    </style>
</head>
<body>
    <main class="page">
        <div class="brand">
            <span class="brand-mark">✿</span>
            <span>Fulawala</span>
        </div>

        <section class="card">
            <header class="card-header">
                <span class="eyebrow">Account privacy</span>
                <h1>Permanently delete your account</h1>
                <p class="subtitle">
                    Use this form to permanently remove your Fulawala customer account.
                    You must verify your account using your registered email/mobile and password.
                </p>
            </header>

            <div class="content">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        Please correct the highlighted information and try again.
                    </div>
                @endif

                <div class="warning">
                    <div>⚠️</div>
                    <div>
                        <strong>This action cannot be undone.</strong>
                        Your profile, saved addresses, devices, subscriptions, custom orders,
                        event bookings, payment records and API access tokens associated with
                        this customer account will be permanently removed.
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('website.account-delete.destroy') }}"
                    onsubmit="return confirm('Are you sure you want to permanently delete this Fulawala account? This cannot be undone.');"
                >
                    @csrf
                    @method('DELETE')

                    <div class="field">
                        <label for="login">Registered email or mobile number</label>
                        <input
                            id="login"
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            maxlength="190"
                            autocomplete="username"
                            required
                        >
                        @error('login')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Account password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            maxlength="255"
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="confirmation">Type DELETE to confirm</label>
                        <input
                            id="confirmation"
                            type="text"
                            name="confirmation"
                            placeholder="DELETE"
                            autocomplete="off"
                            required
                        >
                        <span class="hint">The confirmation must be exactly: DELETE</span>
                        @error('confirmation')
                            <div class="error-text">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="check-row">
                        <input
                            id="understand"
                            type="checkbox"
                            name="understand"
                            value="1"
                            required
                        >
                        <label for="understand">
                            I understand that deleting my account is permanent and the deleted data cannot be restored.
                        </label>
                    </div>
                    @error('understand')
                        <div class="error-text" style="margin-top:-12px;margin-bottom:16px;">{{ $message }}</div>
                    @enderror

                    <button type="submit" class="btn">
                        Permanently Delete My Account
                    </button>
                </form>
            </div>
        </section>

        <a class="back" href="{{ route('website.home') }}">← Back to Fulawala</a>
    </main>
</body>
</html>
