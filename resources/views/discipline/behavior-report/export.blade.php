<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;500;600;700&family=Outfit:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background: #fff;
            color: #333;
            padding: 3rem 2rem;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #333;
            padding-bottom: 1.5rem;
            position: relative;
        }
        .logo {
            width: 70px;
            height: auto;
            margin-bottom: 0.5rem;
        }
        .school-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #0604EA;
        }
        .report-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-top: 0.25rem;
            color: #555;
        }
        .report-period {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.25rem;
        }
        .filter-info {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.25rem;
            font-style: italic;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            font-size: 0.82rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 0.65rem 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f5f5fc;
            color: #0f0e34;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .badge {
            display: inline-block;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
        }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-green { background: #d1fae5; color: #047857; }
        
        .footer-sign {
            margin-top: 4rem;
            display: flex;
            justify-content: flex-end;
            padding-right: 2rem;
            page-break-inside: avoid;
        }
        .sign-area {
            text-align: center;
            width: 250px;
        }
        .sign-line {
            border-bottom: 1px dashed #333;
            margin-bottom: 0.75rem;
            height: 40px;
        }

        .controls {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 100;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.88rem;
            color: white;
            background: #0604EA;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(6, 4, 234, 0.25);
            text-decoration: none;
        }
        .btn:hover {
            background: #0403b2;
        }

        @media print {
            .controls {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>

    <div class="controls">
        <button onclick="window.print()" class="btn">
            <i class="fas fa-print"></i> สั่งพิมพ์ / บันทึก PDF
        </button>
    </div>

    <!-- Report Header -->
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="โลโก้โรงเรียน" class="logo">
        <div class="school-name">โรงเรียนศิริราษฎร์สามัคคี</div>
        <h1 class="report-title">{{ $reportTitle }}</h1>
        <div class="report-period">{{ $periodText }}</div>
        @if($grade || $classroom)
            <div class="filter-info">
                ระดับชั้น: {{ $grade ?? 'ทั้งหมด' }} | ห้องเรียน: {{ $classroom ?? 'ทั้งหมด' }}
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">ลำดับ</th>
                <th style="width: 15%;" class="text-center">วันที่บันทึก</th>
                <th style="width: 15%;" class="text-center">รหัสนักเรียน</th>
                <th style="width: 25%;">ชื่อ-นามสกุล</th>
                <th style="width: 15%;" class="text-center">ระดับชั้น/ห้อง</th>
                <th style="width: 12%;" class="text-center">คะแนนคงเหลือ</th>
                <th style="width: 10%;">ครูประจำชั้น</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $row)
            @php
                $modifier = $row->rule->ScoreModifier;
                if ($row->rule->RuleType === 'ตัดคะแนน') {
                    $modifier = -abs($modifier);
                } else {
                    $modifier = abs($modifier);
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->RecordDate)->format('d/m/Y') }}</td>
                <td class="text-center"><code>{{ $row->student->StudentID }}</code></td>
                <td><strong>{{ $row->student->FullName }}</strong></td>
                <td class="text-center">{{ $row->student->classroom_display }}</td>
                <td class="text-center" style="font-weight:700; color:{{ $row->student->BehaviorScore < 60 ? '#b91c1c' : ($row->student->BehaviorScore < 80 ? '#d97706' : '#047857') }}">
                    {{ $row->student->BehaviorScore }}
                </td>
                <td>{{ $row->student->advisory_teacher->user->FullName ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:2rem; color:#666;">ไม่มีข้อมูลบันทึกตามช่วงเวลาและเงื่อนไขที่เลือก</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Signature line -->
    <div class="footer-sign">
        <div class="sign-area">
            <p>ผู้รายงานข้อมูล</p>
            <div class="sign-line"></div>
            <p style="font-size:0.85rem; color:#666;">(......................................................)</p>
            <p style="font-size:0.8rem; color:#888; margin-top:0.25rem;">ฝ่ายปกครอง โรงเรียนศิริราษฎร์สามัคคี</p>
            <p style="font-size:0.8rem; color:#888; margin-top:0.1rem;">วันที่พิมพ์: {{ now()->locale('th')->isoFormat('D MMMM YYYY') }}</p>
        </div>
    </div>

</body>
</html>
