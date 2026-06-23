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
        max-width: 900px;
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

    /* Option Toggle Buttons */
    .toggle-group {
        display: flex;
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }

    .toggle-btn {
        flex: 1;
        padding: 1rem;
        border-radius: 12px;
        border: 2px solid rgba(99, 102, 241, 0.15);
        background: white;
        font-weight: 700;
        color: #4b5563;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .toggle-btn i {
        color: #6366f1;
        font-size: 1.1rem;
    }

    .toggle-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
    }

    /* Period Active States */
    .toggle-btn.period-active {
        background: rgba(16, 185, 129, 0.08) !important;
        border-color: var(--islamic-emerald) !important;
        color: #065f46 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
    }
    .toggle-btn.period-active i {
        color: var(--islamic-emerald) !important;
    }

    /* Status Active States */
    .toggle-btn.status-active[data-status="ละหมาด"] {
        background: rgba(16, 185, 129, 0.08) !important;
        border-color: var(--islamic-emerald) !important;
        color: #047857 !important;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
    }
    .toggle-btn.status-active[data-status="ละหมาด"] i {
        color: var(--islamic-emerald) !important;
    }

    .toggle-btn.status-active[data-status="ละหมาดไม่ได้"] {
        background: rgba(189, 39, 67, 0.08) !important;
        border-color: var(--red) !important;
        color: var(--red) !important;
        box-shadow: 0 4px 12px rgba(189, 39, 67, 0.15);
    }
    .toggle-btn.status-active[data-status="ละหมาดไม่ได้"] i {
        color: var(--red) !important;
    }

    /* Webcam scanner area */
    #reader {
        width: 100%;
        background: #000;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--border);
    }

    /* Hidden USB Input */
    .usb-input-container {
        position: relative;
        background: #f1f5f9;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
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

    .profile-status-badge.prayed {
        background: rgba(16, 185, 129, 0.1);
        color: #047857;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .profile-status-badge.exempt {
        background: rgba(189, 39, 67, 0.08);
        color: var(--red);
        border: 1px solid rgba(189, 39, 67, 0.15);
    }
</style>
@endpush

@section('content')
<div class="prayer-container">
    
    <div class="responsive-grid-dashboard">
        <!-- Main Scan Panel -->
        <div>
            <!-- Section 2: Webcam Camera Scanner -->
            <div class="theme-card">
                <div class="theme-card-header">
                    <h3><i class="fas fa-camera"></i> สแกนด้วยกล้องมือถือ / Web Camera</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <!-- Friendly error alert -->
                    <div id="camera-error-alert" style="display:none; background: rgba(189,39,67,0.08); color: #bd2743; border: 1px solid rgba(189,39,67,0.25); padding: 0.85rem 1.1rem; border-radius: 10px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 700; line-height: 1.5;">
                        <i class="fas fa-exclamation-triangle" style="margin-right:0.5rem; color: #bd2743;"></i>
                        <span id="camera-error-text"></span>
                    </div>

                    <div id="reader"></div>
                    <div id="camera-loading" style="display:none; text-align:center; padding:1rem; color:var(--text-muted);">
                        <i class="fas fa-spinner fa-spin" style="margin-right:0.5rem;"></i> กำลังเปิดกล้อง...
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Scanner Inputs & Feedback -->
        <div>
            <!-- Section 3: USB Scanner Field -->
            <div class="theme-card">
                <div class="theme-card-header">
                    <h3><i class="fas fa-keyboard"></i> เครื่องยิง Barcode (USB)</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="font-size:0.8rem; color:var(--text-muted); margin-bottom:0.75rem;">
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

            <!-- Section 4: Scan Result Display -->
            <div class="theme-card profile-card" id="student-result-card">
                <div class="theme-card-header" style="background: linear-gradient(135deg, var(--islamic-emerald) 0%, #065f46 100%);">
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
                            <i class="fas fa-check"></i> ละหมาดเรียบร้อย
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
    </div>
</div>
@endsection

@push('scripts')
<!-- Load html5-qrcode library for camera scanning -->
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // State variables
        let selectedPeriod = "{{ $defaultPeriod }}";
        let selectedStatus = "ละหมาด";
        let lastScannedId = "";
        let lastScanTime = 0;

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
                oscillator.frequency.setValueAtTime(1000, audioCtx.currentTime); // 1000 Hz beep
                gainNode.gain.setValueAtTime(0.2, audioCtx.currentTime);

                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.15); // beep for 0.15s
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

        // Loop to enforce autofocus on scanning field if camera not typing
        document.addEventListener("click", function(e) {
            // Refocus barcode input if clicked outside camera reader UI
            if (!e.target.closest('#reader') && !e.target.closest('.sidebar-toggle')) {
                barcodeInput.focus();
            }
        });

        // Barcode Keyboard Submit (USB scanner emulates typing + pressing Enter)
        barcodeInput.addEventListener("keypress", function(e) {
            if (e.key === "Enter") {
                const val = this.value.trim();
                if (val.length > 0) {
                    processScan(val);
                }
                this.value = "";
            }
        });

        // QR Code Web Camera Scanning using html5-qrcode
        function onScanSuccess(decodedText, decodedResult) {
            // Debounce scans (prevent double-scanning the same code in 2.5 seconds)
            const now = Date.now();
            if (decodedText === lastScannedId && (now - lastScanTime < 2500)) {
                return;
            }
            lastScannedId = decodedText;
            lastScanTime = now;

            processScan(decodedText);
        }

        // Check camera availability and show a friendly Thai warning if it fails
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    // Camera works fine, stop the test stream immediately
                    stream.getTracks().forEach(track => track.stop());
                })
                .catch(function(err) {
                    console.warn("Camera check failed:", err);
                    let alertDiv = document.getElementById("camera-error-alert");
                    let textSpan = document.getElementById("camera-error-text");
                    if (alertDiv && textSpan) {
                        let msg = "ไม่สามารถเปิดกล้องได้: ";
                        if (err.name === "NotReadableError" || err.message.indexOf("video source") !== -1) {
                            msg += "กล้องกำลังถูกใช้งานโดยแอปพลิเคชันหรือแท็บอื่นอยู่ กรุณาปิดแอปอื่นที่ใช้กล้อง (เช่น Zoom, Line, Teams หรือแท็บเบราว์เซอร์อื่นที่เปิดกล้อง) แล้วรีเฟรชหน้าเว็บใหม่";
                        } else if (err.name === "NotAllowedError" || err.name === "PermissionDeniedError") {
                            msg += "สิทธิ์การเข้าถึงกล้องถูกปฏิเสธ กรุณาอนุญาตให้เบราว์เซอร์เข้าถึงกล้องในการตั้งค่าเบราว์เซอร์/เว็บ";
                        } else if (err.name === "NotFoundError" || err.name === "DevicesNotFoundError") {
                            msg += "ไม่พบอุปกรณ์กล้องเชื่อมต่อกับเครื่องนี้";
                        } else {
                            msg += err.message || err.name;
                        }
                        textSpan.innerText = msg;
                        alertDiv.style.display = "block";
                    }
                });
        } else {
            let alertDiv = document.getElementById("camera-error-alert");
            let textSpan = document.getElementById("camera-error-text");
            if (alertDiv && textSpan) {
                textSpan.innerText = "เบราว์เซอร์ของคุณไม่รองรับการเปิดกล้องผ่านเว็บ หรือไม่ได้ใช้งานผ่านการเชื่อมต่อที่ปลอดภัย (HTTPS)";
                alertDiv.style.display = "block";
            }
        }

        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { 
                fps: 10, 
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                showTorchButtonIfSupported: true,
                showZoomSliderIfSupported: true
            },
            /* verbose= */ false
        );
        html5QrcodeScanner.render(onScanSuccess);

        // Process Scan payload to Laravel Backend
        function processScan(scannedPayload) {
            resultCard.style.display = "none";
            errorCard.style.display = "none";

            fetch("{{ route('prayer.scan.store') }}", {
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
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    playSuccessBeep();
                    
                    // Display details
                    document.getElementById("result-name").innerText = data.student.name;
                    document.getElementById("result-meta").innerText = `รหัส: ${data.student.id} | ห้อง: ${data.student.class}`;
                    document.getElementById("result-time").innerText = `บันทึกคาบ: ${data.record.period} เวลา ${data.record.time}`;
                    
                    const badge = document.getElementById("result-status-badge");
                    if (data.record.status === "ละหมาด") {
                        badge.className = "profile-status-badge prayed";
                        badge.innerHTML = '<i class="fas fa-check-circle"></i> ✅ ละหมาดแล้ว';
                    } else {
                        badge.className = "profile-status-badge exempt";
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
                    document.getElementById("error-message").innerText = data.message;
                    errorCard.style.display = "block";
                }
            })
            .catch(err => {
                console.error("Scan error:", err);
                document.getElementById("error-message").innerText = "ระบบขัดข้อง กรุณาลองใหม่อีกครั้ง";
                errorCard.style.display = "block";
            })
            .finally(() => {
                barcodeInput.focus();
            });
        }
    });
</script>
@endpush
