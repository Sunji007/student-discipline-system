#!/usr/bin/env python
# -*- coding: utf-8 -*-
"""
สร้าง DFD Level 1 สำหรับระบบบริหารงานวินัยและติดตามพฤติกรรมนักเรียน
โดยใช้ Graphviz เพื่อควบคุมตำแหน่งและป้องกันเส้นทับกัน
"""

from graphviz import Digraph

def create_dfd_level_1():
    # สร้าง graph ขนาด A3 landscape
    dot = Digraph(comment='DFD Level 1 - Student Discipline System')
    dot.attr(rankdir='TB', splines='ortho', nodesep='1.5', ranksep='1.2')
    dot.attr(size='16.5,11.7!', ratio='fill', dpi='300')
    dot.attr(fontname='Arial', fontsize='14')

    # กำหนด style สำหรับ External Entities (สี่เหลี่ยม สีแดง)
    ext_style = {
        'shape': 'box',
        'style': 'filled',
        'fillcolor': '#FF6B6B',
        'fontcolor': 'white',
        'color': '#C92A2A',
        'penwidth': '2',
        'fontname': 'Arial',
        'fontsize': '12',
        'width': '1.8',
        'height': '0.8'
    }

    # กำหนด style สำหรับ Processes (วงกลม สีน้ำเงิน)
    proc_style = {
        'shape': 'circle',
        'style': 'filled',
        'fillcolor': '#4A90E2',
        'fontcolor': 'white',
        'color': '#2E5C8A',
        'penwidth': '2',
        'fontname': 'Arial',
        'fontsize': '11',
        'width': '1.6',
        'height': '1.6',
        'fixedsize': 'true'
    }

    # กำหนด style สำหรับ Data Stores (สี่เหลี่ยมแบน สีเขียว)
    data_style = {
        'shape': 'cylinder',
        'style': 'filled',
        'fillcolor': '#50C878',
        'fontcolor': 'white',
        'color': '#2E8B57',
        'penwidth': '2',
        'fontname': 'Arial',
        'fontsize': '10',
        'width': '1.5',
        'height': '0.6'
    }

    # ========================
    # LAYER 1: EXTERNAL ENTITIES (TOP)
    # ========================
    with dot.subgraph(name='cluster_external') as c:
        c.attr(label='External Entities - ผู้ใช้งานระบบ', style='dashed', color='gray', fontsize='16')
        c.node('Admin', 'ผู้ดูแลระบบ\nAdmin', **ext_style)
        c.node('Discipline', 'เจ้าหน้าที่ฝ่ายปกครอง\nDiscipline Staff', **ext_style)
        c.node('Teacher', 'ครูที่ปรึกษา\nTeacher', **ext_style)
        c.node('Student', 'นักเรียน\nStudent', **ext_style)
        c.node('Parent', 'ผู้ปกครอง\nParent', **ext_style)

    # ========================
    # LAYER 2: PROCESSES (MIDDLE) - แยกเป็น 3 แถว
    # ========================

    # แถวที่ 1: Master Data & Reports
    with dot.subgraph(name='cluster_proc1') as c:
        c.attr(rank='same')
        c.node('P1', '1.0\nจัดการข้อมูล\nผู้ใช้และหลัก', **proc_style)
        c.node('P8', '8.0\nรายงานและ\nแดชบอร์ด', **proc_style)

    # แถวที่ 2: Activities
    with dot.subgraph(name='cluster_proc2') as c:
        c.attr(rank='same')
        c.node('P2', '2.0\nเช็กชื่อ\nเข้าแถว', **proc_style)
        c.node('P6', '6.0\nบันทึก\nละหมาด', **proc_style)
        c.node('P7', '7.0\nระบบ\nข้อความ', **proc_style)

    # แถวที่ 3: Behavior Management
    with dot.subgraph(name='cluster_proc3') as c:
        c.attr(rank='same')
        c.node('P3', '3.0\nจัดการคะแนน\nพฤติกรรม', **proc_style)
        c.node('P4', '4.0\nจัดการ\nเบาะแส', **proc_style)
        c.node('P5', '5.0\nจัดการ\nอุทธรณ์', **proc_style)

    # ========================
    # LAYER 3: DATA STORES (BOTTOM) - แบ่งเป็น 3 กลุ่ม
    # ========================

    # กลุ่ม 1: User Data
    with dot.subgraph(name='cluster_ds1') as c:
        c.attr(label='User Data', style='dashed', color='green', fontsize='12')
        c.attr(rank='same')
        c.node('D1', 'D1: users', **data_style)
        c.node('D2', 'D2: students', **data_style)
        c.node('D3', 'D3: teachers', **data_style)
        c.node('D4', 'D4: parents', **data_style)

    # กลุ่ม 2: Activity Data
    with dot.subgraph(name='cluster_ds2') as c:
        c.attr(label='Activity Data', style='dashed', color='green', fontsize='12')
        c.attr(rank='same')
        c.node('D5', 'D5: attendances', **data_style)
        c.node('D10', 'D10: prayer_records', **data_style)
        c.node('D12', 'D12: semesters', **data_style)

    # กลุ่ม 3: Behavior Data
    with dot.subgraph(name='cluster_ds3') as c:
        c.attr(label='Behavior Data', style='dashed', color='green', fontsize='12')
        c.attr(rank='same')
        c.node('D6', 'D6: behavior_rules', **data_style)
        c.node('D7', 'D7: behavior_records', **data_style)
        c.node('D8', 'D8: informant_reports', **data_style)
        c.node('D9', 'D9: appeals', **data_style)

    # กลุ่ม 4: Communication
    with dot.subgraph(name='cluster_ds4') as c:
        c.attr(rank='same')
        c.node('D11', 'D11: messages', **data_style)

    # ========================
    # DATA FLOWS: External Entities -> Processes
    # ========================

    # Admin
    dot.edge('Admin', 'P1', label='ข้อมูลผู้ใช้', color='#333', fontsize='10')
    dot.edge('Admin', 'P8', label='ขอรายงาน', color='#333', fontsize='10')

    # Discipline
    dot.edge('Discipline', 'P2', label='บันทึกเช็กชื่อ', color='#333', fontsize='10')
    dot.edge('Discipline', 'P3', label='ตัด/เพิ่มคะแนน', color='#333', fontsize='10')
    dot.edge('Discipline', 'P4', label='ตรวจสอบเบาะแส', color='#333', fontsize='10')
    dot.edge('Discipline', 'P5', label='พิจารณาอุทธรณ์', color='#333', fontsize='10')
    dot.edge('Discipline', 'P6', label='บันทึกละหมาด', color='#333', fontsize='10')

    # Teacher
    dot.edge('Teacher', 'P2', label='เช็กชื่อแถว', color='#333', fontsize='10')
    dot.edge('Teacher', 'P3', label='บันทึกพฤติกรรม', color='#333', fontsize='10')
    dot.edge('Teacher', 'P4', label='แจ้งเบาะแส', color='#333', fontsize='10')
    dot.edge('Teacher', 'P6', label='เช็กละหมาด', color='#333', fontsize='10')
    dot.edge('Teacher', 'P7', label='ส่งข้อความ', color='#333', fontsize='10')
    dot.edge('Teacher', 'P8', label='ขอรายงาน', color='#333', fontsize='10')

    # Student
    dot.edge('Student', 'P4', label='แจ้งเบาะแส', color='#333', fontsize='10')
    dot.edge('Student', 'P5', label='ยื่นอุทธรณ์', color='#333', fontsize='10')
    dot.edge('Student', 'P6', label='สแกนละหมาด', color='#333', fontsize='10')
    dot.edge('Student', 'P7', label='ส่งข้อความ', color='#333', fontsize='10')

    # Parent
    dot.edge('Parent', 'P7', label='ส่งข้อความ', color='#333', fontsize='10')
    dot.edge('Parent', 'P8', label='ติดตามบุตร', color='#333', fontsize='10')

    # ========================
    # DATA FLOWS: Processes -> External Entities (Return)
    # ========================

    dot.edge('P1', 'Admin', label='สถานะ', color='#666', style='dashed', fontsize='10')
    dot.edge('P3', 'Student', label='ผลคะแนน', color='#666', style='dashed', fontsize='10')
    dot.edge('P4', 'Student', label='สถานะเบาะแส', color='#666', style='dashed', fontsize='10')
    dot.edge('P5', 'Student', label='ผลอุทธรณ์', color='#666', style='dashed', fontsize='10')
    dot.edge('P6', 'Student', label='สถิติละหมาด', color='#666', style='dashed', fontsize='10')
    dot.edge('P7', 'Teacher', label='ข้อความ', color='#666', style='dashed', fontsize='10')
    dot.edge('P7', 'Student', label='ข้อความ', color='#666', style='dashed', fontsize='10')
    dot.edge('P7', 'Parent', label='ข้อความ', color='#666', style='dashed', fontsize='10')
    dot.edge('P8', 'Admin', label='รายงาน', color='#666', style='dashed', fontsize='10')
    dot.edge('P8', 'Discipline', label='รายงาน', color='#666', style='dashed', fontsize='10')
    dot.edge('P8', 'Teacher', label='รายงาน', color='#666', style='dashed', fontsize='10')
    dot.edge('P8', 'Student', label='แดชบอร์ด', color='#666', style='dashed', fontsize='10')
    dot.edge('P8', 'Parent', label='รายงาน', color='#666', style='dashed', fontsize='10')

    # ========================
    # DATA FLOWS: Process -> Process
    # ========================

    dot.edge('P2', 'P3', label='ขาด 3 ครั้ง\nตัดคะแนน -5', color='#FF6B00', penwidth='2', fontsize='10')
    dot.edge('P4', 'P3', label='เบาะแสเป็นจริง\nตัดคะแนน', color='#FF6B00', penwidth='2', fontsize='10')

    # ========================
    # DATA FLOWS: Processes <-> Data Stores
    # ========================

    # P1 <-> User Data
    dot.edge('P1', 'D1', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('P1', 'D2', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('P1', 'D3', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('P1', 'D4', label='R/W', color='#2E8B57', fontsize='9', dir='both')

    # P2 -> D5, D2
    dot.edge('P2', 'D5', label='Write', color='#2E8B57', fontsize='9')
    dot.edge('D2', 'P2', label='Read', color='#2E8B57', fontsize='9', style='dashed')

    # P3 <-> D6, D7, D2
    dot.edge('P3', 'D6', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('P3', 'D7', label='Write', color='#2E8B57', fontsize='9')
    dot.edge('P3', 'D2', label='Update', color='#2E8B57', fontsize='9', dir='both')

    # P4 <-> D8
    dot.edge('P4', 'D8', label='R/W', color='#2E8B57', fontsize='9', dir='both')

    # P5 <-> D9, D7, D2
    dot.edge('P5', 'D9', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('D7', 'P5', label='Read', color='#2E8B57', fontsize='9', style='dashed')
    dot.edge('P5', 'D2', label='คืนคะแนน', color='#2E8B57', fontsize='9')

    # P6 <-> D10, D2
    dot.edge('P6', 'D10', label='R/W', color='#2E8B57', fontsize='9', dir='both')
    dot.edge('D2', 'P6', label='Read', color='#2E8B57', fontsize='9', style='dashed')

    # P7 <-> D11
    dot.edge('P7', 'D11', label='R/W', color='#2E8B57', fontsize='9', dir='both')

    # P8 -> All Data Stores (Read only)
    dot.edge('D1', 'P8', label='Read', color='#2E8B57', fontsize='9', style='dashed')
    dot.edge('D2', 'P8', label='Read', color='#2E8B57', fontsize='9', style='dashed')
    dot.edge('D5', 'P8', label='Read', color='#2E8B57', fontsize='9', style='dashed')
    dot.edge('D7', 'P8', label='Read', color='#2E8B57', fontsize='9', style='dashed')
    dot.edge('D10', 'P8', label='Read', color='#2E8B57', fontsize='9', style='dashed')

    # Semester references
    dot.edge('D12', 'P1', label='Check', color='#999', fontsize='9', style='dotted')
    dot.edge('D12', 'P2', label='Check', color='#999', fontsize='9', style='dotted')
    dot.edge('D12', 'P3', label='Check', color='#999', fontsize='9', style='dotted')
    dot.edge('D12', 'P6', label='Check', color='#999', fontsize='9', style='dotted')

    return dot

if __name__ == '__main__':
    print("Creating DFD Level 1...")
    dfd = create_dfd_level_1()

    # Save as PNG
    output_path = 'dfd_level_1_final'
    dfd.render(output_path, format='png', cleanup=True)
    print(f"✅ DFD Level 1 saved as: {output_path}.png")

    # Also save SVG (vector format - ขยายได้ไม่เสียคุณภาพ)
    dfd.render(output_path, format='svg', cleanup=True)
    print(f"✅ DFD Level 1 saved as: {output_path}.svg")

    # Save source DOT file
    with open(f'{output_path}.dot', 'w', encoding='utf-8') as f:
        f.write(dfd.source)
    print(f"✅ DFD Level 1 source saved as: {output_path}.dot")

    print("\n📊 สรุป:")
    print("  - External Entities: 5 entities (สีแดง)")
    print("  - Processes: 8 processes (สีน้ำเงิน)")
    print("  - Data Stores: 12 data stores (สีเขียว)")
    print("  - เส้น Data Flows ได้รับการจัดเรียงแบบ orthogonal เพื่อไม่ให้ทับกัน")
