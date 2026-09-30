# DFD Level 1 — ระบบบริหารพฤติกรรมนักเรียน

แตกกระบวนการย่อยจาก DFD Level 0 (ดู [DFD_Level0.md](DFD_Level0.md)) ออกเป็น sub-process — โค้ด Mermaid สำหรับวางใน [mermaid.live](https://mermaid.live) แต่ละผังอยู่ในบล็อก ```mermaid แยกกัน

## ขอบเขต

- **แตก 6 กระบวนการ:** 1.0 บัญชี/สิทธิ์, 2.0 พฤติกรรม/คะแนน, 3.0 เช็คชื่อ, 5.0 อุทธรณ์, 6.0 ข้อมูลแจ้ง, 8.0 รายงาน
- **ไม่แตก 2 กระบวนการ:** 4.0 ละหมาด QR และ 7.0 ข้อความ (primitive — มีขั้นตอนเดียว ตามหลัก DFD ไม่ต้องแตกต่อ)
- ใช้ layout ELK และสไตล์เดียวกับ Level 0 (กระบวนการ = วงกลม, หน่วยงานภายนอก = สี่เหลี่ยม, คลังข้อมูล = ทรงกระบอก, ลูกศรอ่าน/เขียน = สองหัว)
- โหนดเส้นประ เช่น "จาก 3.0" / "ไป 2.0" คือเส้นทางไหลที่เชื่อมไปยังกระบวนการอื่น — คงไว้ให้สอดคล้องกับ Level 0 (balancing)

## ทะเบียนคลังข้อมูล (D1–D12)

| คลัง | ตารางจริง | ที่มา |
|---|---|---|
| D1 บัญชีผู้ใช้ | users | Level 0 |
| D2 ข้อมูลนักเรียนและคะแนน | students | Level 0 |
| D3 บันทึกพฤติกรรม | behavior_records | Level 0 (ระดับ 1 แยกเกณฑ์ออกเป็น D9) |
| D4 การเช็คชื่อเข้าแถว | attendances | Level 0 |
| D5 บันทึกการละหมาด | prayer_records | Level 0 |
| D6 คำร้องอุทธรณ์ | appeals | Level 0 |
| D7 ข้อมูลแจ้งเบาะแส | informant_reports | Level 0 |
| D8 ข้อความ | messages | Level 0 |
| D9 เกณฑ์การตัด/เพิ่มคะแนน | behavior_rules | เพิ่มใหม่ |
| D10 ภาคเรียน | semesters | เพิ่มใหม่ |
| D11 ประวัติการเลื่อนชั้น | student_promotions | เพิ่มใหม่ |
| D12 สิทธิ์เข้าถึงรายบทบาท | role_permissions | เพิ่มใหม่ |

---

## ผัง 1: แตกกระบวนการ 1.0 จัดการบัญชีผู้ใช้และสิทธิ์เข้าถึง

```mermaid
---
title: DFD Level 1 — Diagram 1.0 จัดการบัญชีผู้ใช้และสิทธิ์เข้าถึง
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    ADMIN["ผู้ดูแลระบบ<br/>(Admin)"]
    P11(("1.1<br/>จัดการบัญชีผู้ใช้<br/>(User Accounts)"))
    P12(("1.2<br/>จัดการข้อมูลนักเรียน<br/>และผู้ปกครอง<br/>(Student/Parent)"))
    P13(("1.3<br/>จัดการสิทธิ์เข้าถึง<br/>(Permissions)"))
    P14(("1.4<br/>จัดการภาคเรียนและ<br/>เลื่อนชั้นอัตโนมัติ<br/>(Semester/Promotion)"))
    D1[("D1 บัญชีผู้ใช้<br/>(Users)")]
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]
    D10[("D10 ภาคเรียน<br/>(Semesters)")]
    D11[("D11 ประวัติการเลื่อนชั้น<br/>(Promotions)")]
    D12[("D12 สิทธิ์เข้าถึงรายบทบาท<br/>(Role Permissions)")]

    ADMIN -->|"เพิ่ม/แก้ไขบัญชีผู้ใช้ ไฟล์ CSV"| P11
    P11 <-->|"บัญชีผู้ใช้"| D1
    P11 -->|"สร้างข้อมูลนักเรียนจาก CSV"| P12
    ADMIN -->|"ข้อมูลนักเรียน/ผู้ปกครอง"| P12
    P12 <-->|"ข้อมูลนักเรียน/ผู้ปกครอง"| D2
    ADMIN -->|"ตั้งค่าสิทธิ์รายโมดูล"| P13
    P13 <-->|"สิทธิ์รายบทบาท"| D12
    ADMIN -->|"เปิดภาคเรียนใหม่"| P14
    P14 <-->|"ข้อมูลภาคเรียน"| D10
    D2 -->|"คะแนนนักเรียน"| P14
    P14 -->|"บันทึกผลเลื่อนชั้น (คะแนน ≥ 50)"| D11

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    class ADMIN entity
    class P11,P12,P13,P14 process
    class D1,D2,D10,D11,D12 store
```

## ผัง 2: แตกกระบวนการ 2.0 บันทึก/อนุมัติพฤติกรรมและคำนวณคะแนน

```mermaid
---
title: DFD Level 1 — Diagram 2.0 พฤติกรรมและคะแนน
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    STU["นักเรียน<br/>(Student)"]
    F30["จาก 3.0<br/>(ตัดคะแนนอัตโนมัติ)"]
    F50["จาก 5.0<br/>(ข้อมูลคืนคะแนน)"]
    F60["จาก 6.0<br/>(ผู้แจ้งเท็จ)"]
    P21(("2.1<br/>บันทึกรายงาน<br/>พฤติกรรม<br/>(Log Behavior)"))
    P22(("2.2<br/>ตรวจสอบและอนุมัติ<br/>(Review & Approve)"))
    P23(("2.3<br/>คำนวณคะแนนและ<br/>สถานะความเสี่ยง<br/>(Calc Score)"))
    P24(("2.4<br/>จัดการเกณฑ์<br/>ตัด/เพิ่มคะแนน<br/>(Manage Rules)"))
    D3[("D3 บันทึกพฤติกรรม<br/>(Behavior Records)")]
    D9[("D9 เกณฑ์การตัด/เพิ่มคะแนน<br/>(Behavior Rules)")]
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]

    TEACH -->|"รายงานพฤติกรรม"| P21
    DISC -->|"บันทึกพฤติกรรม"| P21
    F30 -->|"ข้อมูลขาด/สายเกินเกณฑ์"| P21
    F60 -->|"รายการตัดคะแนนผู้แจ้งเท็จ"| P21
    P21 -->|"บันทึกรายการ (รออนุมัติ)"| D3
    D3 <-->|"รายการรออนุมัติ/สถานะ"| P22
    DISC -->|"อนุมัติ/ไม่อนุมัติ"| P22
    P22 -->|"รายการอนุมัติแล้ว"| P23
    F50 -->|"ข้อมูลคืนคะแนน"| P23
    D2 -->|"คะแนนปัจจุบัน"| P23
    P23 -->|"อัปเดตคะแนน/สถานะเสี่ยง"| D2
    P23 -->|"คะแนน/สถานะความเสี่ยง"| STU
    DISC -->|"กำหนดเกณฑ์"| P24
    P24 <-->|"เกณฑ์ตัด/เพิ่มคะแนน"| D9
    D9 -->|"ค่าคะแนนตามเกณฑ์"| P23

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef extflow fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:5 4
    class TEACH,DISC,STU entity
    class P21,P22,P23,P24 process
    class D2,D3,D9 store
    class F30,F50,F60 extflow
```

## ผัง 3: แตกกระบวนการ 3.0 เช็คชื่อเข้าแถว

```mermaid
---
title: DFD Level 1 — Diagram 3.0 เช็คชื่อเข้าแถว
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    P31(("3.1<br/>บันทึกการเข้าแถวรายวัน<br/>(Record Daily Attendance)"))
    P32(("3.2<br/>ตรวจเกณฑ์ขาด/สายและ<br/>ตัดคะแนนอัตโนมัติ<br/>(Auto Deduction)"))
    D4[("D4 การเช็คชื่อเข้าแถว<br/>(Attendance)")]
    F20["ไป 2.0<br/>(บันทึกตัดคะแนน)"]

    TEACH -->|"สถานะรายวัน (มา/สาย/ขาด)"| P31
    P31 -->|"บันทึกการเข้าแถว"| D4
    D4 -->|"จำนวนขาด/สายสะสม"| P32
    P32 -->|"ข้อมูลเกินเกณฑ์ (ไป 2.0)"| F20

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef extflow fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:5 4
    class TEACH entity
    class P31,P32 process
    class D4 store
    class F20 extflow
```

## ผัง 4: แตกกระบวนการ 5.0 พิจารณาอุทธรณ์

```mermaid
---
title: DFD Level 1 — Diagram 5.0 พิจารณาอุทธรณ์
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    STU["นักเรียน<br/>(Student)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    P51(("5.1<br/>ยื่นคำร้องอุทธรณ์<br/>(Submit Appeal)"))
    P52(("5.2<br/>พิจารณาคำร้อง<br/>(Review Appeal)"))
    P53(("5.3<br/>คืนคะแนนและแจ้งผล<br/>(Restore & Notify)"))
    D6[("D6 คำร้องอุทธรณ์<br/>(Appeals)")]
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]
    D3[("D3 บันทึกพฤติกรรม<br/>(Behavior Records)")]
    F20["ไป 2.0<br/>(ข้อมูลคืนคะแนน)"]

    STU -->|"เหตุผล+หลักฐาน"| P51
    P51 -->|"บันทึกคำร้อง (รอพิจารณา)"| D6
    D6 <-->|"คำร้อง/สถานะ"| P52
    DISC -->|"ผลการพิจารณา (คืนคะแนน)"| P52
    P52 -->|"รายการอนุมัติ"| P53
    P53 -->|"อัปเดตคะแนน (คืนคะแนน)"| D2
    P53 -->|"อัปเดตสถานะรายการพฤติกรรม"| D3
    P53 -->|"ผลอุทธรณ์"| STU
    P53 -->|"ข้อมูลคืนคะแนน (ไป 2.0)"| F20

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef extflow fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:5 4
    class STU,DISC entity
    class P51,P52,P53 process
    class D2,D3,D6 store
    class F20 extflow
```

## ผัง 5: แตกกระบวนการ 6.0 รับ/สอบสวนข้อมูลแจ้ง

```mermaid
---
title: DFD Level 1 — Diagram 6.0 รับ/สอบสวนข้อมูลแจ้ง
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    STU["นักเรียน<br/>(Student)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    P61(("6.1<br/>รับเรื่องแจ้ง<br/>(Receive Tip)"))
    P62(("6.2<br/>สอบสวนข้อเท็จจริง<br/>(Investigate)"))
    P63(("6.3<br/>ปิดเรื่องและ<br/>ดำเนินการ<br/>(Close & Act)"))
    D7[("D7 ข้อมูลแจ้งเบาะแส<br/>(Informant Reports)")]
    F20["ไป 2.0<br/>(ตัดคะแนนผู้แจ้งเท็จ)"]
    F70["ไป 7.0<br/>(ข้อความแจ้งผู้ถูกกล่าวหา)"]

    STU -->|"เรื่องแจ้ง+หลักฐาน (ไม่เกิน 3 ครั้ง/วัน)"| P61
    P61 -->|"บันทึกเรื่องแจ้ง (เรื่องใหม่)"| D7
    D7 <-->|"เรื่องแจ้ง/สถานะ"| P62
    DISC -->|"รับเรื่อง/สอบสวน"| P62
    P62 -->|"เรื่องที่สอบสวนแล้ว"| P63
    DISC -->|"ผลการสอบสวน"| P63
    P63 -->|"สถานะปิดเรื่อง"| D7
    P63 -->|"บันทึกตัดคะแนนผู้แจ้งเท็จ (ไป 2.0)"| F20
    P63 -->|"ข้อความแจ้งผู้ถูกกล่าวหา (ไป 7.0)"| F70
    P63 -->|"สถานะเรื่องแจ้ง"| STU

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    classDef extflow fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:5 4
    class STU,DISC entity
    class P61,P62,P63 process
    class D7 store
    class F20,F70 extflow
```

## ผัง 6: แตกกระบวนการ 8.0 รายงานสรุปและแดชบอร์ด

```mermaid
---
title: DFD Level 1 — Diagram 8.0 รายงานสรุปและแดชบอร์ด
config:
  layout: elk
  flowchart:
    nodeSpacing: 50
    rankSpacing: 80
---
%%{init: {"flowchart": {"defaultRenderer": "elk"}}}%%
flowchart TB
    PAR["ผู้ปกครอง<br/>(Parent)"]
    TEACH["ครูที่ปรึกษา<br/>(Teacher)"]
    DISC["ฝ่ายปกครอง<br/>(Discipline Staff)"]
    P81(("8.1<br/>แดชบอร์ดรายบทบาท<br/>(Dashboards)"))
    P82(("8.2<br/>รายงานสรุปพฤติกรรม<br/>(Behavior Report)"))
    P83(("8.3<br/>รายงานเข้าแถว/<br/>ละหมาดรายเดือน<br/>(Monthly Reports)"))
    P84(("8.4<br/>ติดตามนักเรียน<br/>กลุ่มเสี่ยง<br/>(Risk Monitoring)"))
    D2[("D2 ข้อมูลนักเรียนและ<br/>คะแนนพฤติกรรม<br/>(Students & Scores)")]
    D3[("D3 บันทึกพฤติกรรม<br/>(Behavior Records)")]
    D4[("D4 การเช็คชื่อเข้าแถว<br/>(Attendance)")]
    D5[("D5 บันทึกการละหมาด<br/>(Prayer Records)")]

    PAR -->|"เรียกดูข้อมูลบุตร"| P81
    D2 -->|"ข้อมูลคะแนน/ความเสี่ยง"| P81
    D3 -->|"ข้อมูลพฤติกรรม"| P82
    D4 -->|"ข้อมูลการเข้าแถว"| P83
    D5 -->|"ข้อมูลการละหมาด"| P83
    D2 -->|"สถานะความเสี่ยง"| P84
    P81 -->|"พฤติกรรม/การมาเรียนของบุตร"| PAR
    P81 -->|"สถิติห้องเรียนที่ปรึกษา"| TEACH
    P82 -->|"รายงานสรุปพฤติกรรม (Excel/พิมพ์)"| DISC
    P83 -->|"รายงานเข้าแถว/ละหมาดรายเดือน"| DISC
    P84 -->|"รายชื่อนักเรียนเสี่ยง"| DISC

    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142
    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142
    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142
    class PAR,TEACH,DISC entity
    class P81,P82,P83,P84 process
    class D2,D3,D4,D5 store
```
