<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน') — ศิริราษฎร์สามัคคี</title>
    <link rel="icon" href="{{ asset('images/logo.png') }}" type="image/png">
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
    {{-- SweetAlert2 สำหรับแจ้งเตือนแบบโมเดิร์น --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Flatpickr CSS/JS for Buddhist Era Datepicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        // Global Buddhist Era Timepicker Initializer
        window.initBETimepicker = function(selector, initialValueReal) {
            const inputDisplay = document.querySelector(selector + "_display");
            const inputReal = document.querySelector(selector + "_real");
            
            if (!inputDisplay || !inputReal) return;

            // Load initial date (if any)
            let defaultDate = null;
            if (initialValueReal) {
                let parseable = initialValueReal;
                if (!parseable.includes('T') && parseable.includes(' ')) {
                    parseable = parseable.replace(' ', 'T');
                }
                defaultDate = new Date(parseable);
                if (isNaN(defaultDate.getTime())) {
                    defaultDate = new Date();
                }
            } else {
                defaultDate = new Date();
            }

            const fp = flatpickr(inputDisplay, {
                enableTime: true,
                time_24hr: true,
                dateFormat: "d/m/Y H:i",
                defaultDate: defaultDate,
                locale: "th",
                formatDate: (date, format, locale) => {
                    const day = ("0" + date.getDate()).slice(-2);
                    const month = ("0" + (date.getMonth() + 1)).slice(-2);
                    const yearBE = date.getFullYear() + 543;
                    const hours = ("0" + date.getHours()).slice(-2);
                    const minutes = ("0" + date.getMinutes()).slice(-2);
                    return `${day}/${month}/${yearBE} ${hours}:${minutes}`;
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        const d = selectedDates[0];
                        const y = d.getFullYear();
                        const m = ("0" + (d.getMonth() + 1)).slice(-2);
                        const day = ("0" + d.getDate()).slice(-2);
                        const h = ("0" + d.getHours()).slice(-2);
                        const min = ("0" + d.getMinutes()).slice(-2);
                        inputReal.value = `${y}-${m}-${day} ${h}:${min}`;
                    } else {
                        inputReal.value = "";
                    }
                },
                onReady: function(selectedDates, dateStr, instance) {
                    updateBEYearHeader(instance);
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    updateBEYearHeader(instance);
                },
                onMonthChange: function(selectedDates, dateStr, instance) {
                    setTimeout(() => updateBEYearHeader(instance), 0);
                },
                onYearChange: function(selectedDates, dateStr, instance) {
                    setTimeout(() => updateBEYearHeader(instance), 0);
                }
            });

            inputDisplay.addEventListener('click', function() { fp.open(); });
            if (inputDisplay.parentElement) {
                inputDisplay.parentElement.addEventListener('click', function() { fp.open(); });
            }

            function updateBEYearHeader(instance) {
                if (instance.currentYearElement) {
                    instance.currentYearElement.style.display = 'none';
                    if (instance.currentYearElement.nextSibling && instance.currentYearElement.nextSibling.className === 'arrowUp') {
                        instance.currentYearElement.parentNode.style.display = 'none';
                    }
                    
                    let container = instance.currentYearElement.parentNode.parentNode;
                    let beYearSpan = container.querySelector('.be-year-display');
                    if (!beYearSpan) {
                        beYearSpan = document.createElement('span');
                        beYearSpan.className = 'be-year-display';
                        beYearSpan.style.fontWeight = '600';
                        beYearSpan.style.fontSize = '1.05rem';
                        beYearSpan.style.marginLeft = '4px';
                        beYearSpan.style.color = '#374151';
                        container.appendChild(beYearSpan);
                    }
                    beYearSpan.textContent = parseInt(instance.currentYear) + 543;
                }
            }
        };

        // Global Buddhist Era Datepicker Initializer (Date only)
        window.initBEDatepicker = function(selector, initialValueReal) {
            const inputDisplay = document.querySelector(selector + "_display");
            const inputReal = document.querySelector(selector + "_real");
            
            if (!inputDisplay || !inputReal) return;

            // Load initial date (if any)
            let defaultDate = null;
            if (initialValueReal) {
                defaultDate = new Date(initialValueReal);
                if (isNaN(defaultDate.getTime())) {
                    defaultDate = new Date();
                }
            } else {
                defaultDate = new Date();
            }

            const fp = flatpickr(inputDisplay, {
                enableTime: false,
                dateFormat: "d/m/Y",
                defaultDate: defaultDate,
                locale: "th",
                formatDate: (date, format, locale) => {
                    const day = ("0" + date.getDate()).slice(-2);
                    const month = ("0" + (date.getMonth() + 1)).slice(-2);
                    const yearBE = date.getFullYear() + 543;
                    return `${day}/${month}/${yearBE}`;
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates[0]) {
                        const d = selectedDates[0];
                        const y = d.getFullYear();
                        const m = ("0" + (d.getMonth() + 1)).slice(-2);
                        const day = ("0" + d.getDate()).slice(-2);
                        inputReal.value = `${y}-${m}-${day}`;
                    } else {
                        inputReal.value = "";
                    }
                },
                onReady: function(selectedDates, dateStr, instance) {
                    updateBEYearHeader(instance);
                },
                onOpen: function(selectedDates, dateStr, instance) {
                    updateBEYearHeader(instance);
                },
                onMonthChange: function(selectedDates, dateStr, instance) {
                    setTimeout(() => updateBEYearHeader(instance), 0);
                },
                onYearChange: function(selectedDates, dateStr, instance) {
                    setTimeout(() => updateBEYearHeader(instance), 0);
                }
            });

            inputDisplay.addEventListener('click', function() { fp.open(); });
            if (inputDisplay.parentElement) {
                inputDisplay.parentElement.addEventListener('click', function() { fp.open(); });
            }

            function updateBEYearHeader(instance) {
                if (instance.currentYearElement) {
                    instance.currentYearElement.style.display = 'none';
                    if (instance.currentYearElement.nextSibling && instance.currentYearElement.nextSibling.className === 'arrowUp') {
                        instance.currentYearElement.parentNode.style.display = 'none';
                    }
                    
                    let container = instance.currentYearElement.parentNode.parentNode;
                    let beYearSpan = container.querySelector('.be-year-display');
                    if (!beYearSpan) {
                        beYearSpan = document.createElement('span');
                        beYearSpan.className = 'be-year-display';
                        beYearSpan.style.fontWeight = '600';
                        beYearSpan.style.fontSize = '1.05rem';
                        beYearSpan.style.marginLeft = '4px';
                        beYearSpan.style.color = '#374151';
                        container.appendChild(beYearSpan);
                    }
                    beYearSpan.textContent = parseInt(instance.currentYear) + 543;
                }
            }
        };
    </script>
    <script>
        (function() {
            try {
                const isCollapsed = localStorage.getItem('sidebar_collapsed');
                if (isCollapsed === null || isCollapsed === '1') {
                    document.documentElement.classList.add('sidebar-collapsed-preload');
                    document.addEventListener('DOMContentLoaded', function() {
                        document.body.classList.add('sidebar-collapsed');
                    });
                }
            } catch (e) {}
        })();
    </script>

    <style>
        /* SweetAlert2 Theme Adjustments */
        .swal2-popup {
            font-family: 'Sarabun', 'Outfit', sans-serif !important;
            border-radius: 12px !important;
            padding: 1.75rem !important;
        }
        .swal2-title {
            font-size: 1.25rem !important;
            font-weight: 700 !important;
            color: #0f0e34 !important;
        }
        .swal2-html-container {
            font-size: 0.9rem !important;
            color: #4b5563 !important;
        }
        .swal2-confirm.btn-swal-confirm {
            background: var(--primary-gradient) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(6, 4, 234, 0.25) !important;
            border-radius: 8px !important;
            padding: 0.6rem 1.5rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            border: none !important;
        }
        .swal2-confirm.btn-swal-danger {
            background: var(--red-gradient) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(189, 39, 67, 0.25) !important;
            border-radius: 8px !important;
            padding: 0.6rem 1.5rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            border: none !important;
        }
        .swal2-confirm.btn-swal-success {
            background: var(--green-gradient) !important;
            color: #fff !important;
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.25) !important;
            border-radius: 8px !important;
            padding: 0.6rem 1.5rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
            border: none !important;
        }
        .swal2-cancel.btn-swal-cancel {
            background: #f3f4f6 !important;
            color: #374151 !important;
            border: 1.5px solid #e5e7eb !important;
            border-radius: 8px !important;
            padding: 0.6rem 1.5rem !important;
            font-size: 0.88rem !important;
            font-weight: 600 !important;
        }

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

            --purple:         #7c3aed;
            --purple-gradient: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
            
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
            overflow-x: hidden;
            overflow-y: auto;        /* ป้องกัน sidebar ล้นออกไป */
            z-index: 100;
            box-shadow: 4px 0 25px rgba(15, 14, 52, 0.15);

            /* รวมลาย grid และพื้นหลังไล่สีเข้าด้วยกัน */
            background-image:
                linear-gradient(rgba(255,255,255,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.015) 1px, transparent 1px),
                var(--sidebar-gradient);
            background-size: 24px 24px, 24px 24px, 100% 100%;
            background-color: #0f0e34;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.25rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            transition: padding 0.25s ease;
        }

        .brand-content {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            transition: all 0.25s ease;
        }

        .brand-logo {
            width: 50px;
            height: auto;
            flex-shrink: 0;
            filter: drop-shadow(0 4px 10px rgba(0,0,0,0.25));
            transition: all 0.25s ease;
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
            flex: 1;
            transition: opacity 0.2s ease;
        }

        .sidebar-brand .school-name {
            color: var(--white);
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: 0.01em;
        }

        .sidebar-brand .system-name {
            color: rgba(255,255,255,0.65);
            font-size: 0.68rem;
            font-weight: 400;
            line-height: 1.35;
            letter-spacing: 0.01em;
            white-space: normal;
            word-break: normal;
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            white-space: nowrap;
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
            white-space: nowrap;
            overflow: hidden;
            transition: all 0.25s ease;
        }

        /* ── Nav items (Impeccable v2) ── */
        .nav-item a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 1rem 0.65rem 1.25rem;
            margin: 2px 0.75rem;
            color: rgba(255,255,255,0.62);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 10px;
            position: relative;
            transition: all 0.2s cubic-bezier(0.4,0,0.2,1);
            white-space: nowrap;
        }

        .nav-item a i {
            width: 20px;
            text-align: center;
            font-size: 1.05rem;
            color: rgba(255,255,255,0.7);
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .nav-item a .nav-text {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: opacity 0.2s ease;
        }

        .nav-item a:hover {
            color: var(--white);
            background: rgba(255,255,255,0.07);
        }

        .nav-item a:hover i { color: #fff; }

        /* Impeccable active: indigo pill + left accent */
        .nav-item a.active {
            color: var(--white);
            background: rgba(6, 4, 234, 0.80);
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(6,4,234,0.35), inset 0 1px 0 rgba(255,255,255,0.12);
        }

        .nav-item a.active i { color: #fff; opacity: 1; }

        /* Left accent bar for active */
        .nav-item a.active::before {
            content: '';
            position: absolute;
            left: -0.75rem;
            top: 25%; bottom: 25%;
            width: 3px;
            border-radius: 0 3px 3px 0;
            background: #fff;
            opacity: 0.9;
        }

        .nav-badge {
            margin-left: auto;
            background: var(--orange-gradient);
            color: #fff;
            font-size: 0.65rem;
            font-family: 'Outfit', sans-serif;
            padding: 0.12rem 0.45rem;
            border-radius: 10px;
            font-weight: 800;
            box-shadow: 0 2px 6px rgba(240,134,24,0.35);
        }

        .sidebar-footer {
            padding: 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            flex-shrink: 0;
            transition: all 0.25s ease;
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
            white-space: nowrap;
            position: relative;
        }

        .btn-logout:hover {
            background: var(--red-gradient);
            color: var(--white);
            border-color: transparent;
            box-shadow: 0 4px 12px rgba(189,39,67,0.3);
        }

        /* ============================================================
           SIDEBAR COLLAPSED (Mini Sidebar / Icons & Logo Only)
        ============================================================ */
        @media (min-width: 769px) {
            body.sidebar-collapsed .sidebar,
            html.sidebar-collapsed-preload body .sidebar {
                width: 78px;
            }
            body.sidebar-collapsed .main-wrapper,
            html.sidebar-collapsed-preload body .main-wrapper {
                margin-left: 78px;
            }

            body.sidebar-collapsed .sidebar-brand,
            html.sidebar-collapsed-preload body .sidebar-brand {
                padding: 1.25rem 0.5rem;
                display: flex;
                justify-content: center;
            }
            body.sidebar-collapsed .brand-content,
            html.sidebar-collapsed-preload body .brand-content {
                justify-content: center;
                gap: 0;
                width: 100%;
            }
            body.sidebar-collapsed .brand-logo,
            html.sidebar-collapsed-preload body .brand-logo {
                width: 44px;
            }
            body.sidebar-collapsed .brand-text,
            html.sidebar-collapsed-preload body .brand-text {
                display: none !important;
                opacity: 0;
            }

            /* Role Badge in Collapsed mode */
            body.sidebar-collapsed .role-badge,
            html.sidebar-collapsed-preload body .role-badge {
                margin: 0.75rem 0.5rem;
                padding: 0.6rem 0.25rem;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
            }
            body.sidebar-collapsed .role-badge .user-name,
            body.sidebar-collapsed .role-badge .user-username,
            body.sidebar-collapsed .role-badge .user-role-container,
            html.sidebar-collapsed-preload body .role-badge .user-name,
            html.sidebar-collapsed-preload body .role-badge .user-username,
            html.sidebar-collapsed-preload body .role-badge .user-role-container {
                display: none !important;
            }
            body.sidebar-collapsed .role-badge::after,
            html.sidebar-collapsed-preload body .role-badge::after {
                content: '\f007';
                font-family: 'Font Awesome 6 Free';
                font-weight: 900;
                color: var(--yellow);
                font-size: 1.15rem;
            }

            /* Section Title in Collapsed mode */
            body.sidebar-collapsed .nav-section-title,
            html.sidebar-collapsed-preload body .nav-section-title {
                height: 1px;
                padding: 0;
                margin: 0.75rem 0.75rem;
                background: rgba(255,255,255,0.08);
                font-size: 0;
                overflow: hidden;
            }

            /* Nav Item in Collapsed mode */
            body.sidebar-collapsed .nav-item a,
            html.sidebar-collapsed-preload body .nav-item a {
                justify-content: center;
                padding: 0.75rem 0;
                margin: 4px 0.6rem;
                gap: 0;
            }
            body.sidebar-collapsed .nav-item a .nav-text,
            html.sidebar-collapsed-preload body .nav-item a .nav-text {
                display: none !important;
            }
            body.sidebar-collapsed .nav-item a i,
            html.sidebar-collapsed-preload body .nav-item a i {
                font-size: 1.22rem;
                margin: 0;
                width: auto;
            }
            body.sidebar-collapsed .nav-item a.active::before,
            html.sidebar-collapsed-preload body .nav-item a.active::before {
                left: -0.6rem;
            }

            /* Sleek Floating Tooltip on Hover in Collapsed mode */
            body.sidebar-collapsed .nav-item a:hover::after {
                content: attr(data-title);
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background: #0f0e34;
                color: #ffffff;
                padding: 0.45rem 0.85rem;
                border-radius: 6px;
                font-size: 0.82rem;
                font-weight: 500;
                white-space: nowrap;
                z-index: 9999;
                box-shadow: 0 4px 20px rgba(0,0,0,0.35);
                border: 1px solid rgba(255,255,255,0.12);
                pointer-events: none;
                animation: tooltipFadeIn 0.15s ease;
            }
            body.sidebar-collapsed .nav-item a:hover::before {
                content: '';
                position: absolute;
                left: calc(100% + 6px);
                top: 50%;
                transform: translateY(-50%);
                border-width: 5px 6px 5px 0;
                border-style: solid;
                border-color: transparent #0f0e34 transparent transparent;
                z-index: 9999;
            }
            @keyframes tooltipFadeIn {
                from { opacity: 0; transform: translateY(-50%) translateX(-4px); }
                to   { opacity: 1; transform: translateY(-50%) translateX(0); }
            }

            /* Logout Button in Collapsed mode */
            body.sidebar-collapsed .sidebar-footer,
            html.sidebar-collapsed-preload body .sidebar-footer {
                padding: 0.75rem 0.5rem;
            }
            body.sidebar-collapsed .btn-logout,
            html.sidebar-collapsed-preload body .btn-logout {
                padding: 0.65rem 0;
                justify-content: center;
            }
            body.sidebar-collapsed .btn-logout .logout-text,
            html.sidebar-collapsed-preload body .btn-logout .logout-text {
                display: none !important;
            }
            body.sidebar-collapsed .btn-logout i,
            html.sidebar-collapsed-preload body .btn-logout i {
                font-size: 1.15rem;
                margin: 0;
            }
            body.sidebar-collapsed .btn-logout:hover::after {
                content: 'ออกจากระบบ';
                position: absolute;
                left: calc(100% + 12px);
                top: 50%;
                transform: translateY(-50%);
                background: #bd2743;
                color: #ffffff;
                padding: 0.45rem 0.85rem;
                border-radius: 6px;
                font-size: 0.82rem;
                font-weight: 500;
                white-space: nowrap;
                z-index: 9999;
                box-shadow: 0 4px 20px rgba(0,0,0,0.35);
                pointer-events: none;
            }
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

        /* ── Stat Cards (Impeccable v2) ── */
        .stat-card {
            background: var(--white);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 1.5rem 1.5rem 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(6,4,234,0.04), 0 1px 3px rgba(0,0,0,0.03);
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1), box-shadow 0.25s cubic-bezier(0.4,0,0.2,1);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(6,4,234,0.08), 0 4px 10px rgba(0,0,0,0.04);
            border-color: rgba(6,4,234,0.10);
        }

        /* Subtle shimmer bg on hover */
        .stat-card::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0) 60%, rgba(6,4,234,0.025) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }
        .stat-card:hover::after { opacity: 1; }

        /* Accent left-border */
        .stat-card::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 4px;
            border-radius: 0 2px 2px 0;
        }
        .stat-card.navy::before,
        .stat-card.primary::before { background: var(--primary-gradient); }
        .stat-card.gold::before    { background: var(--yellow-gradient); }
        .stat-card.red::before     { background: var(--red-gradient); }
        .stat-card.green::before   { background: var(--green-gradient); }
        .stat-card.orange::before  { background: var(--orange-gradient); }
        .stat-card.purple::before  { background: var(--purple-gradient); }

        /* Gradient icon square (Impeccable v2) */
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
            transition: transform 0.25s ease;
        }
        .stat-card:hover .stat-icon { transform: scale(1.08) rotate(-3deg); }

        .stat-icon.navy,
        .stat-icon.primary {
            background: linear-gradient(135deg, #4c4bf7 0%, #0604EA 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(6,4,234,0.28);
        }
        .stat-icon.gold {
            background: linear-gradient(135deg, #F2C53D 0%, #d97706 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(242,197,61,0.28);
        }
        .stat-icon.red {
            background: linear-gradient(135deg, #e11d48 0%, #BD2743 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(189,39,67,0.28);
        }
        .stat-icon.green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(16,185,129,0.28);
        }
        .stat-icon.orange {
            background: linear-gradient(135deg, #F08618 0%, #f59e0b 100%);
            color: #fff;
            box-shadow: 0 4px 14px rgba(240,134,24,0.28);
        }
        .stat-icon.purple {
            background: var(--purple-gradient);
            color: #fff;
            box-shadow: 0 4px 14px rgba(124,58,237,0.28);
        }

        .stat-info { flex: 1; min-width: 0; }

        .stat-info .stat-value {
            font-size: 1.9rem;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            color: #0f0e34;
            line-height: 1.1;
            letter-spacing: -0.02em;
        }

        .stat-info .stat-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 0.3rem;
            font-weight: 500;
        }

        /* Trend badge (เพิ่มใน v2) */
        .stat-trend {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            font-size: 0.72rem;
            font-weight: 600;
            margin-top: 0.4rem;
            padding: 0.15rem 0.5rem;
            border-radius: 20px;
        }
        .stat-trend.up   { background: rgba(16,185,129,0.10); color: #059669; }
        .stat-trend.down { background: rgba(189,39,67,0.08);  color: #BD2743; }
        .stat-trend.neu  { background: rgba(107,114,128,0.08); color: #6b7280; }

        /* ── Clickable Filter Stat Cards ── */
        .stat-card.stat-card-clickable {
            cursor: pointer;
            user-select: none;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card.stat-card-clickable:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .stat-card.stat-card-clickable.active {
            transform: translateY(-3px);
        }

        .stat-card.stat-card-clickable.green.active {
            border-color: #10b981;
            box-shadow: 0 0 0 2px #10b981, 0 8px 24px rgba(16, 185, 129, 0.25);
            background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%);
        }

        .stat-card.stat-card-clickable.gold.active {
            border-color: #f59e0b;
            box-shadow: 0 0 0 2px #f59e0b, 0 8px 24px rgba(245, 158, 11, 0.25);
            background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%);
        }

        .stat-card.stat-card-clickable.red.active {
            border-color: #ef4444;
            box-shadow: 0 0 0 2px #ef4444, 0 8px 24px rgba(239, 68, 68, 0.25);
            background: linear-gradient(180deg, #ffffff 0%, #fef2f2 100%);
        }

        .stat-card.stat-card-clickable.dimmed {
            opacity: 0.45;
            filter: grayscale(40%);
            transform: none !important;
            box-shadow: none !important;
        }

        /* ── Calendar Filter Styles ── */
        .cal-day-cell {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .cal-day-cell.dimmed {
            opacity: 0.12 !important;
            filter: grayscale(100%);
            transform: scale(0.94);
        }

        .cal-day-cell.highlighted {
            transform: scale(1.08);
            box-shadow: 0 4px 14px rgba(0,0,0,0.14);
            z-index: 5;
            position: relative;
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
            border-bottom: 1px solid #f0f0f8;
            color: var(--text);
            vertical-align: middle;
            transition: background 0.15s ease;
        }

        tbody tr:last-child td { border-bottom: none; }

        /* Impeccable v2: smooth row hover */
        tbody tr { transition: background 0.15s ease; }
        tbody tr:hover td {
            background: linear-gradient(90deg, rgba(6,4,234,0.025) 0%, rgba(6,4,234,0.012) 100%);
        }

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
        .badge-purple  { background: rgba(124,58,237,0.08);       color: var(--purple); }
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

        /* ============================================================
           UNIFIED FILTER TAB BAR (Standardized Segmented Control)
        ============================================================ */
        .filter-tab-bar {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            padding: 0.32rem;
            border-radius: 12px;
            border: 1px solid var(--border);
            gap: 0.35rem;
            max-width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .filter-tab-bar .filter-tab-item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.45rem 1.15rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
            text-decoration: none;
            border: none;
            background: transparent;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            cursor: pointer;
            line-height: 1.4;
        }

        .filter-tab-bar .filter-tab-item:hover {
            color: var(--navy);
            background: rgba(255, 255, 255, 0.75);
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
        }

        .filter-tab-bar .filter-tab-item.active {
            background: var(--primary-gradient, linear-gradient(135deg, #4c4bf7 0%, #0604EA 100%));
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(6, 4, 234, 0.22);
        }

        .filter-tab-bar .filter-tab-item.active-danger.active {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(239, 68, 68, 0.28);
        }

        .filter-tab-bar .filter-tab-item.active-success.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(16, 185, 129, 0.28);
        }

        .filter-tab-bar .filter-tab-item.active-gold.active {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(217, 119, 6, 0.28);
        }

        .filter-tab-bar .filter-tab-item.active-blue.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(37, 99, 235, 0.28);
        }

        .filter-tab-bar .filter-tab-item.active-pink.active {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(236, 72, 153, 0.28);
        }

        .filter-tab-bar .filter-tab-item.active i {
            color: #ffffff !important;
        }

        .filter-tab-bar.filter-tab-bar-sm .filter-tab-item {
            padding: 0.35rem 0.85rem;
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
            transition: all 0.2s ease-in-out;
        }

        .form-control:hover, 
        input[type="text"]:hover, 
        input[type="search"]:hover, 
        input[type="number"]:hover, 
        input[type="email"]:hover, 
        input[type="password"]:hover, 
        input[type="date"]:hover, 
        textarea:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
            background-color: #f8fafc !important;
            cursor: text;
        }

        select.form-control:hover, select:hover {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.18) !important;
            background-color: #f8fafc !important;
            cursor: pointer;
        }

        .form-control:focus, input:focus, select:focus, textarea:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 4px rgba(6, 4, 234, 0.2) !important;
            background-color: #ffffff !important;
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

        /* Sidebar Toggle Button (Always visible on Desktop & Mobile) */
        .sidebar-toggle {
            display: inline-flex;
            background: #f0f0f8;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 1.05rem;
            cursor: pointer;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .sidebar-toggle:hover {
            background: #e2e2f5;
            color: var(--primary);
            border-color: rgba(6,4,234,0.25);
            transform: translateY(-1px);
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

        /* ============================================================
           IMPECCABLE v2 — Additional Enhancements
        ============================================================ */

        /* Card: refined hover border glow */
        .card:hover {
            box-shadow: var(--shadow-lg);
            border-color: rgba(6,4,234,0.10);
        }

        /* Card header: cleaner look */
        .card-header-bar {
            background: linear-gradient(180deg, #fcfcff 0%, #f8f8fd 100%);
        }
        .card-header-bar h3 {
            font-size: 0.95rem;
            letter-spacing: 0.01em;
        }

        /* Topbar: slightly more visible shadow */
        .topbar {
            box-shadow: 0 1px 0 rgba(0,0,0,0.04), 0 4px 20px -8px rgba(6,4,234,0.06);
        }

        /* Topbar title accent bar: gradient */
        .topbar-title::before {
            background: linear-gradient(180deg, hsl(241,90%,63%) 0%, hsl(240,95%,47%) 100%);
            width: 3px;
            height: 18px;
            border-radius: 2px;
        }

        /* Btn-primary: shimmer on hover */
        .btn-primary {
            position: relative;
            overflow: hidden;
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 35%, rgba(255,255,255,0.18) 50%, transparent 65%);
            transform: translateX(-100%);
            transition: transform 0.5s ease;
        }
        .btn-primary:hover::after { transform: translateX(100%); }

        /* Page header: add divider */
        .page-header {
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
            margin-bottom: 1.75rem;
        }
        .page-header h2 {
            font-size: 1.4rem;
            letter-spacing: -0.01em;
        }

        /* Badge: slightly rounder */
        .badge {
            border-radius: 20px;
            padding: 0.2rem 0.6rem;
            font-size: 0.73rem;
        }

        /* Sidebar footer logout: refined */
        .btn-logout {
            border-radius: 10px;
            font-size: 0.85rem;
            letter-spacing: 0.01em;
        }

        /* Alert: rounded more */
        .alert {
            border-radius: 10px;
            animation: alertSlideIn 0.35s cubic-bezier(0.16,1,0.3,1) both;
        }
        @keyframes alertSlideIn {
            from { opacity:0; transform: translateY(-8px); }
            to   { opacity:1; transform: translateY(0); }
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
                    <div class="system-name">ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน</div>
                </div>
            </div>
        </div>

        @php
            $currentActiveRole = session('active_role', auth()->user()->Role);
            $userAvailableRoles = auth()->user()->getAvailableRoles();
        @endphp

        <div class="role-badge" style="position:relative;">
            <div class="user-name">{{ auth()->user()->FullName }}</div>
            <div class="user-username" style="font-size:0.75rem; color:rgba(255, 255, 255, 0.7); display:flex; align-items:center; gap:0.25rem;">
                <i class="far fa-id-card" style="font-size:0.7rem;"></i> รหัสประจำตัว: <strong style="color:#fff;">{{ auth()->user()->Username }}</strong>
            </div>
            <div class="user-role-container" style="display:flex; align-items:center; gap:0.35rem; margin-top:0.15rem;">
                <div class="user-role" style="margin:0;">
                    {{ $currentActiveRole === 'ครู' ? 'ครูประจำชั้น' : $currentActiveRole }}
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            @include('layouts.partials.nav-' . match(strtolower($currentActiveRole)) {
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
                <button type="submit" class="btn-logout" title="ออกจากระบบ">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="logout-text">ออกจากระบบ</span>
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
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="sidebar-toggle" id="sidebarToggle" title="เมนู">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Semester Selector -->
                @php
                    $activeSemester = \App\Models\Semester::where('is_active', true)->first();
                    $selectedSemesterId = session('selected_semester_id', $activeSemester?->semester_id);
                    $semestersList = \App\Models\Semester::orderBy('academic_year', 'desc')->orderBy('term', 'desc')->get();
                @endphp
                @if($semestersList->count() > 0)
                <form action="{{ route('semesters.switch') }}" method="POST" id="semester-switch-form" style="margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                    @csrf
                    <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); white-space: nowrap; display: flex; align-items: center; gap: 0.25rem;">
                        <i class="fas fa-graduation-cap" style="color: var(--primary);"></i> ปีการศึกษา:
                    </label>
                    <select name="semester_id" onchange="document.getElementById('semester-switch-form').submit()" 
                            style="font-family: 'Sarabun', sans-serif; font-size: 0.82rem; font-weight: 500; height: 32px; padding: 0.25rem 2rem 0.25rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); background-color: #fff; color: var(--text); cursor: pointer; outline: none; transition: var(--transition); background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236c6c8f%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 0.65rem auto; -webkit-appearance: none; -moz-appearance: none; appearance: none; box-shadow: var(--shadow-sm);">
                        @foreach($semestersList as $sem)
                            <option value="{{ $sem->semester_id }}" {{ $selectedSemesterId == $sem->semester_id ? 'selected' : '' }}>
                                {{ $sem->academic_year }} ภาคเรียนที่ {{ $sem->term }} {{ $sem->is_active ? '(ปัจจุบัน)' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>
                @endif

                <!-- Child/Student Selector for Parents -->
                @if(in_array(strtolower(auth()->user()->Role), ['ผู้ปกครอง', 'parent']))
                    @php
                        $parentStudents = auth()->user()->parentStudents;
                        $selectedStudentId = session('selected_student_id', $parentStudents->first()?->StudentID);
                    @endphp
                    @if($parentStudents->count() > 1)
                    <form action="{{ route('parent.switch-student') }}" method="POST" id="student-switch-form" style="margin: 0; display: flex; align-items: center; gap: 0.4rem; margin-left: 1rem;">
                        @csrf
                        <label style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); white-space: nowrap; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="fas fa-child" style="color: var(--primary);"></i> บุตรหลาน:
                        </label>
                        <select name="student_id" onchange="document.getElementById('student-switch-form').submit()" 
                                style="font-family: 'Sarabun', sans-serif; font-size: 0.82rem; font-weight: 500; height: 32px; padding: 0.25rem 2rem 0.25rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); background-color: #fff; color: var(--text); cursor: pointer; outline: none; transition: var(--transition); background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%22292.4%22%20height%3D%22292.4%22%3E%3Cpath%20fill%3D%22%236c6c8f%22%20d%3D%22M287%2069.4a17.6%2017.6%200%200%200-13-5.4H18.4c-5%200-9.3%201.8-12.9%205.4A17.6%2017.6%200%200%200%200%2082.2c0%205%201.8%209.3%205.4%2012.9l128%20127.9c3.6%203.6%207.8%205.4%2012.8%205.4s9.2-1.8%2012.8-5.4L287%2095c3.5-3.5%205.4-7.8%205.4-12.8%200-5-1.9-9.2-5.5-12.8z%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 0.65rem auto; -webkit-appearance: none; -moz-appearance: none; appearance: none; box-shadow: var(--shadow-sm);">
                            @foreach($parentStudents as $std)
                                <option value="{{ $std->StudentID }}" {{ $selectedStudentId == $std->StudentID ? 'selected' : '' }}>
                                    {{ $std->FullName }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                    @endif
                @endif
            </div>
            <div class="topbar-right">
                <span style="font-size:0.8rem; color:var(--text-muted);">
                    {{ now()->locale('th')->isoFormat('D MMM ') . (now()->year + 543) }}
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

    {{-- Reusable Custom Confirm Modal --}}
    <div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(15,14,52,0.55); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; max-width:400px; width:92%; box-shadow:0 24px 80px rgba(0,0,0,0.3); overflow:hidden; animation:slideUp .25s ease;">
            <!-- Header bar -->
            <div id="confirmHeaderBar" style="background:linear-gradient(135deg,#e05370,#bd2743); padding:1.5rem 2rem; text-align:center;">
                <div id="confirmIcon" style="font-size:3rem; line-height:1; margin-bottom:0.25rem;">❓</div>
                <h3 id="confirmTitle" style="color:#fff; margin:0; font-size:1.15rem; font-weight:700;">ยืนยันการทำรายการ</h3>
            </div>
            <!-- Body -->
            <div style="padding:1.75rem 2rem;">
                <p id="confirmMessage" style="margin:0 0 1.5rem; color:#374151; font-size:0.92rem; text-align:center; line-height:1.5;"></p>
                <div style="display:flex; gap:0.75rem;">
                    <button id="confirmCancelBtn" class="btn btn-outline" style="flex:1;">ยกเลิก</button>
                    <button id="confirmSubmitBtn" class="btn btn-danger" style="flex:1;">ตกลง</button>
                </div>
            </div>
        </div>
    </div>

    {{-- 2-Step Delete Confirmation Modal --}}
    <div id="deleteModal" style="display:none; position:fixed; inset:0; background:rgba(15,14,52,0.55); backdrop-filter:blur(4px); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:#fff; border-radius:16px; max-width:420px; width:92%; box-shadow:0 24px 80px rgba(0,0,0,0.3); overflow:hidden; animation:slideUp .25s ease;">

            <!-- Step 1: Warning Alert -->
            <div id="deleteStep1">
                <!-- Header bar -->
                <div style="background:linear-gradient(135deg,#bd2743,#e05370); padding:1.5rem 2rem; text-align:center; position:relative;">
                    <div style="font-size:3rem; line-height:1; margin-bottom:0.25rem;">⚠️</div>
                    <h3 style="color:#fff; margin:0; font-size:1.15rem; font-weight:700;">คำเตือน!</h3>
                    <p style="color:rgba(255,255,255,0.8); margin:0.35rem 0 0; font-size:0.82rem;">การดำเนินการนี้ไม่สามารถย้อนกลับได้</p>
                </div>
                <!-- Body -->
                <div style="padding:1.75rem 2rem;">
                    <div style="background:#fff5f7; border:1.5px solid #fbc9d3; border-radius:10px; padding:1rem 1.25rem; margin-bottom:1.25rem;">
                        <p id="deleteMsg1" style="margin:0; color:#7c1a2e; font-size:0.92rem; font-weight:600; line-height:1.5;"></p>
                    </div>
                    <p style="color:#6b7280; font-size:0.83rem; margin:0 0 1.5rem; text-align:center;">
                        กด <strong style="color:#bd2743;">ดำเนินการต่อ</strong> เพื่อไปยังขั้นตอนยืนยัน
                    </p>
                    <div style="display:flex; gap:0.75rem;">
                        <button id="deleteCancel1" class="btn btn-outline" style="flex:1;">ยกเลิก</button>
                        <button id="deleteNext" class="btn btn-danger" style="flex:1;">ดำเนินการต่อ →</button>
                    </div>
                </div>
            </div>

            <!-- Step 2: Type to Confirm -->
            <div id="deleteStep2" style="display:none;">
                <!-- Header bar -->
                <div style="background:linear-gradient(135deg,#7c1a2e,#bd2743); padding:1.5rem 2rem; text-align:center;">
                    <div style="font-size:3rem; line-height:1; margin-bottom:0.25rem;">🗑️</div>
                    <h3 style="color:#fff; margin:0; font-size:1.15rem; font-weight:700;">ยืนยันการลบ</h3>
                    <p style="color:rgba(255,255,255,0.75); margin:0.35rem 0 0; font-size:0.82rem;">ขั้นตอนที่ 2 จาก 2</p>
                </div>
                <!-- Body -->
                <div style="padding:1.75rem 2rem;">
                    <p style="color:#374151; font-size:0.88rem; margin:0 0 0.6rem;">พิมพ์ <strong style="color:#bd2743; background:#fff5f7; padding:0.1rem 0.45rem; border-radius:4px; border:1px solid #fbc9d3;">ยืนยัน</strong> ในช่องด้านล่างเพื่อยืนยันการลบ</p>
                    <input id="deleteConfirmInput"
                           type="text"
                           class="form-control"
                           placeholder='พิมพ์ "ยืนยัน" ที่นี่'
                           autocomplete="off"
                           style="margin-bottom:1.25rem; border-color:#e5e7eb; text-align:center; font-size:1rem; letter-spacing:0.05em;">
                    <div id="deleteInputError" style="display:none; color:#bd2743; font-size:0.8rem; text-align:center; margin:-0.75rem 0 1rem;">❌ กรุณาพิมพ์ "ยืนยัน" ให้ถูกต้อง</div>
                    <div style="display:flex; gap:0.75rem;">
                        <button id="deleteBack" class="btn btn-outline" style="flex:1;">← กลับ</button>
                        <button id="deleteConfirmBtn" class="btn btn-danger" style="flex:1; opacity:0.5; cursor:not-allowed;" disabled>ลบ</button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        sidebar.classList.toggle('show');
                        if (overlay) overlay.classList.toggle('show');
                    } else {
                        document.body.classList.toggle('sidebar-collapsed');
                        const isCollapsed = document.body.classList.contains('sidebar-collapsed');
                        localStorage.setItem('sidebar_collapsed', isCollapsed ? '1' : '0');
                    }
                });

                if (overlay) {
                    overlay.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        overlay.classList.remove('show');
                    });
                }
            }

            // ── 2-Step Delete Modal ──────────────────────────────────
            const modal         = document.getElementById('deleteModal');
            const step1         = document.getElementById('deleteStep1');
            const step2         = document.getElementById('deleteStep2');
            const msg1El        = document.getElementById('deleteMsg1');
            const confirmInput  = document.getElementById('deleteConfirmInput');
            const inputError    = document.getElementById('deleteInputError');
            const btnConfirmOk  = document.getElementById('deleteConfirmBtn');
            let pendingForm     = null;

            function openModal(msg) {
                msg1El.textContent = msg || 'คุณกำลังจะลบรายการนี้ ข้อมูลจะหายไปอย่างถาวร';
                step1.style.display = 'block';
                step2.style.display = 'none';
                confirmInput.value  = '';
                inputError.style.display = 'none';
                btnConfirmOk.disabled   = true;
                btnConfirmOk.style.opacity = '0.5';
                btnConfirmOk.style.cursor  = 'not-allowed';
                modal.style.display = 'flex';
                // Re-trigger animation
                const box = modal.querySelector('div');
                box.style.animation = 'none';
                box.offsetHeight;
                box.style.animation = '';
            }
            function closeModal() {
                modal.style.display = 'none';
                pendingForm = null;
            }

            // Step 1 → Step 2
            document.getElementById('deleteNext').addEventListener('click', function() {
                step1.style.display = 'none';
                step2.style.display = 'block';
                confirmInput.focus();
            });

            // Step 2 → back to Step 1
            document.getElementById('deleteBack').addEventListener('click', function() {
                step2.style.display = 'none';
                step1.style.display = 'block';
                confirmInput.value = '';
                inputError.style.display = 'none';
            });

            // Cancel buttons
            document.getElementById('deleteCancel1').addEventListener('click', closeModal);

            // Enable confirm button only when typed correctly
            confirmInput.addEventListener('input', function() {
                const ok = this.value.trim() === 'ยืนยัน';
                btnConfirmOk.disabled   = !ok;
                btnConfirmOk.style.opacity = ok ? '1' : '0.5';
                btnConfirmOk.style.cursor  = ok ? 'pointer' : 'not-allowed';
                if (ok) inputError.style.display = 'none';
            });

            // Also allow pressing Enter to confirm
            confirmInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !btnConfirmOk.disabled) {
                    btnConfirmOk.click();
                }
            });

            // Final confirm → submit form
            btnConfirmOk.addEventListener('click', function() {
                if (this.disabled) return;
                if (confirmInput.value.trim() !== 'ยืนยัน') {
                    inputError.style.display = 'block';
                    return;
                }
                if (pendingForm) {
                    const f = pendingForm;
                    closeModal();
                    f.submit();
                }
            });

            // Close when clicking backdrop
            modal.addEventListener('click', function(e) {
                if (e.target === modal) closeModal();
            });

            // ── Reusable Confirm Modal ──────────────────────────────
            const confirmModal = document.getElementById('confirmModal');
            const confirmMessage = document.getElementById('confirmMessage');
            const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
            const confirmCancelBtn = document.getElementById('confirmCancelBtn');
            const confirmTitle = document.getElementById('confirmTitle');
            const confirmHeader = document.getElementById('confirmHeaderBar');
            const confirmIcon = document.getElementById('confirmIcon');
            let confirmPendingForm = null;

            window.showConfirmModal = function(options) {
                confirmPendingForm = options.form || null;
                confirmMessage.textContent = options.message || 'คุณต้องการยืนยันการทำรายการนี้ใช่หรือไม่?';
                confirmTitle.textContent = options.title || 'ยืนยันการทำรายการ';
                
                // Color themes
                if (options.theme === 'danger') {
                    confirmHeader.style.background = 'linear-gradient(135deg,#bd2743,#e05370)';
                    confirmSubmitBtn.className = 'btn btn-danger';
                    confirmSubmitBtn.style.color = '#fff';
                    confirmSubmitBtn.textContent = options.submitText || 'ยืนยัน';
                    confirmIcon.textContent = options.icon || '⚠️';
                } else if (options.theme === 'warning') {
                    confirmHeader.style.background = 'linear-gradient(135deg,#eab308,#facc15)';
                    confirmSubmitBtn.className = 'btn btn-warning';
                    confirmSubmitBtn.style.color = '#000';
                    confirmSubmitBtn.textContent = options.submitText || 'ดำเนินการ';
                    confirmIcon.textContent = options.icon || '⚠️';
                } else {
                    confirmHeader.style.background = 'linear-gradient(135deg,#4f46e5,#6366f1)';
                    confirmSubmitBtn.className = 'btn btn-primary';
                    confirmSubmitBtn.style.color = '#fff';
                    confirmSubmitBtn.textContent = options.submitText || 'ตกลง';
                    confirmIcon.textContent = options.icon || '❓';
                }
                
                confirmModal.style.display = 'flex';
            };

            function closeConfirmModal() {
                confirmModal.style.display = 'none';
                confirmPendingForm = null;
            }

            confirmCancelBtn.addEventListener('click', closeConfirmModal);
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) closeConfirmModal();
            });

            confirmSubmitBtn.addEventListener('click', function() {
                if (confirmPendingForm) {
                    const f = confirmPendingForm;
                    closeConfirmModal();
                    f.submit();
                }
            });

            // Intercept form submissions
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('form');
                if (!form) return;

                const methodInput = form.querySelector('input[name="_method"]');
                const isDelete = methodInput && methodInput.value.toUpperCase() === 'DELETE';

                if (isDelete) {
                    e.preventDefault();
                    pendingForm = form;
                    const msg = form.getAttribute('data-confirm') || 'คุณกำลังจะลบรายการนี้ ข้อมูลจะหายไปอย่างถาวร';
                    openModal(msg);
                    return;
                }

                if (form.hasAttribute('data-confirm')) {
                    e.preventDefault();
                    const msg = form.getAttribute('data-confirm') || 'คุณต้องการยืนยันการทำรายการนี้ใช่หรือไม่?';
                    const title = form.getAttribute('data-confirm-title') || 'ยืนยันการทำรายการ';
                    const theme = form.getAttribute('data-confirm-theme') || 'danger';
                    const submitText = form.getAttribute('data-confirm-submit') || 'ตกลง';
                    const icon = form.getAttribute('data-confirm-icon') || '❓';
                    
                    window.showConfirmModal({
                        form: form,
                        message: msg,
                        title: title,
                        theme: theme,
                        submitText: submitText,
                        icon: icon
                    });
                }
            });
        });

    </script>
    @stack('scripts')
</body>
</html>