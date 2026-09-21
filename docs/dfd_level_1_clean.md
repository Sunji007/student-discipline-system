# DFD Level 1 - เวอร์ชันเส้นไม่ทับกัน (Clean Version)
## ระบบสารสนเทศบริหารงานวินัยและติดตามพฤติกรรมนักเรียน

---

## แผนภาพ DFD Level 1 (Left-Right Layout)

```mermaid
graph LR
%% ==========================================
%% LEFT SIDE: EXTERNAL ENTITIES (INPUT)
%% ==========================================
    Admin["👤<br>ผู้ดูแลระบบ<br>Admin"]
    Discipline["👮<br>เจ้าหน้าที่ฝ่ายปกครอง<br>Discipline Staff"]
    Teacher["👨‍🏫<br>ครูที่ปรึกษา<br>Teacher"]

%% ==========================================
%% CENTER: PROCESSES (VERTICAL ARRANGEMENT)
%% ==========================================
    P1(["1.0<br>จัดการข้อมูล<br>ผู้ใช้และหลัก"])
    P2(["2.0<br>เช็กชื่อ<br>เข้าแถว"])
    P3(["3.0<br>จัดการคะแนน<br>พฤติกรรม"])
    P4(["4.0<br>จัดการ<br>เบาะแส"])
    P5(["5.0<br>จัดการ<br>อุทธรณ์"])
    P6(["6.0<br>บันทึก<br>ละหมาด"])
    P7(["7.0<br>ระบบ<br>ข้อความ"])
    P8(["8.0<br>รายงานและ<br>แดชบอร์ด"])

%% ==========================================
%% RIGHT SIDE: EXTERNAL ENTITIES (OUTPUT)
%% ==========================================
    Student["🧑‍🎓<br>นักเรียน<br>Student"]
    Parent["👨‍👩‍👦<br>ผู้ปกครอง<br>Parent"]

%% ==========================================
%% BOTTOM: DATA STORES
%% ==========================================
    D1[("D1<br>users")]
    D2[("D2<br>students")]
    D3[("D3<br>teachers")]
    D4[("D4<br>parents")]
    D5[("D5<br>attendances")]
    D6[("D6<br>behavior_rules")]
    D7[("D7<br>behavior_records")]
    D8[("D8<br>informant_reports")]
    D9[("D9<br>appeals")]
    D10[("D10<br>prayer_records")]
    D11[("D11<br>messages")]
    D12[("D12<br>semesters")]

%% ==========================================
%% FLOWS: ADMIN -> PROCESSES
%% ==========================================
    Admin -->|จัดการผู้ใช้| P1
    Admin -->|ขอรายงาน| P8

%% ==========================================
%% FLOWS: DISCIPLINE -> PROCESSES
%% ==========================================
    Discipline -->|บันทึกเช็กชื่อ| P2
    Discipline -->|ตัด/เพิ่มคะแนน| P3
    Discipline -->|ตรวจสอบเบาะแส| P4
    Discipline -->|พิจารณาอุทธรณ์| P5
    Discipline -->|บันทึกละหมาด| P6

%% ==========================================
%% FLOWS: TEACHER -> PROCESSES
%% ==========================================
    Teacher -->|เช็กชื่อแถว| P2
    Teacher -->|บันทึกพฤติกรรม| P3
    Teacher -->|แจ้งเบาะแส| P4
    Teacher -->|เช็กละหมาด| P6
    Teacher -->|ส่งข้อความ| P7
    Teacher -->|ขอรายงานห้อง| P8

%% ==========================================
%% FLOWS: STUDENT -> PROCESSES
%% ==========================================
    Student -->|แจ้งเบาะแส| P4
    Student -->|ยื่นอุทธรณ์| P5
    Student -->|สแกนละหมาด| P6
    Student -->|ส่งข้อความ| P7

%% ==========================================
%% FLOWS: PARENT -> PROCESSES
%% ==========================================
    Parent -->|ส่งข้อความ| P7

%% ==========================================
%% FLOWS: PROCESSES -> STUDENTS/PARENTS
%% ==========================================
    P3 -->|ผลคะแนน| Student
    P4 -->|สถานะเบาะแส| Student
    P5 -->|ผลอุทธรณ์| Student
    P6 -->|สถิติละหมาด| Student
    P7 -->|ข้อความตอบกลับ| Student
    P7 -->|ข้อความตอบกลับ| Parent
    P7 -->|ข้อความตอบกลับ| Teacher
    P8 -->|แดชบอร์ด| Student
    P8 -->|รายงานบุตร| Parent
    P8 -->|รายงาน| Admin
    P8 -->|รายงาน| Discipline
    P8 -->|รายงาน| Teacher

%% ==========================================
%% FLOWS: PROCESS <-> DATA STORES
%% ==========================================
    P1 <-->|R/W| D1
    P1 <-->|R/W| D2
    P1 <-->|R/W| D3
    P1 <-->|R/W| D4

    P2 -->|Write| D5
    P2 -.->|Read| D2
    P2 -->|ขาด 3 ครั้ง| P3

    P3 <-->|R/W| D6
    P3 -->|Write| D7
    P3 <-->|Update| D2

    P4 <-->|R/W| D8
    P4 -->|ผิดจริง| P3

    P5 <-->|R/W| D9
    P5 -.->|Read| D7
    P5 -->|คืนคะแนน| D2

    P6 <-->|R/W| D10
    P6 -.->|Read| D2

    P7 <-->|R/W| D11

    P8 -.->|Read| D1
    P8 -.->|Read| D2
    P8 -.->|Read| D5
    P8 -.->|Read| D7
    P8 -.->|Read| D10
    P8 -.->|Read| D12

    P1 -.->|Check| D12
    P2 -.->|Check| D12
    P3 -.->|Check| D12
    P6 -.->|Check| D12

%% ==========================================
%% STYLING
%% ==========================================
    classDef extStyle fill:#FF6B6B,stroke:#C92A2A,stroke-width:3px,color:#fff,font-weight:bold
    classDef procStyle fill:#4A90E2,stroke:#2E5C8A,stroke-width:3px,color:#fff,font-weight:bold
    classDef dataStyle fill:#50C878,stroke:#2E8B57,stroke-width:2px,color:#fff

    class Admin,Discipline,Teacher,Student,Parent extStyle
    class P1,P2,P3,P4,P5,P6,P7,P8 procStyle
    class D1,D2,D3,D4,D5,D6,D7,D8,D9,D10,D11,D12 dataStyle
```

---

## ข้อจำกัดของ Mermaid

Mermaid มีข้อจำกัดในการควบคุมตำแหน่งอย่างละเอียด ถ้าเส้นยังทับกันอยู่ แนะนำให้ใช้เครื่องมือเหล่านี้แทน:

### 🎨 เครื่องมือแนะนำสำหรับวาด DFD (ไม่มีเส้นทับกัน)

1. **Draw.io (diagrams.net)** ⭐ แนะนำสูงสุด
   - ฟรี 100%
   - ควบคุม waypoint ได้เอง
   - มี DFD shapes สำเร็จรูป
   - https://app.diagrams.net

2. **Lucidchart**
   - Auto-routing ดีมาก
   - Template DFD สำเร็จรูป
   - https://lucidchart.com

3. **Visual Paradigm Online**
   - มี DFD Level 0, 1, 2 templates
   - Auto-align ฉลาด
   - https://online.visual-paradigm.com

---

## ทางเลือก: ใช้ไฟล์ Draw.io Template

ฉันแนะนำให้ใช้ Draw.io เพราะ:
- ✅ ฟรีและใช้งานง่าย
- ✅ ลากเส้นเองได้ 100%
- ✅ Export PNG/SVG/PDF คุณภาพสูง
- ✅ มี DFD symbols มาตรฐาน

คุณต้องการให้ฉัน:
1. สร้างไฟล์ Draw.io (.drawio) พร้อม template DFD นี้ไหม?
2. หรือสร้างแผนภาพแบบ manual เป็นรูปภาพ SVG?
