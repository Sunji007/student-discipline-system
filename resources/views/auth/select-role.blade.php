<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>โปรดเลือกระบบที่ต้องการ — โรงเรียนศิริราษฎร์สามัคคี</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        :root {
            --primary: #0604EA;
            --primary-dark: #0403b2;
            --primary-gradient: linear-gradient(135deg, #4224B8 0%, #0604EA 100%);
            --navy-dark: #0f0e34;
            --text-dark: #1e293b;
            --text-sub: #64748b;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #F8FAFC;
            background-image: 
                radial-gradient(at 10% 10%, rgba(6, 4, 234, 0.05) 0px, transparent 50%),
                radial-gradient(at 90% 90%, rgba(66, 36, 184, 0.07) 0px, transparent 50%);
            padding: 2rem 1rem;
            color: var(--text-dark);
        }

        .select-role-container {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo {
            width: 72px;
            height: auto;
            margin-bottom: 0.75rem;
            filter: drop-shadow(0 4px 10px rgba(6, 4, 234, 0.15));
        }

        .school-title {
            font-size: 1.3rem;
            font-weight: 800;
            color: #0f0e34;
            margin-bottom: 0.25rem;
        }

        .system-subtitle {
            font-size: 0.85rem;
            color: var(--text-sub);
        }

        .user-greeting {
            display: inline-block;
            background: #EEF2FF;
            color: #3730A3;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin-top: 0.85rem;
            border: 1px solid #C7D2FE;
        }

        .role-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px -10px rgba(15, 14, 52, 0.12), 0 8px 16px -6px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid #E2E8F0;
        }

        .card-banner {
            background: var(--primary-gradient);
            color: white;
            padding: 1.35rem 1.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .banner-check-icon {
            width: 28px;
            height: 28px;
            border: 2px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: bold;
            flex-shrink: 0;
            color: #F59E0B;
        }

        .banner-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            color: white;
            letter-spacing: 0.01em;
        }

        .card-body {
            padding: 2.25rem 2rem 2.5rem;
        }

        .role-options-list {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 2.25rem;
        }

        .role-radio-item {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            font-size: 1.05rem;
            color: #334155;
            font-weight: 500;
            cursor: pointer;
            padding: 0.75rem 1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            border: 1.5px solid #E2E8F0;
            background: #F8FAFC;
        }

        .role-radio-item:hover {
            background: #EEF2FF;
            border-color: #A5B4FC;
            color: #1E1B4B;
        }

        .role-radio-item input[type="radio"] {
            width: 20px;
            height: 20px;
            accent-color: #0604EA;
            cursor: pointer;
        }

        .role-radio-item:has(input[type="radio"]:checked) {
            background: #EEF2FF;
            border-color: #6366F1;
            font-weight: 700;
            color: #312E81;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.12);
        }

        .btn-submit-role {
            width: 100%;
            background: var(--primary-gradient);
            color: white;
            border: none;
            padding: 0.85rem 1.5rem;
            font-size: 1.05rem;
            font-weight: 700;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 15px rgba(6, 4, 234, 0.3);
            font-family: inherit;
        }

        .btn-submit-role:hover {
            opacity: 0.95;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(6, 4, 234, 0.4);
        }

        .btn-submit-role:active {
            transform: translateY(0);
        }

        .footer-logout {
            margin-top: 1.75rem;
            text-align: center;
        }

        .footer-logout a {
            color: var(--text-sub);
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .footer-logout a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>

<div class="select-role-container">
    {{-- Brand Header --}}
    <div class="brand-header">
        <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียนศิริราษฎร์สามัคคี" class="brand-logo">
        <div class="school-title">โรงเรียนศิริราษฎร์สามัคคี</div>
        <div class="system-subtitle">ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน</div>
        <div class="user-greeting">
            <i class="fas fa-user-circle"></i> ยินดีต้อนรับ คุณ {{ $user->FullName }}
        </div>
    </div>

    {{-- Role Selection Card --}}
    <div class="role-card">
        <div class="card-banner">
            <div class="banner-check-icon">✓</div>
            <h1 class="banner-title">โปรดเลือกระบบที่ต้องการ</h1>
        </div>

        <div class="card-body">
            @if(session('error'))
                <div style="background: #FEF2F2; color: #991B1B; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem; border: 1px solid #FCA5A5;">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="">
                @csrf
                <div class="role-options-list">
                    @php
                        $roleLabels = [
                            'ครู' => 'ครูประจำชั้น',
                            'ฝ่ายปกครอง' => 'ฝ่ายปกครอง',
                            'ผู้ปกครอง' => 'ผู้ปกครอง',
                            'ผู้ดูแลระบบ' => 'ผู้ดูแลระบบ',
                            'นักเรียน' => 'นักเรียน'
                        ];
                    @endphp

                    @foreach($roles as $role)
                        @php
                            $label = $roleLabels[$role] ?? $role;
                        @endphp
                        <label class="role-radio-item">
                            <input type="radio" name="role" value="{{ $role }}" {{ $loop->first ? 'checked' : '' }}>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <button type="submit" class="btn-submit-role">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>
    </div>

    {{-- Logout Link --}}
    <div class="footer-logout">
        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> ออกจากระบบ / เปลี่ยนบัญชีผู้ใช้
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleForm = document.querySelector('form[method="POST"]:not(#logout-form)');
    if (roleForm) {
        roleForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = roleForm.querySelector('.btn-submit-role');
            if (btn) { btn.disabled = true; btn.innerText = 'กำลังดำเนินการ...'; }

            const formData = new FormData(roleForm);

            fetch(roleForm.action || window.location.href, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            }).then(async res => {
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    window.location.reload();
                }
            }).catch(err => {
                roleForm.submit();
            });
        });
    }
});
</script>
</body>
</html>
