<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — โรงเรียนศิริราษฎร์สามัคคี</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        /* ============================================================
           IMPECCABLE LOGIN — ศิริราษฎร์สามัคคี
           Design tokens · 8px grid · HSL palette
        ============================================================ */
        :root {
            --primary:       hsl(240, 95%, 47%);
            --primary-dark:  hsl(240, 95%, 35%);
            --primary-light: hsl(241, 90%, 63%);
            --primary-glow:  hsla(240, 95%, 55%, 0.30);
            --primary-pale:  hsla(240, 95%, 55%, 0.07);
            --card-bg:       hsla(0, 0%, 100%, 0.96);
            --text:          hsl(240, 40%, 12%);
            --text-sub:      hsl(240, 20%, 42%);
            --text-muted:    hsl(240, 15%, 58%);
            --border:        hsl(240, 20%, 91%);
            --red:           hsl(348, 68%, 45%);
            --ease-expo:     cubic-bezier(0.16, 1, 0.3, 1);
            --ease-std:      cubic-bezier(0.4, 0, 0.2, 1);
            --dur:           0.25s;
            --r-sm: 8px;
            --r-md: 14px;
            --r-lg: 20px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Background ── */
        body {
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            background: hsl(240, 60%, 8%);
        }

        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 15% 10%,  hsla(258,80%,32%,0.55) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 85% 88%,  hsla(290,70%,28%,0.40) 0%, transparent 55%),
                radial-gradient(ellipse 100% 80% at 50% 50%, hsl(238,65%,11%) 0%, hsl(240,60%,7%) 100%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(hsla(0,0%,100%,0.025) 1px, transparent 1px),
                linear-gradient(90deg, hsla(0,0%,100%,0.025) 1px, transparent 1px);
            background-size: 44px 44px;
            pointer-events: none;
        }

        /* ── Floating orbs ── */
        .neon-glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
        }
        .neon-glow-1 {
            width: 560px; height: 560px;
            background: radial-gradient(circle, hsla(258,80%,55%,0.18) 0%, transparent 70%);
            top: -180px; right: -120px;
            animation: floatOrb 9s ease-in-out infinite alternate;
        }
        .neon-glow-2 {
            width: 460px; height: 460px;
            background: radial-gradient(circle, hsla(290,70%,50%,0.14) 0%, transparent 70%);
            bottom: -160px; left: -120px;
            animation: floatOrb 11s ease-in-out infinite alternate-reverse;
        }
        @keyframes floatOrb {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(22px,-18px) scale(1.06); }
        }

        /* ── Decorative circles ── */
        .circle-deco {
            position: absolute;
            border-radius: 50%;
            border: 1px solid hsla(0,0%,100%,0.055);
            pointer-events: none;
            z-index: 2;
        }
        .circle-deco:nth-child(3) { width: 720px; height: 720px; top: -260px; right: -160px; }
        .circle-deco:nth-child(4) { width: 480px; height: 480px; bottom: -160px; left: -110px; }

        /* ── Login Card ── */
        .login-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 448px;
            border-radius: var(--r-lg);
            overflow: hidden;
            border: 1px solid hsla(0,0%,100%,0.12);
            background: var(--card-bg);
            backdrop-filter: blur(24px) saturate(1.6);
            -webkit-backdrop-filter: blur(24px) saturate(1.6);
            box-shadow:
                0 0 0 1px hsla(0,0%,100%,0.06),
                0 8px 32px  hsla(240,80%,10%,0.20),
                0 32px 80px hsla(240,80%,10%,0.30),
                0 64px 120px hsla(240,80%,10%,0.20);
            animation: riseIn 0.7s var(--ease-expo) both;
        }

        @keyframes riseIn {
            from { opacity:0; transform:translateY(36px) scale(0.96); filter:blur(4px); }
            to   { opacity:1; transform:translateY(0)    scale(1);    filter:blur(0);  }
        }

        /* Top accent bar */
        .login-card::before {
            content: '';
            position: absolute;
            top:0; left:0; right:0;
            height: 3px;
            background: linear-gradient(90deg,
                hsl(240,95%,35%) 0%,
                hsl(241,90%,63%) 50%,
                hsl(290,70%,55%) 100%);
            z-index: 1;
        }

        /* ── Card Header ── */
        .card-header {
            padding: 2.5rem 2.5rem 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .school-emblem {
            width: 82px;
            height: auto;
            margin: 0 auto 1.25rem;
            display: block;
            filter: drop-shadow(0 6px 18px hsla(240,95%,47%,0.18));
            transition: transform 0.4s var(--ease-expo);
        }
        .school-emblem:hover { transform: scale(1.06) rotate(3deg); }

        .card-header h1 {
            color: var(--text);
            font-size: 1.05rem;
            font-weight: 700;
            line-height: 1.55;
            letter-spacing: 0.01em;
        }
        .card-header p {
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 500;
            margin-top: 0.3rem;
            letter-spacing: 0.02em;
        }

        /* ── Card Body ── */
        .card-body { padding: 2rem 2.5rem 2.5rem; }

        /* ── Alert ── */
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
        @keyframes shakeX {
            0%,100%{transform:translateX(0);}
            20%    {transform:translateX(-6px);}
            40%    {transform:translateX(5px);}
            60%    {transform:translateX(-4px);}
            80%    {transform:translateX(3px);}
        }

        /* ── Form groups ── */
        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            font-size: 0.74rem;
            font-weight: 700;
            color: var(--text-sub);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm);
            font-family: 'Sarabun', sans-serif;
            font-size: 0.95rem;
            background: hsl(240, 30%, 99%);
            color: var(--text);
            outline: none;
            transition:
                border-color var(--dur) var(--ease-std),
                box-shadow   var(--dur) var(--ease-std),
                background   var(--dur) var(--ease-std);
        }
        .form-group input::placeholder { color: hsl(240,15%,72%); font-size: 0.9rem; }
        .form-group input:hover        { border-color: hsl(240,30%,82%); }
        .form-group input:focus {
            border-color: var(--primary-light);
            background: #fff;
            box-shadow: 0 0 0 3px var(--primary-pale), 0 1px 4px hsla(240,95%,47%,0.08);
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

        /* ── Password wrapper ── */
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }
        .password-wrapper > input[type="password"],
        .password-wrapper > input[type="text"] {
            width: 100% !important;
            padding-right: 3rem !important;
        }
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear,
        input[type="password"]::-webkit-credentials-auto-fill-button,
        input[type="password"]::-webkit-textfield-decoration-container {
            display: none !important;
            visibility: hidden !important;
        }

        .toggle-password {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 0.25rem;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition:
                color       var(--dur) var(--ease-std),
                background  var(--dur) var(--ease-std);
            -webkit-tap-highlight-color: transparent;
        }
        .toggle-password:hover  { color: var(--primary); background: var(--primary-pale); }
        .toggle-password:focus  { outline: none; }
        .toggle-password:active { transform: translateY(-50%) scale(0.85); }
        .toggle-password svg    { pointer-events: none; display: block; }

        /* ── Remember me ── */
        .form-actions { margin-bottom: 1.5rem; }
        .form-check-remember { display: flex; align-items: center; gap: 0.5rem; }
        .form-check-remember input[type="checkbox"] {
            width: 16px; height: 16px; min-width: 16px;
            border: 1.5px solid var(--border);
            border-radius: 4px;
            cursor: pointer;
            accent-color: var(--primary);
        }
        .remember-label {
            font-size: 0.85rem;
            color: var(--text-sub);
            cursor: pointer;
            font-weight: 500;
            user-select: none;
        }

        /* ── Login button ── */
        .btn-login {
            width: 100%;
            padding: 0.875rem 1.5rem;
            border: none;
            border-radius: var(--r-sm);
            font-family: 'Sarabun', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #fff;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg,
                hsl(258,76%,43%) 0%,
                var(--primary) 60%,
                hsl(241,90%,58%) 100%);
            box-shadow:
                0 4px 16px var(--primary-glow),
                0 1px 3px  hsla(240,95%,47%,0.20);
            transition:
                transform    var(--dur) var(--ease-std),
                box-shadow   var(--dur) var(--ease-std);
        }
        /* Shimmer sweep */
        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg,
                transparent 35%,
                hsla(0,0%,100%,0.18) 50%,
                transparent 65%);
            transform: translateX(-100%);
            transition: transform 0.55s var(--ease-std);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow:
                0 8px 28px var(--primary-glow),
                0 2px 6px  hsla(240,95%,47%,0.25);
        }
        .btn-login:hover::after { transform: translateX(100%); }
        .btn-login:active       { transform: translateY(0); box-shadow: 0 3px 10px var(--primary-glow); }

        /* ── Footer note ── */
        .card-footer-note {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.78rem;
            color: var(--text-muted);
            font-weight: 500;
            line-height: 1.6;
        }

        /* ── Responsive ── */
        @media (max-width: 1024px) {
            body { padding: 1.5rem; align-items: flex-start; overflow-y: auto; overflow-x: hidden; }
            .login-card { margin: 3rem auto; }
        }
        @media (max-width: 480px) {
            body { padding: 1rem; }
            .login-card { margin: 1.5rem auto; border-radius: var(--r-md); }
            .card-header { padding: 2rem 1.5rem 1.5rem; }
            .card-body   { padding: 1.5rem 1.5rem 2rem; }
            .school-emblem { width: 68px; }
        }
    </style>
</head>
<body>
    <div class="neon-glow neon-glow-1"></div>
    <div class="neon-glow neon-glow-2"></div>

    <div class="circle-deco"></div>
    <div class="circle-deco"></div>

    <div class="login-card">

        <div class="card-header">
            <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียนศิริราษฎร์สามัคคี" class="school-emblem">
            <h1>ระบบบริหารงานวินัยนักเรียน<br>โรงเรียนศิริราษฎร์สามัคคี</h1>
            <p>จังหวัดปัตตานี</p>
        </div>

        <div class="card-body">
            <h2 class="login-title" style="font-size: 1.25rem; font-weight: 800; color: var(--text); text-align: left; margin-bottom: 1.5rem; letter-spacing: -0.01em;">เข้าสู่ระบบ</h2>
            @if ($errors->any())
                <div class="alert-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="Username">ชื่อผู้ใช้งาน</label>
                    <input
                        type="text"
                        id="Username"
                        name="Username"
                        value="{{ old('Username') }}"
                        class="{{ $errors->has('Username') ? 'is-invalid' : '' }}"
                        autocomplete="username"
                        autofocus
                        placeholder="กรอกชื่อผู้ใช้งาน"
                    >
                    @error('Username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="Password">รหัสผ่าน</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="Password"
                            name="Password"
                            class="{{ $errors->has('Password') ? 'is-invalid' : '' }}"
                            autocomplete="current-password"
                            placeholder="กรอกรหัสผ่าน"
                        >
                        <button type="button" class="toggle-password" id="togglePassword" onclick="togglePasswordVisibility()" title="แสดงรหัสผ่าน" aria-label="แสดง/ซ่อนรหัสผ่าน">
                            <svg id="eye-svg" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path id="eye-path-1" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle id="eye-circle" cx="12" cy="12" r="3"/>
                                <line id="eye-slash" x1="1" y1="1" x2="23" y2="23" style="display:none;"/>
                            </svg>
                        </button>
                    </div>
                    @error('Password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-actions">
                    <div class="form-check-remember">
                        <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="remember-label">จดจำการเข้าสู่ระบบ</label>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    เข้าสู่ระบบ
                </button>
            </form>

            <p class="card-footer-note">
                หากพบปัญหาการเข้าสู่ระบบ กรุณาติดต่อผู้ดูแลระบบ
            </p>
        </div>
    </div>
<script>
    function togglePasswordVisibility() {
        var input  = document.getElementById('Password');
        var slash  = document.getElementById('eye-slash');
        var circle = document.getElementById('eye-circle');
        var btn    = document.getElementById('togglePassword');

        if (input.type === 'password') {
            input.type           = 'text';
            slash.style.display  = 'inline';
            circle.style.display = 'none';
            btn.title            = 'ซ่อนรหัสผ่าน';
        } else {
            input.type           = 'password';
            slash.style.display  = 'none';
            circle.style.display = 'inline';
            btn.title            = 'แสดงรหัสผ่าน';
        }
    }
</script>
</body>
</html>