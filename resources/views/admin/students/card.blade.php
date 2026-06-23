<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัตรประจำตัวนักเรียน — {{ $student->FullName }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            --primary-islamic: #0B4A3A;
            --emerald: #10B981;
            --gold: #C5A85C;
            --gold-dark: #A68A45;
            --text-dark: #1E293B;
            --bg-card: #FFFFFF;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: #f1f5f9;
            color: var(--text-dark);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem 1rem;
        }

        /* Control Panel */
        .controls {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            border: none;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .btn-print {
            background: var(--primary-islamic);
            color: white;
        }
        .btn-print:hover {
            background: #08372b;
            transform: translateY(-1px);
        }

        .btn-back {
            background: white;
            color: var(--text-dark);
            border: 1px solid #cbd5e1;
        }
        .btn-back:hover {
            background: #f8fafc;
            transform: translateY(-1px);
        }

        /* Card Container (Standard ID-1 Size: 85.6mm x 54mm or scale of 3.375 x 2.125 inches) */
        .card-container {
            width: 338px;
            height: 530px; /* Vertically oriented card */
            background: var(--bg-card);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(11,74,58, 0.12), 0 5px 15px rgba(0,0,0,0.06);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            border: 1px solid rgba(11,74,58,0.15);
            background-image: 
                radial-gradient(circle at 100% 0%, rgba(212, 175, 55, 0.05) 0%, transparent 60%),
                radial-gradient(circle at 0% 100%, rgba(11, 74, 58, 0.05) 0%, transparent 60%);
        }

        /* Top Header Area - School Brand */
        .card-header {
            background: linear-gradient(135deg, var(--primary-islamic) 0%, #05241c 100%);
            padding: 1.25rem 1rem;
            color: white;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: 3px solid var(--gold);
            position: relative;
        }

        .card-header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--gold) 0%, #fef3c7 50%, var(--gold) 100%);
        }

        .logo {
            width: 50px;
            height: auto;
            margin-bottom: 0.5rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
        }

        .school-name {
            font-size: 0.85rem;
            font-weight: 700;
            line-height: 1.3;
            letter-spacing: 0.02em;
        }
        
        .card-title {
            font-size: 0.65rem;
            color: var(--gold);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-top: 0.15rem;
        }

        /* Body Info Area */
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.25rem 1rem 0.5rem;
            position: relative;
        }

        /* Photo Frame */
        .photo-frame {
            width: 100px;
            height: 125px; /* Vertical photo */
            border-radius: 8px;
            border: 2px solid var(--gold);
            overflow: hidden;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
        }

        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-frame i {
            font-size: 3rem;
            color: #cbd5e1;
        }

        /* Student Name & ID */
        .student-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary-islamic);
            text-align: center;
            margin-bottom: 0.2rem;
        }

        .student-id {
            font-family: 'Outfit', sans-serif;
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
            background: #f1f5f9;
            padding: 0.15rem 0.75rem;
            border-radius: 20px;
            margin-bottom: 0.85rem;
        }

        /* Meta details (Grade / Gender) */
        .meta-details {
            display: flex;
            gap: 1.5rem;
            font-size: 0.78rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            border-top: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.4rem 1.5rem;
            width: 100%;
            justify-content: center;
        }

        .meta-item strong {
            color: var(--primary-islamic);
        }

        /* Codes Display Area */
        .codes-section {
            width: 100%;
            display: flex;
            justify-content: space-around;
            align-items: center;
            margin-top: auto;
            padding-bottom: 0.5rem;
        }

        .qr-box {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .barcode-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .barcode-canvas {
            max-width: 170px;
            height: auto;
        }

        /* Footer Accent */
        .card-footer {
            height: 12px;
            background: linear-gradient(90deg, var(--primary-islamic) 0%, var(--emerald) 100%);
            position: relative;
        }

        .card-footer::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: var(--gold);
        }

        /* Print Specific Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .controls {
                display: none !important;
            }
            .card-container {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
                margin: 0 auto;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <a href="javascript:history.back()" class="btn btn-back">
            <i class="fas fa-arrow-left"></i> ย้อนกลับ
        </a>
        <button onclick="window.print()" class="btn btn-print">
            <i class="fas fa-print"></i> พิมพ์บัตรประจำตัว
        </button>
    </div>

    <!-- Student Card Container -->
    <div class="card-container">
        <!-- Header -->
        <div class="card-header">
            <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo">
            <div class="school-name">โรงเรียนศิริราษฎร์สามัคคี</div>
            <div class="card-title">บัตรประจำตัวนักเรียน</div>
        </div>

        <!-- Body -->
        <div class="card-body">
            <!-- Photo Frame -->
            <div class="photo-frame">
                @if($student->Photo)
                    <img src="{{ asset('storage/' . $student->Photo) }}" alt="{{ $student->FullName }}">
                @else
                    <i class="fas fa-user-graduate"></i>
                @endif
            </div>

            <!-- Profile Info -->
            <h2 class="student-name">{{ $student->FullName }}</h2>
            <div class="student-id">ID: {{ $student->StudentID }}</div>

            <!-- Meta details -->
            <div class="meta-details">
                <div class="meta-item">ชั้นเรียน: <strong>{{ $student->classroom_display }}</strong></div>
                <div class="meta-item">เพศ: <strong>{{ $student->Gender ?? '-' }}</strong></div>
            </div>

            <!-- Codes Section -->
            <div class="codes-section">
                <!-- QR Code Box -->
                <div class="qr-box">
                    <canvas id="qr-code"></canvas>
                </div>

                <!-- Barcode Box -->
                <div class="barcode-box">
                    <svg id="barcode"></svg>
                </div>
            </div>
        </div>

        <!-- Decorative Footer border -->
        <div class="card-footer"></div>
    </div>

    <!-- Generate Codes Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Student data payload for QR code
            const qrPayload = JSON.stringify({
                id: "{{ $student->StudentID }}",
                name: "{{ $student->FullName }}",
                grade: "{{ $student->classroom_display }}"
            });

            // 1. Generate QR Code
            const qr = new QRious({
                element: document.getElementById('qr-code'),
                value: qrPayload,
                size: 85,
                background: 'white',
                foreground: '#0B4A3A',
                level: 'H' // High error correction
            });

            // 2. Generate Barcode (Code128 format)
            JsBarcode("#barcode", "{{ $student->StudentID }}", {
                format: "CODE128",
                width: 1.3,
                height: 48,
                displayValue: false,
                lineColor: "#0b4a3a",
                margin: 0
            });
        });
    </script>
</body>
</html>
