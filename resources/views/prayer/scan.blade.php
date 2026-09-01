@extends('layouts.app')

@section('title', 'เช็กชื่อละหมาด')
@section('page-title', 'ระบบเช็กชื่อการละหมาดประจำวัน')

@push('styles')
<style>
    /* Islamic School Colors & Design System */
    :root {
        --islamic-primary: #0D5C3A;
        --islamic-emerald: #10B981;
        --islamic-gold: #C5A85C;
        --islamic-gold-pale: rgba(197, 168, 92, 0.08);
        --islamic-bg: #F4F9F6;
    }

    .prayer-container {
        max-width: 650px;
        margin: 0 auto;
    }

    .theme-card {
        background: var(--white);
        border-radius: 16px;
        border: 1px solid rgba(13, 92, 58, 0.12);
        box-shadow: 0 10px 30px rgba(13, 92, 58, 0.04);
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .theme-card-header {
        background: linear-gradient(135deg, var(--islamic-primary) 0%, #052b1b 100%);
        color: white;
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 3px solid var(--islamic-gold);
    }

    .theme-card-header h3 {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }

    /* USB Input */
    .usb-input-container {
        position: relative;
        background: #f1f5f9;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .usb-input-container input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        font-family: monospace;
        font-size: 1rem;
        color: var(--text);
    }

    .usb-input-container .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: var(--red);
        box-shadow: 0 0 6px var(--red);
    }

    .usb-input-container.focused .status-indicator {
        background: var(--islamic-emerald);
        box-shadow: 0 0 6px var(--islamic-emerald);
    }

    /* Scanned Profile Display Card */
    .profile-card {
        display: none;
        animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(15px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-card-content {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .profile-photo {
        width: 80px;
        height: 100px;
        border-radius: 8px;
        border: 2px solid var(--islamic-gold);
        overflow: hidden;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .profile-photo img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .profile-details {
        flex: 1;
    }

    .profile-name {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--islamic-primary);
        margin-bottom: 0.25rem;
    }

    .profile-meta {
        font-size: 0.88rem;
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .profile-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 30px;
        font-size: 0.82rem;
        font-weight: 700;
    }
</style>
@endpush

@section('content')
<div class="prayer-container">
    
    <!-- USB Scanner Field -->
    <div class="theme-card">
        <div class="theme-card-header">
            <h3><i class="fas fa-keyboard"></i> เครื่องยิง Barcode (USB)</h3>
        </div>
        <div style="padding: 1.5rem;">
            <p style="font-size:0.85rem; color:var(--text-muted); margin-bottom:0.75rem;">
                คลิกที่ช่องป้อนข้อมูลเพื่อเริ่มต้นยิงด้วยเครื่องสแกนบาร์โค้ด
            </p>
            <div class="usb-input-container" id="usb-container">
                <div class="status-indicator" id="usb-status"></div>
                <input type="text" id="barcode-input" placeholder="คลิกเพื่อรอการยิงบาร์โค้ด..." autofocus>
            </div>
            <div style="font-size: 0.72rem; text-align: center; color: var(--text-muted);" id="focus-reminder">
                <span style="color:var(--red); font-weight:700;">●</span> ขาดการโฟกัส กรุณาคลิกในช่องข้อความ
            </div>
        </div>
    </div>

    <!-- Scan Result Display -->
    <div class="theme-card profile-card" id="student-result-card">
        <div class="theme-card-header" id="result-header" style="background: linear-gradient(135deg, var(--islamic-emerald) 0%, #065f46 100%);">
            <h3><i class="fas fa-user-check"></i> บันทึกประวัติสำเร็จ</h3>
        </div>
        <div class="profile-card-content">
            <div class="profile-photo" id="result-photo-container">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="profile-details">
                <div class="profile-name" id="result-name">-</div>
                <div class="profile-meta" id="result-meta">-</div>
                <div class="profile-meta" style="font-size:0.8rem; color:var(--text-muted);" id="result-time">-</div>
                <div class="profile-status-badge" id="result-status-badge">
                    <i class="fas fa-check"></i> ละหมาดแล้ว
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scan Failure Card -->
    <div class="theme-card profile-card" id="error-card" style="border-color: rgba(189,39,67,0.2);">
        <div class="theme-card-header" style="background: var(--red-gradient);">
            <h3><i class="fas fa-exclamation-circle"></i> เกิดข้อผิดพลาด</h3>
        </div>
        <div style="padding: 1.5rem; color: var(--red); font-weight: 600;" id="error-message">
            -
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // State variables
        let selectedPeriod = "{{ $defaultPeriod }}";
        let selectedStatus = "ละหมาด";

        // UI Selectors
        const barcodeInput = document.getElementById("barcode-input");
        const usbContainer = document.getElementById("usb-container");
        const focusReminder = document.getElementById("focus-reminder");
        const resultCard = document.getElementById("student-result-card");
        const errorCard = document.getElementById("error-card");

        // Native Audio Synthesizer Beep Feedback
        function playSuccessBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(1000, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.2, audioCtx.currentTime);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.15);
            } catch(e) {
                console.error("Web Audio beep failed:", e);
            }
        }

        // USB Input Focus Management
        barcodeInput.addEventListener("focus", function() {
            usbContainer.classList.add("focused");
            focusReminder.style.display = "none";
        });

        barcodeInput.addEventListener("blur", function() {
            usbContainer.classList.remove("focused");
            focusReminder.style.display = "block";
        });

        // Enforce autofocus on scanning field
        document.addEventListener("click", function(e) {
            if (!e.target.closest('.sidebar-toggle')) {
                barcodeInput.focus();
            }
        });

        // Barcode Keyboard Submit
        barcodeInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                const val = this.value.trim();
                if (val.length > 0) {
                    processScan(val);
                }
                this.value = "";
            }
        });

        // Process Scan payload to Laravel Backend
        function processScan(scannedPayload) {
            resultCard.style.display = "none";
            errorCard.style.display = "none";

            fetch("{{ route('prayer.scan.store', [], false) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    student_id: scannedPayload,
                    period: selectedPeriod,
                    status: selectedStatus
                })
            })
            .then(async res => {
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.success) {
                    // Display details
                    document.getElementById("result-name").innerText = data.student.name;
                    document.getElementById("result-meta").innerText = `รหัส: ${data.student.id} | ห้อง: ${data.student.class}`;

                    let periodText = data.record.period;
                    if (periodText === "เที่ยง" || periodText === "ซุฮรี") periodText = "ละหมาดซุฮรี";
                    else if (periodText === "บ่าย" || periodText === "อัศรี") periodText = "ละหมาดอัศรี";
                    document.getElementById("result-time").innerText = `บันทึกคาบ: ${periodText}`;
                    
                    const badge = document.getElementById("result-status-badge");
                    const resHeader = document.getElementById("result-header");

                    if (data.record.status === "ละหมาด") {
                        resHeader.style.background = "linear-gradient(135deg, var(--islamic-emerald) 0%, #065f46 100%)";
                        badge.style.background = "rgba(16, 185, 129, 0.1)";
                        badge.style.color = "#047857";
                        badge.style.border = "1px solid rgba(16, 185, 129, 0.2)";
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> ✅ ละหมาดแล้ว';
                    } else {
                        resHeader.style.background = "linear-gradient(135deg, #be123c 0%, #881337 100%)";
                        badge.style.background = "rgba(189, 39, 67, 0.08)";
                        badge.style.color = "var(--red)";
                        badge.style.border = "1px solid rgba(189, 39, 67, 0.15)";
                        badge.innerHTML = '<i class="fas fa-times-circle"></i> ❌ ละหมาดไม่ได้';
                    }

                    // Render photo
                    const photoBox = document.getElementById("result-photo-container");
                    if (data.student.photo) {
                        photoBox.innerHTML = `<img src="${data.student.photo}" alt="${data.student.name}">`;
                    } else {
                        photoBox.innerHTML = `<i class="fas fa-user-graduate" style="color: #9ca3af; font-size: 2.5rem;"></i>`;
                    }

                    resultCard.style.display = "block";
                } else {
                    document.getElementById("error-message").innerText = data.message || `ไม่พบข้อมูลนักเรียน (รหัส: ${scannedPayload})`;
                    errorCard.style.display = "block";
                }
            })
            .catch(err => {
                console.error("Scan error:", err);
                document.getElementById("error-message").innerText = "เกิดข้อผิดพลาดในการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง";
                errorCard.style.display = "block";
            })
            .finally(() => {
                barcodeInput.focus();
            });
        }
    });
</script>
@endpush
