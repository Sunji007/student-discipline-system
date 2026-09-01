<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน — โรงเรียนศิริราษฎร์สามัคคี</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --purple-dark:   #1a0533;
            --purple-card:   #2a0a4a;
            --purple-orb1:   hsla(272, 85%, 58%, 0.65);
            --purple-orb2:   hsla(310, 75%, 55%, 0.50);
            --purple-orb3:   hsla(240, 80%, 45%, 0.45);
            --primary:       hsl(270, 80%, 52%);
            --primary-dark:  hsl(270, 80%, 38%);
            --primary-light: hsl(280, 80%, 68%);
            --primary-glow:  hsla(270, 80%, 55%, 0.35);
            --primary-pale:  hsla(270, 80%, 55%, 0.08);
            --white:         #ffffff;
            --card-bg:       rgba(255, 255, 255, 0.97);
            --text:          hsl(260, 40%, 12%);
            --text-sub:      hsl(260, 18%, 40%);
            --text-muted:    hsl(260, 12%, 58%);
            --border:        hsl(260, 18%, 88%);
            --red:           hsl(348, 68%, 45%);
            --green:         #16a34a;
            --r-sm: 10px;
            --r-md: 16px;
            --r-lg: 24px;
            --ease-expo: cubic-bezier(0.16, 1, 0.3, 1);
            --ease-std:  cubic-bezier(0.4, 0, 0.2, 1);
            --dur: 0.25s;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--purple-dark);
            position: relative;
            overflow-y: auto;
            padding: 2rem 1rem;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: radial-gradient(circle, hsla(270,60%,80%,0.07) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            z-index: 0;
        }

        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(72px);
            pointer-events: none;
            z-index: 1;
        }

        .orb-1 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--purple-orb1) 0%, transparent 70%);
            top: -180px;
            right: -150px;
            animation: orbFloat1 9s ease-in-out infinite alternate;
        }

        .orb-2 {
            width: 480px;
            height: 480px;
            background: radial-gradient(circle, var(--purple-orb2) 0%, transparent 70%);
            bottom: -160px;
            left: -130px;
            animation: orbFloat2 11s ease-in-out infinite alternate;
        }

        .orb-3 {
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, var(--purple-orb3) 0%, transparent 70%);
            top: 30%;
            left: 30%;
            animation: orbFloat3 13s ease-in-out infinite alternate;
        }

        @keyframes orbFloat1 {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-40px, 50px) scale(1.12); }
        }
        @keyframes orbFloat2 {
            0%   { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, -40px) scale(1.15); }
        }
        @keyframes orbFloat3 {
            0%   { transform: translate(0, 0) scale(1) rotate(0deg); }
            100% { transform: translate(-30px, -50px) scale(1.08) rotate(15deg); }
        }

        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            border-radius: var(--r-lg);
            overflow: hidden;
            background: var(--card-bg);
            border: 1px solid hsla(270, 60%, 90%, 0.40);
            box-shadow:
                0 0 0 1px hsla(270, 80%, 55%, 0.12),
                0 8px 32px  hsla(260, 90%, 8%, 0.25),
                0 32px 80px hsla(260, 90%, 8%, 0.30),
                0 0 80px hsla(270, 80%, 52%, 0.18);
            animation: riseIn 0.75s var(--ease-expo) both;
        }

        @keyframes riseIn {
            from { opacity:0; transform: translateY(40px) scale(0.95); filter: blur(6px); }
            to   { opacity:1; transform: translateY(0)    scale(1);    filter: blur(0); }
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg,
                hsl(240, 80%, 45%) 0%,
                hsl(270, 80%, 55%) 40%,
                hsl(310, 75%, 60%) 100%);
            z-index: 1;
        }

        .card-header {
            padding: 1.5rem 2rem 1.25rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
        }

        .school-emblem {
            width: 62px;
            height: auto;
            margin: 0 auto 0.75rem;
            display: block;
            filter: drop-shadow(0 4px 14px hsla(270, 80%, 52%, 0.22));
            transition: transform 0.4s var(--ease-expo);
        }
        .school-emblem:hover { transform: scale(1.07) rotate(3deg); }

        .card-header h1 {
            color: var(--text);
            font-size: 0.95rem;
            font-weight: 700;
            line-height: 1.5;
        }
        .card-header p {
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 500;
            margin-top: 0.2rem;
        }

        .card-divider {
            height: 2px;
            width: 40px;
            background: linear-gradient(90deg, hsl(270,80%,52%), hsl(310,70%,62%));
            border-radius: 99px;
            margin: 0.75rem auto 0;
        }

        .card-body { padding: 1.25rem 2rem 1.75rem; }

        .login-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
            margin-bottom: 0.2rem;
        }
        .login-subtitle {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 1.25rem;
            font-weight: 400;
        }

        .alert-danger {
            background: hsla(348,68%,45%,0.06);
            border: 1px solid hsla(348,68%,45%,0.18);
            border-left: 3px solid var(--red);
            color: var(--red);
            padding: 0.85rem 1rem;
            border-radius: var(--r-sm);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: shakeX 0.4s ease;
        }
        .alert-success {
            background: rgba(22, 163, 74, 0.06);
            border: 1px solid rgba(22, 163, 74, 0.18);
            border-left: 3px solid var(--green);
            color: var(--green);
            padding: 0.85rem 1rem;
            border-radius: var(--r-sm);
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        @keyframes shakeX {
            0%,100%{ transform:translateX(0); }
            20%    { transform:translateX(-6px); }
            40%    { transform:translateX(5px); }
            60%    { transform:translateX(-4px); }
            80%    { transform:translateX(3px); }
        }

        .form-group { margin-bottom: 0.9rem; }

        .form-group label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--text-sub);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 0.9rem;
            color: var(--text-muted);
            pointer-events: none;
            z-index: 2;
            display: flex;
            align-items: center;
        }

        .form-group input[type="email"] {
            width: 100%;
            padding: 0.65rem 1rem 0.65rem 2.6rem;
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm);
            font-family: 'Sarabun', sans-serif;
            font-size: 0.95rem;
            background: hsl(260, 25%, 99%);
            color: var(--text);
            outline: none;
            transition:
                border-color var(--dur) var(--ease-std),
                box-shadow   var(--dur) var(--ease-std),
                background   var(--dur) var(--ease-std);
        }
        .form-group input::placeholder { color: hsl(260,12%,70%); }
        .form-group input:hover        { border-color: hsl(270,30%,78%); }
        .form-group input:focus {
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 3px var(--primary-pale), 0 1px 4px hsla(270,80%,52%,0.08);
        }
        .form-group input.is-invalid {
            border-color: var(--red);
            box-shadow: 0 0 0 3px hsla(348,68%,45%,0.10);
        }
        .invalid-feedback {
            color: var(--red);
            font-size: 0.78rem;
            margin-top: 0.4rem;
            font-weight: 500;
        }

        .form-actions { margin-bottom: 1.25rem; display: flex; justify-content: flex-end; align-items: center; }
        .forgot-password-link {
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color var(--dur) var(--ease-std);
        }
        .forgot-password-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--r-sm);
            font-family: 'Sarabun', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            color: #fff;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg,
                hsl(255, 75%, 45%) 0%,
                hsl(270, 80%, 52%) 50%,
                hsl(300, 70%, 58%) 100%);
            box-shadow:
                0 4px 20px var(--primary-glow),
                0 1px 4px hsla(270,80%,52%,0.20);
            transition:
                transform    var(--dur) var(--ease-std),
                box-shadow   var(--dur) var(--ease-std);
        }
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg,
                transparent 35%,
                hsla(0,0%,100%,0.22) 50%,
                transparent 65%);
            transform: translateX(-100%);
            transition: transform 0.55s var(--ease-std);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px var(--primary-glow), 0 2px 8px hsla(270,80%,52%,0.25);
        }
        .btn-login:hover::after { transform: translateX(100%); }
        .btn-login:active { transform: translateY(0); box-shadow: 0 3px 10px var(--primary-glow); }

        .card-footer-note {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.6;
        }

        @media (max-width: 1024px) {
            body { padding: 1.5rem; align-items: flex-start; overflow-y: auto; }
            .login-card { margin: 3rem auto; }
        }
    </style>
</head>
<body>

    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="login-card">
        <div class="card-header">
            <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียนศิริราษฎร์สามัคคี" class="school-emblem">
            <h1>ระบบสารสนเทศการบริหารงานวินัย<br>และติดตามพฤติกรรมนักเรียน<br>โรงเรียนศิริราษฎร์สามัคคี</h1>
            <p>จังหวัดปัตตานี</p>
            <div class="card-divider"></div>
        </div>

        <div class="card-body">
            <h2 class="login-title">ลืมรหัสผ่าน</h2>
            <p class="login-subtitle">กรอกอีเมลเพื่อรับลิงก์สำหรับเปลี่ยนรหัสผ่านใหม่</p>

            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                {{-- Email Address --}}
                <div class="form-group">
                    <label for="email">อีเมลของคุณ</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                        </span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                            autocomplete="email"
                            autofocus
                            required
                            placeholder="example@example.com"
                        >
                    </div>
                </div>

                <div class="form-actions" style="justify-content: center;">
                    <a href="{{ route('login') }}" class="forgot-password-link">
                        กลับไปหน้าเข้าสู่ระบบ
                    </a>
                </div>

                <button type="submit" class="btn-login">
                    ส่งลิงก์เปลี่ยนรหัสผ่าน
                </button>
            </form>

            <p class="card-footer-note">
                หากพบปัญหาการเข้าใช้งาน กรุณาติดต่อผู้ดูแลระบบ
            </p>
        </div>
    </div>

</body>
</html>
