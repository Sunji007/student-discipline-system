# DFD Level 0 — บันทึกการออกแบบ

แผนภาพ: [dfd-level0-student-discipline.html](dfd-level0-student-discipline.html)

ใช้ความหมาย Diagram 0 ตามเอกสาร DFD_Level0.md เดิม: 1.0–8.0 คงเลขเดิม เพิ่ม 9.0 ให้ครอบคลุม StudentPromotionService ที่มีอยู่จริง

## วิธีอ่าน

- ทั้ง 9 ส่วนเป็นกระบวนการระดับเดียวกัน ไม่ใช่การแตกเป็น Level 1
- E1–E5 และ D1–D10 ที่แสดงซ้ำคือหน่วยงาน/คลังเดียวกัน ใช้การแสดงซ้ำเพื่อตัดเส้นพาดข้ามกระบวนการ
- C1 คือข้อมูลภาคเรียนใหม่ 1.0 → 9.0; C2 คือสรุปผลเลื่อนชั้น 9.0 → 1.0 จุดเชื่อมเหล่านี้ไม่ใช่หน่วยงานภายนอก
- กระบวนการสื่อสารกันผ่านคลังข้อมูลร่วม ข้อมูลแจ้งเท็จจาก 6.0 บันทึก D3 เพื่อรออนุมัติใน 2.0
- คำร้อง 5.0 และการเข้าแถว 3.0 ปรับคะแนนใน D2 โดยตรงตามโค้ด ไม่เพิ่มเส้นผ่าน 2.0 ที่ไม่มีอยู่ใน implementation
- ข้อความจาก 6.0 เขียน D8 และถูกอ่าน/ส่งต่อถึงผู้ใช้ผ่าน 7.0
- แสดงเฉพาะกลุ่มข้อมูลและการไหลหลัก รายละเอียดตรวจสอบ input/session/cache/file storage อยู่ภายในแต่ละกระบวนการ
- สไตล์ขาวดำตามผู้ใช้; Tahoma และ Consolas แทนฟอนต์เริ่มต้นของสกิลเพื่อใช้ภาษาไทยและพิมพ์แบบออฟไลน์

## หลักฐานจากระบบ

### 1.0 จัดการข้อมูลหลัก บัญชีผู้ใช้และสิทธิ์

ข้อมูลหลักครอบคลุมครู ผู้ปกครอง ฝ่ายปกครอง หน่วยงาน ห้องที่ปรึกษา และภาคเรียน

- `routes/web.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/Admin`
- `app/Models/Semester.php`

### 2.0 จัดการพฤติกรรม และคะแนนวินัย

ครูบันทึกพฤติกรรมเพื่อรอฝ่ายปกครองอนุมัติ ข้อมูลที่อนุมัติใช้คำนวณคะแนน

- `app/Http/Controllers/Teacher/BehaviorRecordController.php`
- `app/Http/Controllers/Discipline/BehaviorRecordController.php`
- `app/Http/Controllers/Discipline/BehaviorRuleController.php`

### 3.0 บันทึกการเข้าแถว และตัดคะแนนอัตโนมัติ

มาสายสะสม 3 ครั้งเท่ากับขาด 1 ครั้ง และขาดสะสมทุก 3 ครั้งตัด 5 คะแนน

- `app/Http/Controllers/Teacher/AttendanceController.php`
- `app/Services/AttendanceDeductionService.php`

### 4.0 บันทึกและติดตาม การละหมาด

การบันทึกตรวจสอบบทบาทและรหัสนักเรียน ส่วนรายงานสรุปแสดงในกระบวนการ 8.0

- `app/Http/Controllers/Prayer/PrayerController.php`
- `routes/web.php`

### 5.0 รับและพิจารณา คำร้องอุทธรณ์

ฝ่ายปกครองคืนคะแนนหรือยกเลิกคำร้อง การคืนคะแนนปรับ D2 และสถานะใน D3 โดยตรง

- `app/Http/Controllers/Student/AppealController.php`
- `app/Http/Controllers/Discipline/AppealController.php`

### 6.0 รับและตรวจสอบ ข้อมูลแจ้งเบาะแส

การบันทึกพฤติกรรมแจ้งเท็จและข้อความเป็นทางเลือกหลังตรวจสอบ ไม่เกิดขึ้นกับทุกเรื่อง

- `app/Http/Controllers/Student/InformantReportController.php`
- `app/Http/Controllers/Discipline/InformantReportController.php`

### 7.0 รับส่งข้อความ และติดตามการอ่าน

ผู้รับขึ้นกับสิทธิ์ของแต่ละบทบาท ฝ่ายปกครองส่งข้อความถึงนักเรียนพร้อมสำเนาถึงผู้ปกครองได้

- `app/Http/Controllers/Admin/MessageController.php`
- `app/Http/Controllers/Discipline/MessageController.php`
- `app/Http/Controllers/Teacher/MessageController.php`
- `app/Http/Controllers/Student/MessageController.php`
- `app/Http/Controllers/ParentGuardian/MessageController.php`

### 8.0 แสดงข้อมูลติดตาม รายงานและแดชบอร์ด

คำขอรายงานระบุภาคเรียน ตัวกรอง และนักเรียนที่มีสิทธิ์ดู การส่งออกขึ้นกับสิทธิ์ของบทบาท

- `app/Http/Controllers/Admin/DashboardController.php`
- `app/Http/Controllers/Discipline/BehaviorReportController.php`
- `app/Http/Controllers/Teacher/DashboardController.php`
- `app/Http/Controllers/Student/DashboardController.php`
- `app/Http/Controllers/ParentGuardian/DashboardController.php`
- `app/Http/Controllers/Prayer/PrayerController.php`

### 9.0 ประเมินคะแนนวินัย และเลื่อนชั้นอัตโนมัติ

คะแนนอย่างน้อย 50 จึงผ่านเกณฑ์วินัย: ม.1–ม.5 เลื่อนชั้น ม.6 สำเร็จการศึกษา; ต่ำกว่าเกณฑ์คงชั้นเดิม

- `app/Models/Semester.php`
- `app/Services/StudentPromotionService.php`
- `app/Console/Commands/AutoPromoteStudents.php`

## คลังข้อมูล

| รหัส | ชื่อ | ตารางจริง |
|---|---|---|
| D1 | บัญชีผู้ใช้และสิทธิ์ | users, role_permissions |
| D2 | ข้อมูลนักเรียน / คะแนนและสถานะความเสี่ยง | students |
| D3 | บันทึกพฤติกรรม / และเกณฑ์คะแนน | behavior_records, behavior_rules |
| D4 | การเช็คชื่อเข้าแถว | attendances |
| D5 | การละหมาดและการแก้ไข | prayer_records, prayer_corrections |
| D6 | คำร้องอุทธรณ์ | appeals |
| D7 | ข้อมูลแจ้งเบาะแส | informant_reports |
| D8 | ข้อความ | messages |
| D9 | ข้อมูลหลักและภาคเรียน | teachers, parents, discipline_staff, departments, teacher_advisory_rooms, semesters |
| D10 | ประวัติการเลื่อนชั้น | student_promotions |

## ตรวจสอบ

ตัวสร้างตรวจเส้นตรงทุกช่วงของลูกศรทุกคู่ก่อนสร้างไฟล์: ต้องไม่มีเส้นตัดหรือเส้นซ้อน และกรอบป้ายต้องไม่ซ้อนกับโหนดหรือป้ายอื่น
ตรวจแบบ conservative บนทางเดินมุมฉากก่อนปัดมุมรัศมี 8px และจัดจุดต่อบนกระบวนการห่างกัน 32px

สร้างใหม่: `python docs/diagrams/build_dfd_level0.py`
