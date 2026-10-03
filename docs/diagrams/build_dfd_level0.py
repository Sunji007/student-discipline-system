"""Build a monochrome DFD Level 0 from the application's logical data flows.

Run: python docs/diagrams/build_dfd_level0.py
The HTML contains nine same-level fragments, arranged on five print sheets.
Repeated entity/store IDs refer to the same logical object throughout.
"""
from html import escape
from itertools import combinations
from pathlib import Path
import json

ROOT = Path(__file__).resolve().parents[2]
OUTPUT = ROOT / 'docs' / 'dfd-level0-student-discipline.html'
ENTITIES = {
    'E1': ['ผู้ดูแลระบบ'], 'E2': ['ฝ่ายปกครอง'], 'E3': ['ครู'],
    'E4': ['นักเรียน'], 'E5': ['ผู้ปกครอง'],
}
STORES = {
    'D1': (['บัญชีผู้ใช้และสิทธิ์'], ['users', 'role_permissions']),
    'D2': (['ข้อมูลนักเรียน', 'คะแนนและสถานะความเสี่ยง'], ['students']),
    'D3': (['บันทึกพฤติกรรม', 'และเกณฑ์คะแนน'], ['behavior_records', 'behavior_rules']),
    'D4': (['การเช็คชื่อเข้าแถว'], ['attendances']),
    'D5': (['การละหมาดและการแก้ไข'], ['prayer_records', 'prayer_corrections']),
    'D6': (['คำร้องอุทธรณ์'], ['appeals']),
    'D7': (['ข้อมูลแจ้งเบาะแส'], ['informant_reports']),
    'D8': (['ข้อความ'], ['messages']),
    'D9': (['ข้อมูลหลักและภาคเรียน'], ['teachers', 'parents', 'discipline_staff',
                                'departments', 'teacher_advisory_rooms', 'semesters']),
    'D10': (['ประวัติการเลื่อนชั้น'], ['student_promotions']),
}
CONNECTORS = {
    'C1': ['ข้อมูลภาคเรียนใหม่', '1.0 → 9.0'],
    'C2': ['สรุปผลการเลื่อนชั้น', '9.0 → 1.0'],
}

def flow(node, *label):
    return {'node': node, 'label': list(label)}

PROCESSES = [
    {'id': '1.0', 'title': ['จัดการข้อมูลหลัก', 'บัญชีผู้ใช้และสิทธิ์'],
     'sources': ['routes/web.php', 'app/Http/Controllers/Auth/LoginController.php',
                 'app/Http/Controllers/Admin', 'app/Models/Semester.php'],
     'inputs': [flow('E1', 'ข้อมูลเข้าสู่ระบบ / ข้อมูลหลัก', 'บัญชี สิทธิ์ และภาคเรียน'),
                flow('E2', 'ข้อมูลเข้าสู่ระบบ / บทบาท'), flow('E3', 'ข้อมูลเข้าสู่ระบบ / บทบาท'),
                flow('E4', 'ข้อมูลเข้าสู่ระบบ / บทบาท'), flow('E5', 'ข้อมูลเข้าสู่ระบบ / บทบาท'),
                flow('D1', 'บัญชีผู้ใช้และสิทธิ์เดิม'), flow('D2', 'ข้อมูลนักเรียนเดิม'),
                flow('D9', 'ข้อมูลหลักและภาคเรียนเดิม'), flow('C2', 'สรุปผลประมวลผลเลื่อนชั้น')],
     'outputs': [flow('E1', 'ผลตรวจสอบสิทธิ์ / ผลจัดการข้อมูล'), flow('E2', 'ผลเข้าสู่ระบบและสิทธิ์ใช้งาน'),
                 flow('E3', 'ผลเข้าสู่ระบบและสิทธิ์ใช้งาน'), flow('E4', 'ผลเข้าสู่ระบบและสิทธิ์ใช้งาน'),
                 flow('E5', 'ผลเข้าสู่ระบบและสิทธิ์ใช้งาน'), flow('D1', 'บัญชีผู้ใช้และสิทธิ์ที่ปรับปรุง'),
                 flow('D2', 'ข้อมูลนักเรียนที่ปรับปรุง'), flow('D9', 'ข้อมูลหลักและภาคเรียนที่ปรับปรุง'),
                 flow('C1', 'ข้อมูลภาคเรียนใหม่')],
     'note': 'ข้อมูลหลักครอบคลุมครู ผู้ปกครอง ฝ่ายปกครอง หน่วยงาน ห้องที่ปรึกษา และภาคเรียน'},
    {'id': '2.0', 'title': ['จัดการพฤติกรรม', 'และคะแนนวินัย'],
     'sources': ['app/Http/Controllers/Teacher/BehaviorRecordController.php',
                 'app/Http/Controllers/Discipline/BehaviorRecordController.php',
                 'app/Http/Controllers/Discipline/BehaviorRuleController.php'],
     'inputs': [flow('E3', 'ข้อมูลพฤติกรรมและหลักฐาน'),
                flow('E2', 'ข้อมูลพฤติกรรม / ผลอนุมัติ', 'เกณฑ์ตัดและเพิ่มคะแนน'),
                flow('D2', 'ข้อมูลนักเรียนและคะแนนเดิม'), flow('D3', 'บันทึกพฤติกรรมและเกณฑ์คะแนน'),
                flow('D9', 'ภาคเรียนและห้องที่ปรึกษา'), flow('D6', 'ผลอุทธรณ์ที่ใช้คำนวณคะแนน')],
     'outputs': [flow('E3', 'ผลบันทึกและสถานะอนุมัติ'), flow('E2', 'รายการพฤติกรรมและผลดำเนินการ'),
                 flow('D2', 'คะแนนและความเสี่ยงที่ปรับปรุง'),
                 flow('D3', 'บันทึกพฤติกรรม / เกณฑ์', 'และสถานะอนุมัติที่ปรับปรุง')],
     'note': 'ครูบันทึกพฤติกรรมเพื่อรอฝ่ายปกครองอนุมัติ ข้อมูลที่อนุมัติใช้คำนวณคะแนน'},
    {'id': '3.0', 'title': ['บันทึกการเข้าแถว', 'และตัดคะแนนอัตโนมัติ'],
     'sources': ['app/Http/Controllers/Teacher/AttendanceController.php',
                 'app/Services/AttendanceDeductionService.php'],
     'inputs': [flow('E3', 'ข้อมูลมา / สาย / ขาด'), flow('D2', 'รายชื่อนักเรียนและคะแนนเดิม'),
                flow('D4', 'ประวัติการเข้าแถวสะสม'), flow('D3', 'เกณฑ์และรายการตัดคะแนนเดิม'),
                flow('D9', 'ภาคเรียนและห้องที่ปรึกษา'), flow('D6', 'ผลอุทธรณ์ที่ใช้คำนวณคะแนน')],
     'outputs': [flow('E3', 'ผลบันทึกการเข้าแถว'), flow('D4', 'รายการเข้าแถวที่ปรับปรุง'),
                 flow('D3', 'เกณฑ์และบันทึกตัดคะแนนอัตโนมัติ'),
                 flow('D2', 'คะแนนและความเสี่ยงที่ปรับปรุง')],
     'note': 'มาสายสะสม 3 ครั้งเท่ากับขาด 1 ครั้ง และขาดสะสมทุก 3 ครั้งตัด 5 คะแนน'},
    {'id': '4.0', 'title': ['บันทึกและติดตาม', 'การละหมาด'],
     'sources': ['app/Http/Controllers/Prayer/PrayerController.php', 'routes/web.php'],
     'inputs': [flow('E4', 'ข้อมูล QR / Barcode', 'และสถานะละหมาดของตน'),
                flow('E2', 'ข้อมูลสแกน / สถานะละหมาด', 'และรายการแก้ไข'),
                flow('D2', 'ข้อมูลนักเรียน'), flow('D5', 'ประวัติละหมาดและการแก้ไข'),
                flow('D9', 'ข้อมูลภาคเรียน')],
     'outputs': [flow('E4', 'ผลบันทึกละหมาดของตน'), flow('E2', 'ผลบันทึกและผลแก้ไขละหมาด'),
                 flow('D5', 'รายการละหมาดและการแก้ไข')],
     'note': 'การบันทึกตรวจสอบบทบาทและรหัสนักเรียน ส่วนรายงานสรุปแสดงในกระบวนการ 8.0'},
    {'id': '5.0', 'title': ['รับและพิจารณา', 'คำร้องอุทธรณ์'],
     'sources': ['app/Http/Controllers/Student/AppealController.php',
                 'app/Http/Controllers/Discipline/AppealController.php'],
     'inputs': [flow('E4', 'คำร้องอุทธรณ์และหลักฐาน', 'หรือข้อมูลยกเลิกคำร้อง'),
                flow('E2', 'ผลพิจารณา / จำนวนคะแนนคืน'), flow('D6', 'คำร้องและสถานะเดิม'),
                flow('D3', 'รายการพฤติกรรมและเกณฑ์คะแนน'), flow('D2', 'ข้อมูลนักเรียนและคะแนนเดิม')],
     'outputs': [flow('E4', 'สถานะและผลพิจารณาอุทธรณ์'), flow('E2', 'รายการคำร้องและผลพิจารณา'),
                 flow('D6', 'คำร้องและผลพิจารณาที่ปรับปรุง'), flow('D3', 'สถานะรายการพฤติกรรมที่ปรับปรุง'),
                 flow('D2', 'คะแนนคืนและความเสี่ยงที่ปรับปรุง')],
     'note': 'ฝ่ายปกครองคืนคะแนนหรือยกเลิกคำร้อง การคืนคะแนนปรับ D2 และสถานะใน D3 โดยตรง'},
    {'id': '6.0', 'title': ['รับและตรวจสอบ', 'ข้อมูลแจ้งเบาะแส'],
     'sources': ['app/Http/Controllers/Student/InformantReportController.php',
                 'app/Http/Controllers/Discipline/InformantReportController.php'],
     'inputs': [flow('E4', 'ข้อมูลแจ้งเบาะแสและหลักฐาน'), flow('E2', 'ผลตรวจสอบ / ผลปิดเรื่อง', 'ตัวเลือกบันทึกพฤติกรรมและแจ้งผล'),
                flow('D7', 'เรื่องแจ้งและสถานะเดิม'), flow('D2', 'ข้อมูลนักเรียนที่เกี่ยวข้อง'),
                flow('D3', 'เกณฑ์คะแนนกรณีแจ้งเท็จ'), flow('D9', 'ข้อมูลภาคเรียน')],
     'outputs': [flow('E4', 'สถานะเรื่องแจ้งของตน'), flow('E2', 'รายการเรื่องแจ้งและผลตรวจสอบ'),
                 flow('D7', 'เรื่องแจ้ง / ผลตรวจสอบ / สถานะ'), flow('D3', 'บันทึกพฤติกรรมแจ้งเท็จ', 'สถานะรออนุมัติ เมื่อเลือกดำเนินการ'),
                 flow('D8', 'ข้อความแจ้งผู้เกี่ยวข้อง', 'เมื่อเลือกแจ้งผล')],
     'note': 'การบันทึกพฤติกรรมแจ้งเท็จและข้อความเป็นทางเลือกหลังตรวจสอบ ไม่เกิดขึ้นกับทุกเรื่อง'},
    {'id': '7.0', 'title': ['รับส่งข้อความ', 'และติดตามการอ่าน'],
     'sources': ['app/Http/Controllers/Admin/MessageController.php',
                 'app/Http/Controllers/Discipline/MessageController.php',
                 'app/Http/Controllers/Teacher/MessageController.php',
                 'app/Http/Controllers/Student/MessageController.php',
                 'app/Http/Controllers/ParentGuardian/MessageController.php'],
     'inputs': [flow('E1', 'ข้อความ / ผู้รับ / ไฟล์แนบ'), flow('E2', 'ข้อความ / ผู้รับ / ไฟล์แนบ'),
                flow('E3', 'ข้อความ / ผู้รับ / ไฟล์แนบ'), flow('E4', 'ข้อความ / ผู้รับ / ไฟล์แนบ'),
                flow('E5', 'ข้อความ / ผู้รับ / ไฟล์แนบ'), flow('D1', 'ข้อมูลบัญชีผู้ส่งและผู้รับ'),
                flow('D9', 'ความสัมพันธ์นักเรียน–ผู้ปกครอง'), flow('D2', 'ข้อมูลนักเรียนที่สัมพันธ์กับบัญชี'),
                flow('D8', 'ข้อความและสถานะการอ่านเดิม')],
     'outputs': [flow('E1', 'กล่องข้อความและผลการส่ง'), flow('E2', 'กล่องข้อความและผลการส่ง'),
                 flow('E3', 'กล่องข้อความและผลการส่ง'), flow('E4', 'กล่องข้อความและผลการส่ง'),
                 flow('E5', 'ข้อความ / สำเนาถึงผู้ปกครอง', 'และผลการส่ง'),
                 flow('D8', 'ข้อความใหม่และสถานะการอ่าน')],
     'note': 'ผู้รับขึ้นกับสิทธิ์ของแต่ละบทบาท ฝ่ายปกครองส่งข้อความถึงนักเรียนพร้อมสำเนาถึงผู้ปกครองได้'},
    {'id': '8.0', 'title': ['แสดงข้อมูลติดตาม', 'รายงานและแดชบอร์ด'],
     'sources': ['app/Http/Controllers/Admin/DashboardController.php',
                 'app/Http/Controllers/Discipline/BehaviorReportController.php',
                 'app/Http/Controllers/Teacher/DashboardController.php',
                 'app/Http/Controllers/Student/DashboardController.php',
                 'app/Http/Controllers/ParentGuardian/DashboardController.php',
                 'app/Http/Controllers/Prayer/PrayerController.php'],
     'inputs': [flow('D1', 'บัญชีและสถิติผู้ใช้งาน'), flow('D2', 'ข้อมูลนักเรียน คะแนน และความเสี่ยง'), flow('D3', 'พฤติกรรมและเกณฑ์คะแนน'),
                flow('D4', 'ประวัติการเข้าแถว'), flow('D5', 'ประวัติละหมาดและการแก้ไข'),
                flow('D6', 'ผลอุทธรณ์และคะแนนคืน'), flow('D7', 'จำนวนเรื่องแจ้งและสถานะ'),
                flow('D8', 'จำนวนข้อความที่ยังไม่อ่าน'),
                flow('D9', 'ภาคเรียน / ห้องที่ปรึกษา', 'และความสัมพันธ์ผู้ปกครอง')],
     'outputs': [flow('E1', 'ภาพรวมระบบและสถิติ'), flow('E2', 'รายงานพฤติกรรม / นักเรียนเสี่ยง', 'รายงานเข้าแถวและละหมาด'),
                 flow('E3', 'ข้อมูลนักเรียนและสถิติห้องที่ปรึกษา'),
                 flow('E4', 'คะแนน / พฤติกรรม / เข้าแถว', 'ละหมาดและผลอุทธรณ์ของตน'),
                 flow('E5', 'คะแนน / พฤติกรรม / เข้าแถว', 'และละหมาดของบุตร')],
     'requests': ['E1', 'E2', 'E3', 'E4', 'E5'],
     'note': 'คำขอรายงานระบุภาคเรียน ตัวกรอง และนักเรียนที่มีสิทธิ์ดู การส่งออกขึ้นกับสิทธิ์ของบทบาท'},
    {'id': '9.0', 'title': ['ประเมินคะแนนวินัย', 'และเลื่อนชั้นอัตโนมัติ'],
     'sources': ['app/Models/Semester.php', 'app/Services/StudentPromotionService.php',
                 'app/Console/Commands/AutoPromoteStudents.php'],
     'inputs': [flow('C1', 'ข้อมูลภาคเรียนใหม่'), flow('D9', 'ปีการศึกษาและภาคเรียนประเมิน'),
                flow('D1', 'สถานะบัญชีนักเรียน'), flow('D2', 'ข้อมูลนักเรียน ชั้น และห้องเดิม'),
                flow('D3', 'พฤติกรรมและเกณฑ์คะแนน'), flow('D6', 'คะแนนคืนจากผลอุทธรณ์'),
                flow('D10', 'ปีการศึกษาที่ประมวลผลแล้ว')],
     'outputs': [flow('D2', 'ระดับชั้นและห้องที่ปรับปรุง'), flow('D1', 'สถานะกำลังศึกษา / สำเร็จการศึกษา'),
                 flow('D10', 'ปีที่ประมวลผลและผลสรุปเลื่อนชั้น'), flow('C2', 'สรุปผลประมวลผลเลื่อนชั้น')],
     'note': 'คะแนนอย่างน้อย 50 จึงผ่านเกณฑ์วินัย: ม.1–ม.5 เลื่อนชั้น ม.6 สำเร็จการศึกษา; ต่ำกว่าเกณฑ์คงชั้นเดิม'},
]

def text(x, y, lines, cls='node-label', anchor='middle'):
    return ''.join(f'<text class="{cls}" x="{x}" y="{y + i * 20}" text-anchor="{anchor}">{escape(s)}</text>'
                   for i, s in enumerate(lines))

def node(node_id, x, cy, uid):
    y, w, h = cy - 24, 240, 48
    kind = 'entity' if node_id.startswith('E') else 'store' if node_id.startswith('D') else 'connector'
    lines = ENTITIES[node_id] if kind == 'entity' else STORES[node_id][0] if kind == 'store' else CONNECTORS[node_id]
    if kind == 'store':
        shape = f'<path class="store-shape" d="M {x+w} {y} H {x} V {y+h} H {x+w}"/><path class="store-shape" d="M {x+48} {y} V {y+h}"/>'
        label = text(x + 24, cy + 4, [node_id], 'code') + text(x + 144, cy + 4 - (12 if len(lines)>1 else 0), lines)
    elif kind == 'connector':
        shape = f'<rect class="connector-shape" x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>'
        label = text(x + 24, cy + 4, [node_id], 'code') + text(x + 144, cy + 4 - (12 if len(lines)>1 else 0), lines)
    else:
        shape = f'<rect class="entity-shape" x="{x}" y="{y}" width="{w}" height="{h}"/>'
        label = text(x + 24, cy + 4, [node_id], 'code') + text(x + 140, cy + 4, lines)
    return f'<g id="{uid}" data-node="{node_id}" data-bounds="{x},{y},{w},{h}">{shape}{label}</g>'

def rounded_path(points):
    out = f'M {points[0][0]} {points[0][1]}'
    for a, b, c in zip(points, points[1:], points[2:]):
        dx1, dy1 = b[0]-a[0], b[1]-a[1]
        dx2, dy2 = c[0]-b[0], c[1]-b[1]
        sign = lambda v: (v > 0) - (v < 0)
        r = 8
        before = (b[0]-sign(dx1)*r, b[1]-sign(dy1)*r)
        after = (b[0]+sign(dx2)*r, b[1]+sign(dy2)*r)
        out += f' L {before[0]} {before[1]} Q {b[0]} {b[1]} {after[0]} {after[1]}'
    return out + f' L {points[-1][0]} {points[-1][1]}'

GEOMETRY = []

def diagram(p):
    slug = 'dfd0-p' + p['id'].split('.')[0]
    requests = p.get('requests', [])
    # Report requests appear as five separate data flows; no actor grouping.
    inputs = p['inputs'] + [flow(e, 'ภาคเรียน / ตัวกรอง / ขอบเขตข้อมูล') for e in requests]
    outputs = p['outputs']
    n = max(len(inputs), len(outputs))
    height = max(640, n * 64 + 272)
    center = (height - 96) // 2
    # Central process ports stay 32 px apart; outer endpoints 64 px apart.
    process_h = max(176, (n - 1)*32 + 80)
    process_y = center - process_h // 2
    process_rect = (808, process_y, 224, process_h)
    edges, leaves, labels, geom_edges, geom_nodes, geom_labels = [], [], [], [], [], []
    for side, entries in [('in', inputs), ('out', outputs)]:
        count = len(entries)
        start_y = center - (count - 1)*32
        port_start = center - (count - 1)*16
        for i, f in enumerate(entries):
            cy, py = start_y + i*64, port_start + i*32
            nx = 48 if side == 'in' else 1552
            uid = f'{slug}-{side}-{i}'
            leaves.append(node(f['node'], nx, cy, uid))
            geom_nodes.append({'id': uid, 'bounds': [nx, cy-24, 240, 48]})
            if side == 'in':
                corridor = 784 - min(i, count-1-i)*16
                points = [(288, cy), (corridor, cy), (corridor, py), (808, py)]
                if cy == py:
                    points = [(288, cy), (808, py)]
                label_x = 496
            else:
                corridor = 1056 + min(i, count-1-i)*16
                points = [(1032, py), (corridor, py), (corridor, cy), (1552, cy)]
                if cy == py:
                    points = [(1032, py), (1552, cy)]
                label_x = 1336
            edge_id = f'{slug}-flow-{side}-{i}'
            label_h = 20*len(f['label'])
            label_y = cy - 8 - label_h
            # Labels sit on the outer horizontal segment with eight px clearance.
            edges.append(f'<path id="{edge_id}" class="flow" d="{rounded_path(points)}" marker-end="url(#{slug}-arrow)"/>')
            labels.append(f'<g class="flow-label" data-edge="{edge_id}" data-label-bounds="{label_x-184},{label_y},368,{label_h}"><rect x="{label_x-184}" y="{label_y}" width="368" height="{label_h}" fill="#fff"/>{text(label_x, label_y+16, f["label"], "payload")}</g>')
            geom_edges.append({'id': edge_id, 'points': points})
            geom_labels.append({'id': edge_id, 'bounds': [label_x-184, label_y, 368, label_h]})
    geom_nodes.append({'id': slug, 'bounds': list(process_rect)})
    GEOMETRY.append({'process': p['id'], 'edges': geom_edges, 'nodes': geom_nodes, 'labels': geom_labels})
    # All connectors and label masks precede nodes, including the process.
    title = ' '.join(p['title'])
    markers = ''.join(f'<marker id="{slug}-{name}" markerWidth="8" markerHeight="8" refX="8" refY="4" orient="auto" markerUnits="userSpaceOnUse"><path d="M0 0 L8 4 L0 8 Z" fill="#111"/></marker>' for name in ['arrow', 'arrow-accent', 'arrow-link'])
    process = f'<g id="{slug}" data-node="{p["id"]}" data-bounds="808,{process_y},224,{process_h}"><rect class="process-shape" x="808" y="{process_y}" width="224" height="{process_h}" rx="8"/><line x1="808" y1="{process_y+40}" x2="1032" y2="{process_y+40}" stroke="#111"/>{text(920, process_y+28, [p["id"]], "process-code")}{text(920, center-8, p["title"], "process-label")}</g>'
    legend_y = height - 64
    legend = f'<g class="legend"><line x1="48" y1="{legend_y-16}" x2="1792" y2="{legend_y-16}" stroke="#ccc"/>'
    legend += f'<rect x="48" y="{legend_y}" width="40" height="24" fill="#fff" stroke="#111"/>{text(100,legend_y+20,["หน่วยงานภายนอก"],"legend-text","start")}'
    legend += f'<rect x="352" y="{legend_y}" width="40" height="24" rx="4" fill="#fff" stroke="#111"/>{text(404,legend_y+20,["กระบวนการ"],"legend-text","start")}'
    legend += f'<path d="M704 {legend_y} H664 V{legend_y+24} H704" fill="none" stroke="#111"/>{text(716,legend_y+20,["คลังข้อมูล"],"legend-text","start")}'
    legend += f'<path d="M952 {legend_y+16} H1000" class="flow" marker-end="url(#{slug}-arrow)"/>{text(1012,legend_y+20,["กระแสข้อมูล"],"legend-text","start")}'
    legend += text(1792,legend_y+20,['รหัสซ้ำ = หน่วยงาน / คลังเดียวกัน'],'legend-text','end') + '</g>'
    return f'''<figure class="fragment" id="process-{p['id'].split('.')[0]}">
      <svg viewBox="0 0 1840 {height}" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="{slug}-title {slug}-desc">
        <title id="{slug}-title">DFD Level 0 — {p['id']} {escape(title)}</title>
        <desc id="{slug}-desc">ส่วนของ DFD Level 0 แสดงข้อมูลเข้าและข้อมูลออกของกระบวนการ {p['id']} {escape(title)} โดยหน่วยงานและคลังข้อมูลรหัสซ้ำหมายถึงรายการเดียวกันทั้งแผนภาพ</desc>
        <defs>{markers}</defs><rect width="1840" height="{height}" fill="#fff"/>
        {text(48,32,['กระบวนการ '+p['id']+'  ·  '+title],'fragment-title','start')}
        {text(48,64,['แหล่งข้อมูล / ข้อมูลเข้า'],'column-title','start')}{text(1792,64,['ข้อมูลออก / ปลายทาง'],'column-title','end')}
        {''.join(edges)}{''.join(labels)}{''.join(leaves)}{process}{legend}
      </svg><figcaption>{escape(p['note'])}</figcaption></figure>'''

def overlap(a, b):
    return max(a[0], b[0]) < min(a[0]+a[2], b[0]+b[2]) and max(a[1], b[1]) < min(a[1]+a[3], b[1]+b[3])

def segments(points):
    return list(zip(points, points[1:]))

def segments_intersect(s1, s2):
    a,b = s1; c,d = s2
    horiz1 = a[1] == b[1]; horiz2 = c[1] == d[1]
    if horiz1 == horiz2:
        axis = 0 if horiz1 else 1
        fixed = 1-axis
        return a[fixed] == c[fixed] and max(min(a[axis],b[axis]),min(c[axis],d[axis])) <= min(max(a[axis],b[axis]),max(c[axis],d[axis]))
    if not horiz1:
        a,b,c,d = c,d,a,b
    return min(a[0],b[0]) <= c[0] <= max(a[0],b[0]) and min(c[1],d[1]) <= a[1] <= max(c[1],d[1])

def verify_geometry():
    issues = []
    for g in GEOMETRY:
        for e1,e2 in combinations(g['edges'],2):
            if any(segments_intersect(s1,s2) for s1 in segments(e1['points']) for s2 in segments(e2['points'])):
                issues.append(f"Connectors intersect: {e1['id']} / {e2['id']}")
        for label in g['labels']:
            for n in g['nodes']:
                if overlap(label['bounds'],n['bounds']):
                    issues.append(f"Label touches node: {label['id']} / {n['id']}")
        for l1,l2 in combinations(g['labels'],2):
            if overlap(l1['bounds'],l2['bounds']):
                issues.append(f"Labels overlap: {l1['id']} / {l2['id']}")
    if issues:
        raise ValueError('\n'.join(issues))
    count = sum(len(g['edges']) for g in GEOMETRY)
    print(f'Geometry verified: {len(GEOMETRY)} processes, {count} directed flows; zero connector intersections or overlapping labels/nodes.')

def build():
    sections = []
    titles = ['ข้อมูลหลักและงานวินัย', 'การเข้าแถวและการละหมาด', 'การอุทธรณ์และการแจ้งเบาะแส',
              'การสื่อสารและการรายงาน', 'การเลื่อนชั้นและทะเบียนคลังข้อมูล']
    for index in range(5):
        selected = PROCESSES[index*2:index*2+2]
        figures = ''.join(diagram(p) for p in selected)
        dictionary = ''
        if index == 4:
            rows = ''.join(f'<tr><th scope="row">{key}</th><td>{escape(" / ".join(label))}</td><td>{"<br>".join(escape(s) for s in tables)}</td></tr>' for key,(label,tables) in STORES.items())
            dictionary = '<div class="dictionary"><h3>ทะเบียนคลังข้อมูลเชิงตรรกะ</h3><table><thead><tr><th>รหัส</th><th>คลังข้อมูล</th><th>ตารางในระบบ</th></tr></thead><tbody>'+rows+'</tbody></table><p>คลังข้อมูลใน Level 0 เป็นกลุ่มข้อมูลเชิงตรรกะ จึงอาจครอบคลุมหลายตารางจริง</p></div>'
        sections.append(f'<section class="sheet" aria-labelledby="sheet-{index+1}-title"><header><p class="eyebrow">DATA FLOW DIAGRAM · LEVEL 0 · {index+1}/5</p><h2 id="sheet-{index+1}-title">{titles[index]}</h2><p>ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน</p></header>{figures}{dictionary}<footer>DFD Level 0 (Diagram 0) · แผ่น {index+1} จาก 5 · ทุกกระบวนการอยู่ในระดับเดียวกัน</footer></section>')
    html = '''<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DFD Level 0 — ระบบบริหารงานวินัยนักเรียน</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#ededed;color:#111;font-family:Tahoma,"Leelawadee UI","Noto Sans Thai",sans-serif;line-height:1.55}
.intro,.sheet{max-width:1560px;margin:24px auto;padding:32px;background:#fff}.intro{border-bottom:2px solid #111}.intro h1{font-size:28px;margin:0 0 12px;font-weight:600}.intro p{margin:8px 0;max-width:1120px;font-size:16px}
.intro nav{display:flex;flex-wrap:wrap;gap:12px;margin-top:16px}.intro a{color:#111;text-decoration:none;border-bottom:1px solid #777;font-size:16px}.sheet header{margin-bottom:16px;border-bottom:1px solid #bbb;padding-bottom:12px}.eyebrow{font-family:Consolas,monospace;letter-spacing:.08em;font-size:12px;margin:0;color:#444}.sheet h2{font-size:24px;font-weight:600;margin:4px 0}.sheet header>p:last-child{font-size:16px;margin:0;color:#444}.fragment{margin:0 0 16px;break-inside:avoid}.fragment svg{display:block;width:100%;height:auto}.fragment figcaption{font-size:14px;margin:0 48px 12px;color:#444}.sheet footer{border-top:1px solid #bbb;font-size:12px;padding-top:12px;color:#555}
svg text{font-family:Tahoma,"Leelawadee UI","Noto Sans Thai",sans-serif;fill:#111}.node-label,.payload,.legend-text{font-size:16px}.payload{font-weight:400}.code{font-family:Consolas,monospace;font-size:16px;font-weight:600}.process-code{font-family:Consolas,monospace;font-size:24px;font-weight:600}.process-label{font-size:20px;font-weight:600}.fragment-title{font-size:20px;font-weight:600}.column-title{font-size:16px;fill:#555}.flow{fill:none;stroke:#111;stroke-width:1.5}.entity-shape,.process-shape{fill:#fff;stroke:#111;stroke-width:1.5}.store-shape{fill:none;stroke:#111;stroke-width:1.5}.connector-shape{fill:#fff;stroke:#111;stroke-width:1.5;stroke-dasharray:4 4}.dictionary{margin:16px 48px 24px}.dictionary h3{font-size:20px;margin:0 0 12px}.dictionary table{width:100%;border-collapse:collapse;font-size:14px}.dictionary th,.dictionary td{text-align:left;vertical-align:top;padding:8px 12px;border-bottom:1px solid #ddd}.dictionary thead{background:#f5f5f5}.dictionary td:last-child{font-family:Consolas,monospace;font-size:12px}.dictionary p{font-size:14px;color:#555}
@media(max-width:900px){.intro,.sheet{margin:12px;padding:16px}.fragment{overflow-x:auto}.fragment svg{min-width:1200px}.fragment figcaption{margin:8px 0}.dictionary{margin:12px 0;overflow-x:auto}.intro h1{font-size:24px}}
@page{size:A3 portrait;margin:10mm}@media print{body{background:#fff}.intro{display:none}.sheet{max-width:none;margin:0;padding:0;break-after:page}.sheet:last-child{break-after:auto}.fragment{margin-bottom:8px}.fragment svg{min-width:0;width:100%}.sheet header{margin-bottom:4px;padding-bottom:4px}.sheet h2{font-size:18px}.sheet header>p:last-child{font-size:12px}.eyebrow{font-size:10px}.fragment figcaption{font-size:10px;margin:0 24px 4px}.sheet footer{font-size:10px;padding-top:4px}.dictionary{margin:4px 24px}.dictionary h3{font-size:14px;margin-bottom:4px}.dictionary table{font-size:10px}.dictionary th,.dictionary td{padding:2px 8px}.dictionary td:last-child{font-size:10px}.dictionary p{font-size:10px;margin:4px 0}}
</style></head><body><div class="intro"><h1>DFD Level 0 — ระบบบริหารงานวินัยนักเรียน</h1>
<p>9 กระบวนการ · 5 หน่วยงานภายนอก · 10 คลังข้อมูลเชิงตรรกะ จัดเป็น 5 แผ่นต่อเนื่องเพื่อให้เส้นทุกเส้นแยกจากกัน</p>
<p>หน่วยงานและคลังข้อมูลที่แสดงซ้ำด้วยรหัสเดียวกันหมายถึงรายการเดียวกันทั้งระบบ ส่วน C1 และ C2 เป็นจุดเชื่อมข้ามแผ่นระหว่าง 1.0 กับ 9.0 ลูกศรแต่ละเส้นระบุข้อมูลและทิศทางชัดเจน</p>
<p>ใช้ Level 0 ในความหมาย Diagram 0: แตกกระบวนการหลักของระบบเป็น 1.0–9.0 ส่วน Context Diagram จะมีเพียงกระบวนการ 0 เดียว ทุกส่วนในไฟล์นี้ยังเป็น Level 0</p>
<p>พิมพ์หรือบันทึก PDF ผ่านเบราว์เซอร์โดยเลือก A3 แนวตั้ง สไตล์ขาวดำใช้ฟอนต์ Tahoma ในเครื่องเพื่ออ่านภาษาไทยได้โดยไม่ต้องโหลดฟอนต์ภายนอก</p><nav aria-label="กระบวนการ">'''
    html += ''.join(f'<a href="#process-{p["id"].split(".")[0]}">{p["id"]} {escape(" ".join(p["title"]))}</a>' for p in PROCESSES)
    html += '</nav></div>' + ''.join(sections) + '</body></html>\n'
    verify_geometry()
    OUTPUT.write_text(html, encoding='utf-8')
    notes = ROOT / 'docs' / 'dfd-level0-design-notes.md'
    lines = ['# DFD Level 0 — บันทึกการออกแบบ', '',
             'แผนภาพ: [dfd-level0-student-discipline.html](dfd-level0-student-discipline.html)', '',
             'ใช้ความหมาย Diagram 0 ตามเอกสาร DFD_Level0.md เดิม: 1.0–8.0 คงเลขเดิม เพิ่ม 9.0 ให้ครอบคลุม StudentPromotionService ที่มีอยู่จริง', '',
             '## วิธีอ่าน', '',
             '- ทั้ง 9 ส่วนเป็นกระบวนการระดับเดียวกัน ไม่ใช่การแตกเป็น Level 1',
             '- E1–E5 และ D1–D10 ที่แสดงซ้ำคือหน่วยงาน/คลังเดียวกัน ใช้การแสดงซ้ำเพื่อตัดเส้นพาดข้ามกระบวนการ',
             '- C1 คือข้อมูลภาคเรียนใหม่ 1.0 → 9.0; C2 คือสรุปผลเลื่อนชั้น 9.0 → 1.0 จุดเชื่อมเหล่านี้ไม่ใช่หน่วยงานภายนอก',
             '- กระบวนการสื่อสารกันผ่านคลังข้อมูลร่วม ข้อมูลแจ้งเท็จจาก 6.0 บันทึก D3 เพื่อรออนุมัติใน 2.0',
             '- คำร้อง 5.0 และการเข้าแถว 3.0 ปรับคะแนนใน D2 โดยตรงตามโค้ด ไม่เพิ่มเส้นผ่าน 2.0 ที่ไม่มีอยู่ใน implementation',
             '- ข้อความจาก 6.0 เขียน D8 และถูกอ่าน/ส่งต่อถึงผู้ใช้ผ่าน 7.0',
             '- แสดงเฉพาะกลุ่มข้อมูลและการไหลหลัก รายละเอียดตรวจสอบ input/session/cache/file storage อยู่ภายในแต่ละกระบวนการ',
             '- สไตล์ขาวดำตามผู้ใช้; Tahoma และ Consolas แทนฟอนต์เริ่มต้นของสกิลเพื่อใช้ภาษาไทยและพิมพ์แบบออฟไลน์', '',
             '## หลักฐานจากระบบ', '']
    for p in PROCESSES:
        lines += [f'### {p["id"]} {" ".join(p["title"])}', '', p['note'], '']
        lines += [f'- `{s}`' for s in p['sources']]
        lines += ['']
    lines += ['## คลังข้อมูล', '', '| รหัส | ชื่อ | ตารางจริง |', '|---|---|---|']
    lines += [f'| {key} | {" / ".join(label)} | {", ".join(tables)} |' for key,(label,tables) in STORES.items()]
    lines += ['', '## ตรวจสอบ', '',
              'ตัวสร้างตรวจเส้นตรงทุกช่วงของลูกศรทุกคู่ก่อนสร้างไฟล์: ต้องไม่มีเส้นตัดหรือเส้นซ้อน และกรอบป้ายต้องไม่ซ้อนกับโหนดหรือป้ายอื่น',
              'ตรวจแบบ conservative บนทางเดินมุมฉากก่อนปัดมุมรัศมี 8px และจัดจุดต่อบนกระบวนการห่างกัน 32px', '',
              'สร้างใหม่: `python docs/diagrams/build_dfd_level0.py`', '']
    notes.write_text('\n'.join(lines), encoding='utf-8')
    (Path(__file__).parent / 'dfd-level0-flows.json').write_text(json.dumps(PROCESSES,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    print(f'Created {OUTPUT}')

if __name__ == '__main__':
    build()
