<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบวินัยนักเรียน') — ศิริราษฎร์สามัคคี</title>
    {{-- Preconnect: เชื่อมต่อ CDN ล่วงหน้าลด latency --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    {{-- Google Fonts: โหลด async ไม่บล็อก render --}}
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet"></noscript>
    {{-- Font Awesome: โหลด async ไม่บล็อก render --}}
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    <style>
        :root {
            /* ===== Core Brand & Gradients ===== */
            --primary:        #0604EA;
            --primary-dark:   #0403b2;
            --primary-light:  #4c4bf7;
            --primary-pale:   rgba(6, 4, 234, 0.05);
            --primary-border: rgba(6, 4, 234, 0.12);
            --primary-gradient: linear-gradient(135deg, #4224B8 0%, #0604EA 100%);
            --sidebar-gradient: linear-gradient(180deg, #0f0e34 0%, #1b1959 100%);
            --white:          #FFFFFF;

            /* ===== Semantic Gradients ===== */
            --red:            #BD2743;
            --red-gradient:   linear-gradient(135deg, #BD2743 0%, #e11d48 100%);
            --green:          #16a34a;
            --green-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --orange:         #F08618;
            --orange-gradient: linear-gradient(135deg, #F08618 0%, #f59e0b 100%);
            --yellow:         #F2C53D;
            --yellow-gradient: linear-gradient(135deg, #F2C53D 0%, #d97706 100%);

            /* ===== Neutral ===== */
            --bg:             #f5f5fc;       /* ขาวอมเทาอมม่วงบางเบา */
            --surface:        #FFFFFF;
            --border:         #e2e2f0;
            --text:           #1e1e38;
            --text-muted:     #6c6c8f;

            /* ===== Layout ===== */
            --sidebar-w: 260px;
            --topbar-h:  65px;
            
            /* ===== Transitions & Shadows ===== */
            --transition:     all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --shadow-sm:      0 2px 8px rgba(6, 4, 234, 0.04);
            --shadow-md:      0 10px 25px -5px rgba(6, 4, 234, 0.05), 0 4px 10px -3px rgba(6, 4, 234, 0.02);
            --shadow-lg:      0 20px 40px -10px rgba(6, 4, 234, 0.08), 0 8px 16px -6px rgba(6, 4, 234, 0.04);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* ================================================
           SIDEBAR
        ================================================ */
        .sidebar {
            width: var(--sidebar-w);
            height: 100vh;           /* ความสูงเต็มหน้าจอ */
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            overflow: hidden;        /* ป้องกัน sidebar ล้นออกไป */
            z-index: 100;
            box-shadow: 4px 0 25px rgba(15, 14, 52, 0.15);

            /* รวมลาย grid และพื้นหลังไล่สีเข้าด้วยกัน */
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px),
                var(--sidebar-gradient);
            background-size: 24px 24px, 24px 24px, 100% 100%;
            background-color: #0f0e34;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .brand-content {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .brand-logo {
            width: 44px;
            height: auto;
            flex-shrink: 0;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.25));
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 0.1rem;
        }

        .sidebar-brand .school-name {
            color: var(--white);
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.02em;
        }

        .sidebar-brand .system-name {
            color: rgba(255,255,255,0.55);
            font-size: 0.7rem;
            font-weight: 400;
            letter-spacing: 0.01em;
        }

        /* Role Badge - Glassmorphism */
        .role-badge {
            margin: 1rem 1.25rem 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            transition: var(--transition);
        }
        
        .role-badge:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255, 255, 255, 0.15);
        }

        .role-badge .user-name {
            color: var(--white);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .role-badge .user-role {
            color: var(--yellow);
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: flex;
            align-items: center;
            gap: 0.35rem;
        }
        
        .role-badge .user-role::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background: var(--yellow);
            border-radius: 50%;
            box-shadow: 0 0 8px var(--yellow);
        }

        /* Nav */
        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
            overflow-y: auto;        /* scroll ได้เมื่อเมนูยาว */
            min-height: 0;           /* จำเป็นสำหรับ flex child ที่ overflow */
        }

        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 10px; }

        .nav-section-title {
            padding: 0.85rem 1.5rem 0.35rem;
            font-size: 0.65rem;
            font-weight: 800;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.7rem 1.5rem;
            color: rgba(255,255,255,0.68);
            text-decoration: none;
            font-size: 0.88rem;
            font-weight: 500;
            transition: var(--transition);
            border-left: 3px solid transparent;
            margin: 2px 0;
            position: relative;
        }

        .nav-item a:hover {
            color: var(--white);
            background: rgba(255,255,255,0.05);
            border-left-color: rgba(255,255,255,0.3);
            padding-left: 1.65rem;
        }

        .nav-item a.active {
            color: var(--white);
            background: rgba(255,255,255,0.08);
            border-left-color: var(--yellow);
            font-weight: 600;
            padding-left: 1.65rem;
        }

        .nav-item a i {
            width: 18px;
            text-align: center;
            font-size: 0.95rem;
            color: rgba(255,255,255,0.5);
            transition: var(--transition);
            flex-shrink: 0;
        }

        .nav-item a:hover i,
        .nav-item a.active i {
            color: var(--yellow);
            opacity: 1;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--yellow-gradient);
            color: #0f0e34;
            font-size: 0.68rem;
            font-family: 'Outfit', sans-serif;
            padding: 0.15rem 0.5rem;
            border-radius: 12px;
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(242, 197, 61, 0.3);
        }

        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;          /* ป้องกันไม่ให้ถูกบีบออกไป */
        }

        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            width: 100%;
            padding: 0.7rem 1rem;
            background: rgba(189,39,67,0.1);
            border: 1px solid rgba(189,39,67,0.25);
            border-radius: 8px;
            color: #ff8fa3;
            font-family: 'Sarabun', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: var(--transition);
        }

        .btn-logout:hover {
            background: var(--red-gradient);
            color: var(--white);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(189,39,67,0.3);
        }

        /* ================================================
           MAIN CONTENT
        ================================================ */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Topbar - Glassmorphic */
        .topbar {
            height: var(--topbar-h);
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 0 rgba(0,0,0,0.02), 0 4px 15px -10px rgba(0,0,0,0.05);
        }

        .topbar-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f0e34;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .topbar-title::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 16px;
            background: var(--primary-gradient);
            border-radius: 2px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .topbar-msg-btn {
            position: relative;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 1.15rem;
            transition: var(--transition);
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f0f0f8;
        }

        .topbar-msg-btn:hover {
            color: var(--primary);
            background: #e5e5f5;
            transform: translateY(-1px);
        }

        .topbar-msg-btn .dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 8px; height: 8px;
            background: var(--red-gradient);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Page content */
        .page-content {
            flex: 1;
            padding: 2rem;
            max-width: 1300px;
            width: 100%;
            margin: 0 auto;
        }

        .page-header { margin-bottom: 2rem; }

        .page-header h2 {
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f0e34;
        }

        .page-header p {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-top: 0.3rem;
        }

        /* ================================================
           CARDS (Rounded & Floating)
        ================================================ */
        .card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
        }

        .card-header-bar {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #fafafc;
        }

        .card-header-bar h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #0f0e34;
        }

        .card-body-pad { padding: 1.5rem; }

        /* ================================================
           STAT CARDS
        ================================================ */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            border-radius: 12px;
            border: 1px solid var(--border);
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
        }

        /* Accent left-border ตาม variant */
        .stat-card.navy::before,
        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.gold::before    { background: var(--yellow-gradient); }
        .stat-card.red::before     { background: var(--red-gradient); }
        .stat-card.green::before   { background: var(--green-gradient); }
        .stat-card.orange::before  { background: var(--orange-gradient); }

        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0,0,0,0.03);
            transition: var(--transition);
        }

        .stat-icon.navy,
        .stat-icon.primary { background: rgba(6,4,234,0.06); color: var(--primary); }
        .stat-icon.gold    { background: rgba(242,197,61,0.08);  color: #c99307; }
        .stat-icon.red     { background: rgba(189,39,67,0.06); color: var(--red); }
        .stat-icon.green   { background: rgba(16,185,129,0.06); color: #059669; }
        .stat-icon.orange  { background: rgba(240,134,24,0.06); color: var(--orange); }

        .stat-info .stat-value {
            font-size: 1.85rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: #0f0e34;
            line-height: 1.1;
        }

        .stat-info .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
            font-weight: 500;
        }

        /* ================================================
           TABLES
        ================================================ */
        .table-wrap { overflow-x: auto; border-radius: 8px; border: 1px solid var(--border); }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        thead th {
            padding: 0.85rem 1.25rem;
            text-align: left;
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            background: #fafafc;
            border-bottom: 1px solid var(--border);
        }

        tbody td {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid #f2f2fa;
            color: var(--text);
            vertical-align: middle;
        }

        tbody tr:last-child td { border-bottom: none; }

        tbody tr:hover td { background: #fafafc; }

        /* ================================================
           BADGES (Gills & Soft Backgrounds)
        ================================================ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.65rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
        }

        .badge-navy,
        .badge-primary { background: rgba(6,4,234,0.06);       color: var(--primary); }
        .badge-gold    { background: rgba(242,197,61,0.09);       color: #925f0e; }
        .badge-red     { background: rgba(189,39,67,0.07);       color: var(--red); }
        .badge-green   { background: rgba(22,163,74,0.07);       color: #14532d; }
        .badge-orange  { background: rgba(240,134,24,0.07);       color: #7c2d12; }
        .badge-gray    { background: rgba(91,91,138,0.06);       color: var(--text-muted); }

        /* ================================================
           BUTTONS (Premium Shadow & Gradient)
        ================================================ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid transparent;
            text-decoration: none;
            transition: var(--transition);
            letter-spacing: 0.01em;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .btn:active {
            transform: translateY(0);
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: var(--white);
            box-shadow: 0 4px 14px rgba(6, 4, 234, 0.25);
        }
        
        .btn-primary:hover {
            box-shadow: 0 6px 20px rgba(6, 4, 234, 0.35);
            color: var(--white);
        }

        /* btn-gold → ขาวมีขอบสีน้ำเงินม่วง */
        .btn-gold {
            background: var(--white);
            color: var(--primary);
            border: 1.5px solid var(--primary);
            box-shadow: 0 2px 6px rgba(6, 4, 234, 0.03);
        }
        .btn-gold:hover {
            background: var(--primary-pale);
        }

        .btn-danger {
            background: var(--red-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(189,39,67,0.25);
        }
        .btn-danger:hover { box-shadow: 0 6px 20px rgba(189,39,67,0.35); }

        .btn-success {
            background: var(--green-gradient);
            color: white;
            box-shadow: 0 4px 14px rgba(16,185,129,0.25);
        }
        .btn-success:hover { box-shadow: 0 6px 20px rgba(16,185,129,0.35); }

        .btn-outline {
            background: transparent;
            border: 1.5px solid var(--border);
            color: var(--text-muted);
        }
        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-pale);
        }

        .btn-sm {
            padding: 0.4rem 0.85rem;
            font-size: 0.8rem;
            border-radius: 6px;
        }

        /* ================================================
           FORMS (Rounded Controls)
        ================================================ */
        .form-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f0e34;
            margin-bottom: 0.45rem;
            letter-spacing: 0.02em;
        }

        .form-control {
            width: 100%;
            padding: 0.65rem 1rem;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            font-family: 'Sarabun', sans-serif;
            font-size: 0.92rem;
            background: white;
            color: var(--text);
            outline: none;
            transition: var(--transition);
        }

        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 4px rgba(6, 4, 234, 0.08);
        }

        .form-control.is-invalid { border-color: var(--red); }

        .invalid-feedback {
            color: var(--red);
            font-size: 0.8rem;
            margin-top: 0.35rem;
            font-weight: 500;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .form-group { margin-bottom: 1.25rem; }

        /* ================================================
           ALERTS (Premium Border Left & Icons)
        ================================================ */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 1.25rem;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            box-shadow: var(--shadow-sm);
        }

        .alert-success {
            background: rgba(16,185,129,0.06);
            border-left: 4px solid var(--green);
            color: #064e3b;
        }
        .alert-danger {
            background: rgba(189,39,67,0.06);
            border-left: 4px solid var(--red);
            color: #5c0f1e;
        }
        .alert-warning {
            background: rgba(240,134,24,0.06);
            border-left: 4px solid var(--orange);
            color: #451a03;
        }
        .alert-info {
            background: rgba(6,4,234,0.05);
            border-left: 4px solid var(--primary);
            color: #0c0a3e;
        }

        /* ================================================
           PAGINATION
        ================================================ */
        .pagination {
            display: flex;
            gap: 0.35rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .pagination .page-link {
            padding: 0.45rem 0.85rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.85rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            transition: var(--transition);
        }

        .pagination .page-link:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-pale);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-gradient);
            border-color: transparent;
            color: white;
            box-shadow: 0 4px 10px rgba(6, 4, 234, 0.2);
        }

        /* ================================================
           SCORE BAR
        ================================================ */
        .score-bar {
            width: 90px;
            height: 8px;
            background: #e8e8f2;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
            vertical-align: middle;
            margin-left: 0.5rem;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        .score-bar-fill {
            height: 100%;
            border-radius: 4px;
            background: var(--green-gradient);
            transition: width 0.4s ease-out;
        }

        .score-bar-fill.medium { background: var(--orange-gradient); }
        .score-bar-fill.low    { background: var(--red-gradient); }

        /* ================================================
           TOGGLE SWITCH (สำหรับ Permissions)
        ================================================ */
        .toggle-switch {
            position: relative;
            display: inline-flex;
            align-items: center;
            cursor: pointer;
        }

        .toggle-switch input { display: none; }

        .toggle-track {
            width: 44px;
            height: 24px;
            background: #dcdcf2;
            border-radius: 12px;
            transition: var(--transition);
            position: relative;
        }

        .toggle-track::after {
            content: '';
            position: absolute;
            top: 3px; left: 3px;
            width: 18px; height: 18px;
            background: white;
            border-radius: 50%;
            transition: var(--transition);
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        }

        .toggle-switch input:checked + .toggle-track {
            background: var(--primary-gradient);
        }

        .toggle-switch input:checked + .toggle-track::after {
            transform: translateX(20px);
        }

        /* Mobile Toggle Button */
        .sidebar-toggle {
            display: none;
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 1.25rem;
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .sidebar-toggle:hover {
            background: #e5e5f5;
            color: var(--primary);
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 14, 52, 0.4);
            backdrop-filter: blur(4px);
            z-index: 99;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        
        .sidebar-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* Responsive Grid Utilities */
        .responsive-grid-dashboard {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 1rem;
            align-items: start;
        }
        .responsive-grid-student {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: start;
        }
        .responsive-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .responsive-grid-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: inline-flex;
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-wrapper {
                margin-left: 0 !important;
            }
            
            .sidebar-overlay {
                display: block;
            }
            
            .page-content {
                padding: 1.25rem;
            }
            
            .topbar {
                padding: 0 1.25rem;
            }
            
            .stat-grid {
                grid-template-columns: 1fr;
            }

            .responsive-grid-dashboard,
            .responsive-grid-student,
            .responsive-grid-2,
            .responsive-grid-3 {
                grid-template-columns: 1fr !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- SIDEBAR --}}
    @auth
    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="brand-content">
                <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียนศิริราษฎร์สามัคคี" class="brand-logo">
                <div class="brand-text">
                    <div class="school-name">โรงเรียนศิริราษฎร์สามัคคี</div>
                    <div class="system-name">ระบบบริหารงานวินัยนักเรียน</div>
                </div>
            </div>
        </div>

        <div class="role-badge">
            <div class="user-name">{{ auth()->user()->FullName }}</div>
            <div class="user-role">{{ auth()->user()->Role === 'ครู' ? 'ครูประจำชั้น' : auth()->user()->Role }}</div>
        </div>

        <nav class="sidebar-nav">
            @include('layouts.partials.nav-' . match(strtolower(auth()->user()->Role)) {
                'ผู้ดูแลระบบ', 'admin' => 'admin',
                'ฝ่ายปกครอง', 'discipline' => 'discipline',
                'ครู', 'teacher' => 'teacher',
                'นักเรียน', 'student' => 'student',
                'ผู้ปกครอง', 'parent' => 'parent',
                default        => 'admin',
            })
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    ออกจากระบบ
                </button>
            </form>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    @endauth

    {{-- MAIN --}}
    <div class="main-wrapper" @guest style="margin-left: 0;" @endguest>
        @auth
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <button class="sidebar-toggle" id="sidebarToggle" title="เมนู">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="topbar-title">@yield('page-title', 'แดชบอร์ด')</span>
            </div>
            <div class="topbar-right">
                @php
                    $msgRoute = match(auth()->user()->Role) {
                        'ผู้ดูแลระบบ' => route('admin.messages.index'),
                        'ฝ่ายปกครอง'  => route('discipline.messages.index'),
                        'ครู'          => route('teacher.messages.index'),
                        'นักเรียน'     => route('student.messages.index'),
                        'ผู้ปกครอง'    => route('parent.messages.index'),
                        default        => '#',
                    };
                @endphp
                <a href="{{ $msgRoute }}" class="topbar-msg-btn" title="ข้อความ">
                    <i class="fas fa-envelope"></i>
                    <span class="dot"></span>
                </a>
                <span style="font-size:0.8rem; color:var(--text-muted);">
                    {{ now()->locale('th')->isoFormat('D MMM YYYY') }}
                </span>
            </div>
        </header>
        @endauth

        <main class="page-content">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggleBtn && sidebar && overlay) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    overlay.classList.toggle('show');
                });

                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html>