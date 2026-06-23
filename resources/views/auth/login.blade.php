<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ — โรงเรียนศิริราษฎร์สามัคคี</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- โหลด font async ไม่บล็อก render --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
        <style>
        :root {
            --primary:       #0604EA;
            --primary-dark:  #0403b2;
            --primary-light: #4c4bf7;
            --primary-pale:  rgba(6, 4, 234, 0.06);
            --primary-gradient: linear-gradient(135deg, #4224B8 0%, #0604EA 100%);
            --white:         #FFFFFF;
            --off-white:     #f7f7fa;
            --red:           #BD2743;
            --text:          #0d0d2b;
            --text-muted:    #6366a0;
            --border:        #e2e2f0;
            --transition:    all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #0f0e34 0%, #1c1a5e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* ลาย grid พื้นหลังเรืองแสง */
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
        }
        
        /* วงเรืองแสงนีออนพื้นหลัง */
        .neon-glow {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.15;
            pointer-events: none;
            z-index: 1;
        }
        .neon-glow-1 {
            width: 500px;
            height: 500px;
            background: #4224B8;
            top: -200px;
            right: -100px;
        }
        .neon-glow-2 {
            width: 400px;
            height: 400px;
            background: #E51DE8;
            bottom: -150px;
            left: -100px;
        }

        /* วงกลมเส้นตกแต่ง */
        .circle-deco {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.06);
            pointer-events: none;
            z-index: 2;
        }
        .circle-deco:nth-child(1) { width: 700px; height: 700px; top: -250px; right: -150px; }
        .circle-deco:nth-child(2) { width: 450px; height: 450px; bottom: -150px; left: -100px; }

        .login-card {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            width: 100%;
            max-width: 440px;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.5);
            box-shadow:
                0 20px 50px rgba(0,0,0,0.3),
                0 0 0 1px rgba(255,255,255,0.05);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }



        .card-header {
            padding: 2.5rem 2.5rem 1.75rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
            position: relative;
        }

        .school-emblem {
            width: 85px;
            height: auto;
            margin: 0 auto 1.25rem;
            display: block;
            filter: drop-shadow(0 4px 12px rgba(6, 4, 234, 0.15));
            transition: var(--transition);
        }
        
        .school-emblem:hover {
            transform: scale(1.05) rotate(2deg);
        }

        .card-header h1 {
            color: #0f0e34;
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            line-height: 1.5;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.82rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .card-body { padding: 2rem 2.5rem 2.5rem; }

        .form-group { margin-bottom: 1.25rem; }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f0e34;
            letter-spacing: 0.03em;
            margin-bottom: 0.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 0.95rem;
            background: white;
            color: var(--text);
            transition: var(--transition);
            outline: none;
        }

        .form-group input:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(6, 4, 234, 0.08);
        }

        .form-group input.is-invalid {
            border-color: var(--red);
        }

        .invalid-feedback {
            color: var(--red);
            font-size: 0.8rem;
            margin-top: 0.35rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(189,39,67,0.06);
            border: 1px solid rgba(189,39,67,0.2);
            border-left: 4px solid var(--red);
            color: var(--red);
            padding: 0.8rem 1rem;
            border-radius: 8px;
            font-size: 0.88rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        .form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.75rem;
        }

        .form-check-remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-check-remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .remember-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
            font-weight: 500;
            transition: var(--transition);
        }
        
        .form-check-remember input[type="checkbox"]:checked ~ .remember-label {
            color: #0f0e34;
            font-weight: 600;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            transition: var(--transition);
        }

        .forgot-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 0.85rem;
            background: var(--primary-gradient);
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 16px rgba(6, 4, 234, 0.25);
        }

        .btn-login:hover {
            box-shadow: 0 6px 22px rgba(6, 4, 234, 0.35);
            transform: translateY(-1px);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }

        .card-footer-note {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 500;
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
                    <input
                        type="password"
                        id="Password"
                        name="Password"
                        class="{{ $errors->has('Password') ? 'is-invalid' : '' }}"
                        autocomplete="current-password"
                        placeholder="กรอกรหัสผ่าน"
                    >
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
</body>
</html>