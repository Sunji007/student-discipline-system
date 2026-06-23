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
            color: #0d5c3a;
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
            font-size: 0.88rem;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 0.75rem;
            text-align: left;
        }
        th {
            background-color: #f4fdf8;
            color: #0d5c3a;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
        }
        tr:nth-child(even) td {
            background-color: #fafafa;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .percent-badge {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
        }
        
        .footer-sign {
            margin-top: 4rem;
            display: flex;
            justify-content: flex-end;
            padding-right: 2rem;
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
            background: #0d5c3a;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            text-decoration: none;
        }
        .btn:hover {
            background: #08372b;
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
        <h1 class="report-title">{{ $reportTitle }} ({{ $type === 'daily' ? 'รายวัน' : ($type === 'weekly' ? 'รายสัปดาห์' : ($type === 'monthly' ? 'รายเดือน' : 'รายภาคเรียน')) }})</h1>
        <div class="report-period">{{ $periodText }}</div>
        @if($grade || $classroom || $passingStatus)
            <div class="filter-info">
                ระดับชั้น: {{ $grade ?? 'ทั้งหมด' }} | ห้องเรียน: {{ $classroom ?? 'ทั้งหมด' }}
                @if($passingStatus)
                    | เกณฑ์การละหมาด: {{ $passingStatus === 'pass' ? 'ผ่านเกณฑ์ (80% ขึ้นไป)' : 'ไม่ผ่านเกณฑ์ (ต่ำกว่า 80%)' }}
                @endif
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">รหัสนักเรียน</th>
                <th style="width: 35%;">ชื่อ-นามสกุล</th>
                <th style="width: 15%;">ระดับชั้น/ห้อง</th>
                <th style="width: 10%;" class="text-center">เพศ</th>
                <th style="width: 10%;" class="text-center">ละหมาดแล้ว</th>
                <th style="width: 10%;" class="text-center">ขาดละหมาด</th>
                <th style="width: 12%;" class="text-center">ละหมาดไม่ได้</th>
                <th style="width: 10%;" class="text-center">ร้อยละ (%)</th>
                <th style="width: 13%;" class="text-center">เกณฑ์ละหมาด</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $row)
            <tr>
                <td><code>{{ $row['StudentID'] }}</code></td>
                <td><strong>{{ $row['FullName'] }}</strong></td>
                <td>{{ $row['Class'] }}</td>
                <td class="text-center">{{ $row['Gender'] ?? '-' }}</td>
                <td class="text-center" style="color:#047857; font-weight:700;">{{ $row['prayed'] }}</td>
                <td class="text-center" style="color:#b91c1c; font-weight:700;">{{ $row['absent'] }}</td>
                <td class="text-center" style="color:#4b5563;">{{ $row['exempt'] }}</td>
                <td class="text-center percent-badge" style="font-weight:700; color:{{ $row['percent'] >= 80 ? '#047857' : ($row['percent'] >= 60 ? '#d97706' : '#b91c1c') }}">
                    {{ $row['percent'] }}%
                </td>
                <td class="text-center" style="font-weight:700; color:{{ $row['percent'] >= 80 ? '#047857' : (isset($row['is_corrected']) && $row['is_corrected'] ? '#1d4ed8' : '#b91c1c') }}">
                    @if($row['percent'] >= 80)
                        ผ่านเกณฑ์
                    @elseif(isset($row['is_corrected']) && $row['is_corrected'])
                        แก้ละหมาดแล้ว
                    @else
                        ไม่ผ่านเกณฑ์
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature line -->
    <div class="footer-sign">
        <div class="sign-area">
            <p>ผู้รายงานข้อมูล</p>
            <div class="sign-line"></div>
            <p style="font-size:0.85rem; color:#666;">(......................................................)</p>
            <p style="font-size:0.8rem; color:#888; margin-top:0.25rem;">วันที่: {{ now()->locale('th')->isoFormat('D MMMM YYYY') }}</p>
        </div>
    </div>

</body>
</html>
