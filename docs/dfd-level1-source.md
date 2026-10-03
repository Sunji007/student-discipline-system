# DFD Level 1 — ระบบบริหารพฤติกรรมนักเรียน

แตกจาก Mermaid Level 0 ที่ผู้ใช้ให้ ครบทั้ง 8 กระบวนการหลัก รวม 25 กระบวนการย่อย

ไฟล์ HTML จัดเส้นด้วยพิกัด SVG และตรวจว่าไม่มีเส้นทับ/ตัดกัน ส่วน Mermaid เป็นต้นฉบับสำหรับแก้ไข การจัดเส้นของ Mermaid ขึ้นกับ renderer

D9 ภาคเรียนและ D10 ยอดขาด/สายสะสมเป็นข้อมูลภายในผัง 1.0 และ 3.0; D10 เป็นข้อมูลสรุปเชิงตรรกะจากการเข้าแถว ไม่ได้เพิ่มตารางฐานข้อมูล

C1–C4 คงการเชื่อมระหว่างกระบวนการหลักตาม Level 0 ส่วน Lx-y ใน HTML คือจุดต่อเส้นระหว่างกระบวนการย่อยในผังเดียวกัน

## ผัง 1.0 จัดการบัญชีผู้ใช้ และสิทธิ์เข้าถึง

```mermaid
---
title: DFD Level 1 — Diagram 1.0 จัดการบัญชีผู้ใช้ และสิทธิ์เข้าถึง
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P11(("1.1<br/>จัดการบัญชีผู้ใช้<br/>(User Accounts)"))
    P12(("1.2<br/>จัดการสิทธิ์เข้าถึง<br/>(Permissions)"))
    P13(("1.3<br/>จัดการภาคเรียน<br/>(Semesters)"))
    ADMIN["ผู้ดูแลระบบ<br/>(Admin)"]
    D1[("D1 บัญชีผู้ใช้และสิทธิ์<br/>(Users & Permissions)")]
    D9[("D9 ข้อมูลภาคเรียน<br/>(คลังภายใน 1.0)")]
    ADMIN -->|"ข้อมูลเพิ่ม/แก้ไขบัญชีผู้ใช้"| P11
    D1 -->|"บัญชีผู้ใช้เดิม"| P11
    P11 -->|"บัญชีผู้ใช้ที่ปรับปรุง"| D1
    ADMIN -->|"ข้อมูลกำหนดสิทธิ์เข้าถึง"| P12
    D1 -->|"สิทธิ์เข้าถึงเดิม"| P12
    P12 -->|"สิทธิ์เข้าถึงที่ปรับปรุง"| D1
    ADMIN -->|"ข้อมูลเปิด/แก้ไขภาคเรียน"| P13
    D9 -->|"ข้อมูลภาคเรียนเดิม"| P13
    P13 -->|"ข้อมูลภาคเรียนที่ปรับปรุง"| D9
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P11,P12,P13 process
    class D1,D9 store
    class ADMIN entity
```

## ผัง 2.0 บันทึก/อนุมัติพฤติกรรม และคำนวณคะแนน

```mermaid
---
title: DFD Level 1 — Diagram 2.0 บันทึก/อนุมัติพฤติกรรม และคำนวณคะแนน
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P21(("2.1<br/>รับและบันทึก<br/>รายงานพฤติกรรม<br/>(Log Behavior)"))
    P22(("2.2<br/>ตรวจสอบและอนุมัติ<br/>รายการพฤติกรรม<br/>(Review Behavior)"))
    P23(("2.3<br/>คำนวณคะแนนและ<br/>สถานะความเสี่ยง<br/>(Calculate Scores)"))
    P24(("2.4<br/>จัดการเกณฑ์<br/>ตัด/เพิ่มคะแนน<br/>(Manage Rules)"))
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    C1["C1<br/>3.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    C3["C3<br/>6.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    D3[("D3 บันทึกพฤติกรรมและ<br/>เกณฑ์การตัด/เพิ่มคะแนน<br/>(Records & Rules)")]
    C2["C2<br/>5.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]
    STU["นักเรียน<br/>(Student)"]
    TEACH -->|"รายงานพฤติกรรมนักเรียน"| P21
    DISC -->|"ข้อมูลบันทึกพฤติกรรม"| P21
    C1 -->|"ข้อมูลขาด/สายเกินเกณฑ์"| P21
    C3 -->|"ตัดคะแนนผู้แจ้งเท็จ"| P21
    P21 -->|"บันทึกพฤติกรรมรออนุมัติ"| D3
    P21 -->|"ข้อมูลรายการพฤติกรรมที่รับแล้ว"| P22
    D3 -->|"รายการพฤติกรรมรออนุมัติ"| P22
    DISC -->|"ผลอนุมัติ/ไม่อนุมัติพฤติกรรม"| P22
    P22 -->|"สถานะพฤติกรรมที่พิจารณาแล้ว"| D3
    P22 -->|"รายการอนุมัติสำหรับปรับคะแนน"| P23
    C2 -->|"ข้อมูลคืนคะแนน"| P23
    D2 -->|"คะแนนและสถานะเสี่ยงเดิม"| P23
    D3 -->|"เกณฑ์และค่าตัด/เพิ่มคะแนน"| P23
    P23 -->|"คะแนนและสถานะเสี่ยงที่ปรับปรุง"| D2
    P23 -->|"คะแนน/สถานะความเสี่ยง"| STU
    DISC -->|"ข้อมูลกำหนดเกณฑ์ตัด/เพิ่มคะแนน"| P24
    D3 -->|"เกณฑ์ตัด/เพิ่มคะแนนเดิม"| P24
    P24 -->|"เกณฑ์ตัด/เพิ่มคะแนนที่ปรับปรุง"| D3
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P21,P22,P23,P24 process
    class D3,D2 store
    class TEACH,DISC,STU entity
    class C1,C3,C2 continuation
```

## ผัง 3.0 เช็คชื่อเข้าแถว

```mermaid
---
title: DFD Level 1 — Diagram 3.0 เช็คชื่อเข้าแถว
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P31(("3.1<br/>รับและตรวจสอบ<br/>ข้อมูลเข้าแถว<br/>(Check Attendance)"))
    P32(("3.2<br/>บันทึกเข้าแถวและ<br/>ปรับยอดสะสม<br/>(Save Attendance)"))
    P33(("3.3<br/>ตรวจเกณฑ์ขาด/สาย<br/>และส่งข้อมูลตัดคะแนน<br/>(Check Threshold)"))
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    D4[("D4 การเช็คชื่อเข้าแถว<br/>(Attendance)")]
    D10[("D10 ยอดขาด/สายสะสม<br/>(ข้อมูลสรุปภายใน 3.0)")]
    C1["C1<br/>3.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    TEACH -->|"บันทึกเข้าแถว (มา/สาย/ขาด)"| P31
    P31 -->|"ข้อมูลเข้าแถวที่ตรวจสอบแล้ว"| P32
    P32 -->|"บันทึกการเข้าแถว"| D4
    D10 -->|"ยอดขาด/สายสะสมเดิม"| P32
    P32 -->|"ยอดขาด/สายสะสมที่ปรับปรุง"| D10
    P32 -->|"รหัสนักเรียนและรายการล่าสุด"| P33
    D10 -->|"ยอดขาด/สายสะสมสำหรับประเมิน"| P33
    P33 -->|"ข้อมูลขาด/สายเกินเกณฑ์"| C1
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P31,P32,P33 process
    class D4,D10 store
    class TEACH entity
    class C1 continuation
```

## ผัง 4.0 บันทึกการละหมาด

```mermaid
---
title: DFD Level 1 — Diagram 4.0 บันทึกการละหมาด
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P41(("4.1<br/>รับและตรวจสอบ<br/>ข้อมูล QR ละหมาด<br/>(Check Prayer QR)"))
    P42(("4.2<br/>ตรวจข้อมูลสแกน<br/>และสถานะละหมาด<br/>(Validate Scan)"))
    P43(("4.3<br/>บันทึกการละหมาด<br/>(Save Prayer)"))
    STU["นักเรียน<br/>(Student)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    D5[("D5 บันทึกการละหมาด<br/>(Prayer Records)")]
    STU -->|"แสดง QR ละหมาด"| P41
    P41 -->|"รหัสนักเรียนจาก QR ละหมาด"| P42
    DISC -->|"สแกน QR/บันทึกสถานะ"| P42
    P42 -->|"ข้อมูลละหมาดที่ตรวจสอบแล้ว"| P43
    P43 -->|"บันทึกการละหมาด"| D5
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P41,P42,P43 process
    class D5 store
    class STU,DISC entity
```

## ผัง 5.0 พิจารณาอุทธรณ์

```mermaid
---
title: DFD Level 1 — Diagram 5.0 พิจารณาอุทธรณ์
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P51(("5.1<br/>รับและตรวจสอบ<br/>คำร้องอุทธรณ์<br/>(Submit Appeal)"))
    P52(("5.2<br/>พิจารณาคำร้อง<br/>อุทธรณ์<br/>(Review Appeal)"))
    P53(("5.3<br/>บันทึกผลอุทธรณ์<br/>และส่งข้อมูลคืนคะแนน<br/>(Resolve Appeal)"))
    STU["นักเรียน<br/>(Student)"]
    D6[("D6 คำร้องอุทธรณ์<br/>(Appeals)")]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    C2["C2<br/>5.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    STU -->|"ยื่นอุทธรณ์+หลักฐาน"| P51
    P51 -->|"คำร้องอุทธรณ์รอพิจารณา"| D6
    P51 -->|"ข้อมูลคำร้องที่รับแล้ว"| P52
    D6 -->|"คำร้องอุทธรณ์และสถานะเดิม"| P52
    DISC -->|"ผลการพิจารณา (คืนคะแนน)"| P52
    P52 -->|"ผลพิจารณาและจำนวนคะแนนคืน"| P53
    P53 -->|"คำร้องและสถานะหลังพิจารณา"| D6
    P53 -->|"สถานะ/ผลอุทธรณ์"| STU
    P53 -->|"ข้อมูลคืนคะแนน"| C2
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P51,P52,P53 process
    class D6 store
    class STU,DISC entity
    class C2 continuation
```

## ผัง 6.0 รับ/สอบสวนข้อมูลแจ้ง

```mermaid
---
title: DFD Level 1 — Diagram 6.0 รับ/สอบสวนข้อมูลแจ้ง
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P61(("6.1<br/>รับและตรวจสอบ<br/>ข้อมูลแจ้งเบาะแส<br/>(Receive Report)"))
    P62(("6.2<br/>สอบสวนข้อมูลแจ้ง<br/>และสรุปผล<br/>(Investigate)"))
    P63(("6.3<br/>ปิดเรื่องและ<br/>ส่งข้อมูลดำเนินการ<br/>(Close Report)"))
    STU["นักเรียน<br/>(Student)"]
    D7[("D7 ข้อมูลแจ้งเบาะแส<br/>(Informant Reports)")]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    C3["C3<br/>6.0 → 2.0<br/>(ระหว่างผังหลัก)"]
    C4["C4<br/>6.0 → 7.0<br/>(ระหว่างผังหลัก)"]
    STU -->|"แจ้งเบาะแส (ไม่เกิน 3 ครั้ง/วัน)"| P61
    D7 -->|"ประวัติเรื่องแจ้งและจำนวนรายวัน"| P61
    P61 -->|"เรื่องแจ้งที่รับใหม่"| D7
    P61 -->|"ข้อมูลเรื่องแจ้งที่รับแล้ว"| P62
    D7 -->|"เรื่องแจ้งและสถานะสอบสวน"| P62
    DISC -->|"ผลการสอบสวน"| P62
    P62 -->|"เรื่องแจ้งพร้อมผลการสอบสวน"| P63
    P63 -->|"เรื่องแจ้ง ผลสอบสวน และสถานะปิด"| D7
    P63 -->|"สถานะเรื่องแจ้ง"| STU
    P63 -->|"ตัดคะแนนผู้แจ้งเท็จ"| C3
    P63 -->|"แจ้งผู้ถูกกล่าวหา"| C4
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P61,P62,P63 process
    class D7 store
    class STU,DISC entity
    class C3,C4 continuation
```

## ผัง 7.0 ส่งข้อความ และแจ้งเตือน

```mermaid
---
title: DFD Level 1 — Diagram 7.0 ส่งข้อความ และแจ้งเตือน
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P71(("7.1<br/>รับและตรวจสอบ<br/>ข้อมูลข้อความ<br/>(Validate Message)"))
    P72(("7.2<br/>บันทึกข้อความ<br/>และผู้รับ<br/>(Save Message)"))
    P73(("7.3<br/>แสดงกล่องข้อความ<br/>และแจ้งเตือน<br/>(Deliver Message)"))
    C4["C4<br/>6.0 → 7.0<br/>(ระหว่างผังหลัก)"]
    STU["นักเรียน<br/>(Student)"]
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    D8[("D8 ข้อความ<br/>(Messages)")]
    PAR["ผู้ปกครอง<br/>(Parent)"]
    C4 -->|"แจ้งผู้ถูกกล่าวหา"| P71
    STU -->|"ส่งข้อความ"| P71
    TEACH -->|"ส่งข้อความ"| P71
    DISC -->|"ส่งข้อความ (CC ผู้ปกครอง)"| P71
    P71 -->|"ข้อความและผู้รับที่ตรวจสอบแล้ว"| P72
    P72 -->|"ข้อความและผู้รับที่บันทึก"| D8
    P72 -->|"รหัสอ้างอิงข้อความที่บันทึกแล้ว"| P73
    D8 -->|"ข้อความสำหรับแสดงและแจ้งเตือน"| P73
    P73 -->|"กล่องข้อความ/แจ้งเตือน"| STU
    P73 -->|"ข้อความ/แจ้งเตือน"| PAR
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P71,P72,P73 process
    class D8 store
    class STU,TEACH,DISC,PAR entity
    class C4 continuation
```

## ผัง 8.0 รายงานสรุป และแดชบอร์ด

```mermaid
---
title: DFD Level 1 — Diagram 8.0 รายงานสรุป และแดชบอร์ด
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
flowchart TB
    P81(("8.1<br/>รวบรวมข้อมูล<br/>ตามขอบเขตผู้ใช้งาน<br/>(Collect Report Data)"))
    P82(("8.2<br/>สรุปสถิติและ<br/>วิเคราะห์ความเสี่ยง<br/>(Summarize Reports)"))
    P83(("8.3<br/>แสดงรายงานสรุป<br/>และแดชบอร์ด<br/>(Present Reports)"))
    PAR["ผู้ปกครอง<br/>(Parent)"]
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]
    D3[("D3 บันทึกพฤติกรรมและ<br/>เกณฑ์การตัด/เพิ่มคะแนน<br/>(Records & Rules)")]
    D4[("D4 การเช็คชื่อเข้าแถว<br/>(Attendance)")]
    D5[("D5 บันทึกการละหมาด<br/>(Prayer Records)")]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    PAR -->|"เรียกดูข้อมูลบุตร"| P81
    D2 -->|"ข้อมูลคะแนน/ความเสี่ยง"| P81
    D3 -->|"ข้อมูลพฤติกรรม"| P81
    D4 -->|"ข้อมูลการเข้าแถว"| P81
    D5 -->|"ข้อมูลการละหมาด"| P81
    P81 -->|"คะแนน พฤติกรรม เข้าแถว และละหมาด"| P82
    P82 -->|"ผลสรุป สถิติ และข้อมูลนักเรียนเสี่ยง"| P83
    P83 -->|"รายงานสรุป/นักเรียนเสี่ยง"| DISC
    P83 -->|"พฤติกรรม/การมาเรียนของบุตร"| PAR
    P83 -->|"สถิติห้องเรียนที่ปรึกษา"| TEACH
    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4
    class P81,P82,P83 process
    class D2,D3,D4,D5 store
    class PAR,DISC,TEACH entity
```
