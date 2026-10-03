"""Create eight balanced Level 1 decompositions of the supplied Level 0.

Run: python docs/diagrams/build_dfd_level1.py
Outputs: HTML diagram, Mermaid Markdown, model JSON and design notes.
"""
from html import escape
from itertools import combinations
from pathlib import Path
import json
import re
import build_dfd_level0 as draw

ROOT=Path(__file__).resolve().parents[2]
HERE=Path(__file__).parent
PARENT_NODES=draw.NODES.copy()
PARENT_EDGES={e['id']:e for e in draw.EDGES}
PARENT_PROCESSES=draw.PROCESS_IDS.copy()
BOUNDARY_CROSS=draw.CROSS.copy()
DIAGRAMS=[]

def sub(pid,thai,english):
    return {'id':pid,'shape':'circle','label':'\n'.join([pid[1]+'.'+pid[2],*thai,'('+english+')'])}

def diagram(parent,subs):
    model={'parent':f'P{parent}','nodes':{s['id']:s for s in subs},'edges':[]}
    DIAGRAMS.append(model)
    return model

def add(model,source,target,label,parent_edge=None,direction=None):
    eid=f'l1-{model["parent"]}-{len(model["edges"])+1}'
    model['edges'].append({'id':eid,'source':source,'target':target,'label':label,
                          'bidirectional':False,'parent_edge':parent_edge,'direction':direction})

def boundary(model,eid,child,direction,label=None):
    e=PARENT_EDGES[eid]
    parent=model['parent']
    assert parent in [e['source'],e['target']],(parent,eid)
    peer=e['target'] if e['source']==parent else e['source']
    if peer in PARENT_PROCESSES:
        c=BOUNDARY_CROSS[eid]
        peer=c['id']
        model['nodes'][peer]={'id':peer,'shape':'continuation','label':peer+'\n'+c['source'][1:]+'.0 → '+c['target'][1:]+'.0\n(ระหว่างผังหลัก)'}
    else:
        model['nodes'][peer]=PARENT_NODES[peer].copy()
    if direction=='in':
        add(model,peer,child,label or e['label'],eid,direction)
    else:
        add(model,child,peer,label or e['label'],eid,direction)

def store(model,key,label):
    model['nodes'][key]={'id':key,'shape':'cylinder','label':label}

# 1.0: keep the administrator input and D1 read/write interface.
m=diagram(1,[sub('P11',['จัดการบัญชีผู้ใช้'],'User Accounts'),
             sub('P12',['จัดการสิทธิ์เข้าถึง'],'Permissions'),
             sub('P13',['จัดการภาคเรียน'],'Semesters')])
boundary(m,'e1','P11','in','ข้อมูลเพิ่ม/แก้ไขบัญชีผู้ใช้')
boundary(m,'e2','P11','in','บัญชีผู้ใช้เดิม')
boundary(m,'e2','P11','out','บัญชีผู้ใช้ที่ปรับปรุง')
boundary(m,'e1','P12','in','ข้อมูลกำหนดสิทธิ์เข้าถึง')
boundary(m,'e2','P12','in','สิทธิ์เข้าถึงเดิม')
boundary(m,'e2','P12','out','สิทธิ์เข้าถึงที่ปรับปรุง')
boundary(m,'e1','P13','in','ข้อมูลเปิด/แก้ไขภาคเรียน')
store(m,'D9','D9 ข้อมูลภาคเรียน\n(คลังภายใน 1.0)')
add(m,'D9','P13','ข้อมูลภาคเรียนเดิม')
add(m,'P13','D9','ข้อมูลภาคเรียนที่ปรับปรุง')

# 2.0: retain the three cross-process inputs from 3.0, 5.0 and 6.0.
m=diagram(2,[sub('P21',['รับและบันทึก','รายงานพฤติกรรม'],'Log Behavior'),
             sub('P22',['ตรวจสอบและอนุมัติ','รายการพฤติกรรม'],'Review Behavior'),
             sub('P23',['คำนวณคะแนนและ','สถานะความเสี่ยง'],'Calculate Scores'),
             sub('P24',['จัดการเกณฑ์','ตัด/เพิ่มคะแนน'],'Manage Rules')])
boundary(m,'e4','P21','in')
boundary(m,'e3','P21','in','ข้อมูลบันทึกพฤติกรรม')
boundary(m,'e10','P21','in')
boundary(m,'e23','P21','in')
boundary(m,'e6','P21','out','บันทึกพฤติกรรมรออนุมัติ')
add(m,'P21','P22','ข้อมูลรายการพฤติกรรมที่รับแล้ว')
boundary(m,'e6','P22','in','รายการพฤติกรรมรออนุมัติ')
boundary(m,'e3','P22','in','ผลอนุมัติ/ไม่อนุมัติพฤติกรรม')
boundary(m,'e6','P22','out','สถานะพฤติกรรมที่พิจารณาแล้ว')
add(m,'P22','P23','รายการอนุมัติสำหรับปรับคะแนน')
boundary(m,'e18','P23','in')
boundary(m,'e5','P23','in','คะแนนและสถานะเสี่ยงเดิม')
boundary(m,'e6','P23','in','เกณฑ์และค่าตัด/เพิ่มคะแนน')
boundary(m,'e5','P23','out','คะแนนและสถานะเสี่ยงที่ปรับปรุง')
boundary(m,'e7','P23','out')
boundary(m,'e3','P24','in','ข้อมูลกำหนดเกณฑ์ตัด/เพิ่มคะแนน')
boundary(m,'e6','P24','in','เกณฑ์ตัด/เพิ่มคะแนนเดิม')
boundary(m,'e6','P24','out','เกณฑ์ตัด/เพิ่มคะแนนที่ปรับปรุง')

# 3.0: the accumulated attendance state is internal to this decomposition.
m=diagram(3,[sub('P31',['รับและตรวจสอบ','ข้อมูลเข้าแถว'],'Check Attendance'),
             sub('P32',['บันทึกเข้าแถวและ','ปรับยอดสะสม'],'Save Attendance'),
             sub('P33',['ตรวจเกณฑ์ขาด/สาย','และส่งข้อมูลตัดคะแนน'],'Check Threshold')])
boundary(m,'e8','P31','in')
add(m,'P31','P32','ข้อมูลเข้าแถวที่ตรวจสอบแล้ว')
boundary(m,'e9','P32','out')
store(m,'D10','D10 ยอดขาด/สายสะสม\n(ข้อมูลสรุปภายใน 3.0)')
add(m,'D10','P32','ยอดขาด/สายสะสมเดิม')
add(m,'P32','D10','ยอดขาด/สายสะสมที่ปรับปรุง')
add(m,'P32','P33','รหัสนักเรียนและรายการล่าสุด')
add(m,'D10','P33','ยอดขาด/สายสะสมสำหรับประเมิน')
boundary(m,'e10','P33','out')

# 4.0: validate the submitted QR/barcode, match the scan, then save.
m=diagram(4,[sub('P41',['รับและตรวจสอบ','ข้อมูล QR ละหมาด'],'Check Prayer QR'),
             sub('P42',['ตรวจข้อมูลสแกน','และสถานะละหมาด'],'Validate Scan'),
             sub('P43',['บันทึกการละหมาด'],'Save Prayer')])
boundary(m,'e11','P41','in')
add(m,'P41','P42','รหัสนักเรียนจาก QR ละหมาด')
boundary(m,'e12','P42','in')
add(m,'P42','P43','ข้อมูลละหมาดที่ตรวจสอบแล้ว')
boundary(m,'e13','P43','out')

# 5.0: score restoration leaves this parent through its original 2.0 link.
m=diagram(5,[sub('P51',['รับและตรวจสอบ','คำร้องอุทธรณ์'],'Submit Appeal'),
             sub('P52',['พิจารณาคำร้อง','อุทธรณ์'],'Review Appeal'),
             sub('P53',['บันทึกผลอุทธรณ์','และส่งข้อมูลคืนคะแนน'],'Resolve Appeal')])
boundary(m,'e14','P51','in')
boundary(m,'e15','P51','out','คำร้องอุทธรณ์รอพิจารณา')
add(m,'P51','P52','ข้อมูลคำร้องที่รับแล้ว')
boundary(m,'e15','P52','in','คำร้องอุทธรณ์และสถานะเดิม')
boundary(m,'e16','P52','in')
add(m,'P52','P53','ผลพิจารณาและจำนวนคะแนนคืน')
boundary(m,'e15','P53','out','คำร้องและสถานะหลังพิจารณา')
boundary(m,'e17','P53','out')
boundary(m,'e18','P53','out')

# 6.0: keep the daily submission limit and conditional outgoing actions.
m=diagram(6,[sub('P61',['รับและตรวจสอบ','ข้อมูลแจ้งเบาะแส'],'Receive Report'),
             sub('P62',['สอบสวนข้อมูลแจ้ง','และสรุปผล'],'Investigate'),
             sub('P63',['ปิดเรื่องและ','ส่งข้อมูลดำเนินการ'],'Close Report')])
boundary(m,'e19','P61','in')
boundary(m,'e20','P61','in','ประวัติเรื่องแจ้งและจำนวนรายวัน')
boundary(m,'e20','P61','out','เรื่องแจ้งที่รับใหม่')
add(m,'P61','P62','ข้อมูลเรื่องแจ้งที่รับแล้ว')
boundary(m,'e20','P62','in','เรื่องแจ้งและสถานะสอบสวน')
boundary(m,'e21','P62','in')
add(m,'P62','P63','เรื่องแจ้งพร้อมผลการสอบสวน')
boundary(m,'e20','P63','out','เรื่องแจ้ง ผลสอบสวน และสถานะปิด')
boundary(m,'e22','P63','out')
boundary(m,'e23','P63','out')
boundary(m,'e24','P63','out')

# 7.0: message validation, persistence, and recipient delivery.
m=diagram(7,[sub('P71',['รับและตรวจสอบ','ข้อมูลข้อความ'],'Validate Message'),
             sub('P72',['บันทึกข้อความ','และผู้รับ'],'Save Message'),
             sub('P73',['แสดงกล่องข้อความ','และแจ้งเตือน'],'Deliver Message')])
for eid in ['e24','e25','e26','e27']:
    boundary(m,eid,'P71','in')
add(m,'P71','P72','ข้อความและผู้รับที่ตรวจสอบแล้ว')
boundary(m,'e28','P72','out','ข้อความและผู้รับที่บันทึก')
add(m,'P72','P73','รหัสอ้างอิงข้อความที่บันทึกแล้ว')
boundary(m,'e28','P73','in','ข้อความสำหรับแสดงและแจ้งเตือน')
boundary(m,'e29','P73','out')
boundary(m,'e30','P73','out')

# 8.0: collect the parent inputs, compute reports, then publish its outputs.
m=diagram(8,[sub('P81',['รวบรวมข้อมูล','ตามขอบเขตผู้ใช้งาน'],'Collect Report Data'),
             sub('P82',['สรุปสถิติและ','วิเคราะห์ความเสี่ยง'],'Summarize Reports'),
             sub('P83',['แสดงรายงานสรุป','และแดชบอร์ด'],'Present Reports')])
for eid in ['e31','e32','e33','e34','e35']:
    boundary(m,eid,'P81','in')
add(m,'P81','P82','คะแนน พฤติกรรม เข้าแถว และละหมาด')
add(m,'P82','P83','ผลสรุป สถิติ และข้อมูลนักเรียนเสี่ยง')
for eid in ['e36','e37','e38']:
    boundary(m,eid,'P83','out')

def number(pid):
    return DIAGRAM_NODE_LOOKUP[pid]['label'].split('\n')[0]

DIAGRAM_NODE_LOOKUP={key:n for m in DIAGRAMS for key,n in m['nodes'].items()}

def check_balancing():
    expected=set()
    for parent in PARENT_PROCESSES:
        for eid,e in PARENT_EDGES.items():
            if e['source']==parent:
                expected.add((parent,eid,'out'))
                if e['bidirectional']:
                    expected.add((parent,eid,'in'))
            if e['target']==parent:
                expected.add((parent,eid,'in'))
                if e['bidirectional']:
                    expected.add((parent,eid,'out'))
    actual={(m['parent'],e['parent_edge'],e['direction']) for m in DIAGRAMS for e in m['edges'] if e['parent_edge']}
    if actual!=expected:
        raise ValueError(f'Balancing error: missing={expected-actual}, added={actual-expected}')
    for m in DIAGRAMS:
        for e in m['edges']:
            assert e['source'] in m['nodes'] and e['target'] in m['nodes'],e
    print(f'Balancing verified: all {len(expected)} directed parent interfaces retained across 8 decompositions.')
    return expected

def check_routes(geometries):
    for parent,groups in geometries:
        routes=[r for g in groups for r in g['edges']]
        nodes=[n for g in groups for n in g['nodes']]
        labels=[l for g in groups for l in g['labels']]
        for a,b in combinations(routes,2):
            assert not any(draw.segments_intersect(s,t) for s in zip(a['points'],a['points'][1:]) for t in zip(b['points'],b['points'][1:])),(parent,a['id'],b['id'])
        for label in labels:
            assert all(not draw.overlap(label['bounds'],n['bounds']) for n in nodes),(parent,label['id'])
            x,y,w,h=label['bounds']
            sides=[((x,y),(x+w,y)),((x,y+h),(x+w,y+h)),((x,y),(x,y+h)),((x+w,y),(x+w,y+h))]
            assert not any(draw.segments_intersect(s,t) for r in routes for s in zip(r['points'],r['points'][1:]) for t in sides),(parent,label['id'],'mask hides route')
        for a,b in combinations(labels,2):
            assert not draw.overlap(a['bounds'],b['bounds']),(parent,a['id'],b['id'])
    print(f'Geometry verified: {sum(len(g["edges"]) for _,groups in geometries for g in groups)} drawn routes; zero intersections or overlapping/hiding labels.')

def mermaid(model):
    title=' '.join(PARENT_NODES[model['parent']]['label'].split('\n')[:-1])
    lines=['---',f'title: DFD Level 1 — Diagram {title}','config:','  layout: elk','  flowchart:','    nodeSpacing: 50','    rankSpacing: 80','---','flowchart TB']
    for key,node in model['nodes'].items():
        label=node['label'].replace('\n','<br/>')
        if node['shape']=='circle':
            shape=f'{key}(("{label}"))'
        elif node['shape']=='cylinder':
            shape=f'{key}[("{label}")]'
        else:
            shape=f'{key}["{label}"]'
        lines.append('    '+shape)
    lines += [f'    {e["source"]} -->|"{e["label"]}"| {e["target"]}' for e in model['edges']]
    lines += ['    classDef entity fill:#ffffff,stroke:#2d3142,stroke-width:1.5px,color:#2d3142',
              '    classDef process fill:#fdeee4,stroke:#eb6c36,stroke-width:1.5px,color:#2d3142',
              '    classDef store fill:#f5f5f5,stroke:#4f5d75,stroke-width:1px,color:#2d3142',
              '    classDef continuation fill:#ffffff,stroke:#4f5d75,stroke-width:1px,color:#4f5d75,stroke-dasharray:4 4']
    for shape,cls in [('circle','process'),('cylinder','store'),('rect','entity'),('continuation','continuation')]:
        keys=[key for key,node in model['nodes'].items() if node['shape']==shape]
        if keys:
            lines.append('    class '+','.join(keys)+' '+cls)
    return '\n'.join(lines)

def build():
    expected=check_balancing()
    # Reuse the verified Level 0 routing grammar and source palette.
    base_html=ROOT/'docs/dfd-level0-student-discipline.html'
    if not base_html.exists():
        draw.build()
    html0=base_html.read_text(encoding='utf-8')
    css=re.search(r'<style>(.*?)</style>',html0,re.S).group(1)
    css+='\n.parent-diagram{margin:40px 0}.parent-diagram h2{font-size:24px;margin:8px 20px}.parent-diagram p{margin:4px 20px 16px;font-size:16px;color:#4f5d75}.route-table{margin:12px 20px 24px;border-collapse:collapse;font-size:14px}.route-table th,.route-table td{padding:6px 16px;border-bottom:1px solid #ddd;text-align:left}.local-note{padding:12px 16px;background:#f5f5f5}.parent-diagram:target{scroll-margin-top:20px}\n'
    sections=[];geometries=[];markdown=['# DFD Level 1 — ระบบบริหารพฤติกรรมนักเรียน','',
        'แตกจาก Mermaid Level 0 ที่ผู้ใช้ให้ ครบทั้ง 8 กระบวนการหลัก รวม 25 กระบวนการย่อย','',
        'ไฟล์ HTML จัดเส้นด้วยพิกัด SVG และตรวจว่าไม่มีเส้นทับ/ตัดกัน ส่วน Mermaid เป็นต้นฉบับสำหรับแก้ไข การจัดเส้นของ Mermaid ขึ้นกับ renderer','',
        'D9 ภาคเรียนและ D10 ยอดขาด/สายสะสมเป็นข้อมูลภายในผัง 1.0 และ 3.0; D10 เป็นข้อมูลสรุปเชิงตรรกะจากการเข้าแถว ไม่ได้เพิ่มตารางฐานข้อมูล','',
        'C1–C4 คงการเชื่อมระหว่างกระบวนการหลักตาม Level 0 ส่วน Lx-y ใน HTML คือจุดต่อเส้นระหว่างกระบวนการย่อยในผังเดียวกัน','']
    for m in DIAGRAMS:
        parent=m['parent'];idx=parent[1:];prefix=f'dfd-level1-parent-{idx}'
        draw.NODES=m['nodes'];draw.EDGES=m['edges'];draw.GEOMETRY=[]
        child_ids=[key for key,n in m['nodes'].items() if n['shape']=='circle']
        internal=[e for e in m['edges'] if e['source'] in child_ids and e['target'] in child_ids]
        draw.CROSS={e['id']:{'id':f'L{idx}-{i+1}','source':e['source'],'target':e['target'],
                          'route':number(e['source'])+' → '+number(e['target']),
                          'hint':f'ภายในผัง {idx}.0'} for i,e in enumerate(internal)}
        offset=128;groups=[]
        for child in child_ids:
            group,height=draw.process_group(child,offset)
            group=group.replace('dfd-level0-mermaid',prefix).replace('id="process-','id="process-l1-')
            groups.append(group);offset+=height
        geometries.append((parent,draw.GEOMETRY))
        title=' '.join(PARENT_NODES[parent]['label'].split('\n')[:-1])
        markers=''.join(f'<marker id="{prefix}-{name}" markerWidth="8" markerHeight="8" refX="8" refY="4" orient="auto-start-reverse" markerUnits="userSpaceOnUse"><path d="M0 0 L8 4 L0 8 Z" fill="#4f5d75"/></marker>' for name in ['arrow','arrow-accent','arrow-link'])
        legend=draw.text(48,offset+32,['หน่วยงาน / คลังข้อมูลรหัสซ้ำ คือรายการเดียวกัน · C = เชื่อมระหว่างผังหลัก · L = ต่อเส้นภายในผังนี้'],'legend-text',anchor='start')
        legend+=draw.text(48,offset+64,['วงกลม = กระบวนการย่อย · สี่เหลี่ยม = หน่วยงานภายนอก · ทรงกระบอก = คลังข้อมูล · กรอบเส้นประ = จุดต่อเส้น'],'legend-text',anchor='start')
        svg=f'<svg viewBox="0 0 1840 {offset+112}" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="{prefix}-title {prefix}-desc"><title id="{prefix}-title">DFD Level 1 — {escape(title)}</title><desc id="{prefix}-desc">ผังย่อยของกระบวนการ {idx}.0 มี {len(child_ids)} กระบวนการย่อย คงข้อมูลเข้าออกของ Level 0 และใช้จุดเชื่อมกับสัญลักษณ์ซ้ำเพื่อให้เส้นไม่ทับกัน</desc><defs>{markers}</defs><rect width="1840" height="{offset+112}" fill="#fff"/>{draw.text(48,48,["DFD Level 1 — Diagram "+title],"diagram-title",anchor="start")}{draw.text(48,88,["แตกกระบวนการ "+idx+".0 · "+str(len(child_ids))+" กระบวนการย่อย"],"diagram-subtitle",anchor="start")}{"".join(groups)}{legend}</svg>'
        rows=''.join(f'<tr><td>{c["id"]}</td><td>{c["route"]}</td><td>{escape(next(e["label"] for e in internal if e["source"]==c["source"] and e["target"]==c["target"]))}</td></tr>' for c in draw.CROSS.values())
        route_table='<table class="route-table"><thead><tr><th>จุดเชื่อม</th><th>กระบวนการย่อย</th><th>ข้อมูล</th></tr></thead><tbody>'+rows+'</tbody></table>' if rows else ''
        note=''
        if parent=='P1':
            note='<p class="local-note">D9 เป็นคลังภาคเรียนภายในกระบวนการ 1.0 ซึ่งแตกจากข้อมูลภาคเรียนในลูกศรของผู้ดูแลระบบ</p>'
        if parent=='P3':
            note='<p class="local-note">D10 เป็นข้อมูลสรุปขาด/สายภายใน 3.0 จากการเข้าแถว ไม่ได้หมายถึงการสร้างตารางฐานข้อมูลใหม่</p>'
        if parent=='P6':
            note='<p class="local-note">ข้อมูลตัดคะแนนผู้แจ้งเท็จและข้อมูลแจ้งผู้ถูกกล่าวหาถูกส่งเมื่อผลสอบสวนเข้าเงื่อนไขที่เกี่ยวข้อง</p>'
        sections.append(f'<section class="parent-diagram" id="diagram-{idx}" aria-labelledby="heading-{idx}"><h2 id="heading-{idx}">ผัง {escape(title)}</h2><p>กระบวนการย่อย {number(child_ids[0])}–{number(child_ids[-1])} · คงขอบเขตข้อมูลเข้า–ออกของ {idx}.0</p>{note}<div class="drawing">{svg}</div>{route_table}</section>')
        markdown += [f'## ผัง {title}','', '```mermaid',mermaid(m),'```','']
    check_routes(geometries)
    nav=''.join(f'<a href="#diagram-{i}">ผัง {i}.0</a>' for i in range(1,9))
    html='<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DFD Level 1 — ระบบบริหารพฤติกรรมนักเรียน</title><style>'+css+'</style></head><body><main class="page"><header><h1>DFD Level 1 — ระบบบริหารพฤติกรรมนักเรียน</h1><p>แตกกระบวนการจาก Mermaid Level 0: 8 ผังหลัก · 25 กระบวนการย่อย · สไตล์เดียวกับ Level 0</p><p>อ่านแต่ละผังจากบนลงล่าง รหัส C1–C4 เชื่อมระหว่างผังหลัก และรหัส L เชื่อมกระบวนการย่อยในผังเดียวกัน หน่วยงานและคลังข้อมูลที่แสดงซ้ำคือรายการเดียวกัน</p><nav aria-label="เลือกผังย่อย">'+nav+'</nav></header>'+''.join(sections)+'<footer>DFD Level 1 · ตรวจสมดุลข้อมูลเข้าออกทั้ง 48 ทิศทางของ Level 0 · ตรวจพิกัดเส้นและป้ายข้อมูลแล้ว</footer></main></body></html>\n'
    (ROOT/'docs/dfd-level1-student-discipline.html').write_text(html,encoding='utf-8')
    (ROOT/'docs/dfd-level1-source.md').write_text('\n'.join(markdown),encoding='utf-8')
    (HERE/'dfd-level1-model.json').write_text(json.dumps(DIAGRAMS,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    (HERE/'dfd-level1-geometry.json').write_text(json.dumps(geometries,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    (ROOT/'docs/dfd-level1-design-notes.md').write_text('\n'.join([
        '# บันทึกการออกแบบ DFD Level 1','',
        'แผนภาพ: [dfd-level1-student-discipline.html](dfd-level1-student-discipline.html)',
        'Mermaid: [dfd-level1-source.md](dfd-level1-source.md)','',
        '- แตก 1.0–8.0 ทุกกระบวนการเป็น 25 กระบวนการย่อย ไม่เพิ่มกระบวนการเลื่อนชั้นที่ไม่มีใน Mermaid ต้นฉบับ',
        '- คงหน่วยงานภายนอกเดิม และทุกการไหลระหว่างกระบวนการหลักตาม C1–C4',
        '- ตรวจสมดุลข้อมูลตาม parent_edge และ direction ครบ 48 อินเทอร์เฟซที่มีทิศทาง; ข้อมูลแบบรวมใน Level 0 ถูกแยกเป็นรายการละเอียดใน Level 1',
        '- คง D1–D8 ตามต้นฉบับ เพิ่ม D9 ภาคเรียนภายใน 1.0 และ D10 ยอดขาด/สายสะสมภายใน 3.0; ทั้งสองไม่เพิ่มอินเทอร์เฟซระหว่างผังหลัก',
        '- D10 เป็นคลังสรุปเชิงตรรกะของข้อมูลเข้าแถว ไม่ได้อ้างว่าระบบมีตารางฐานข้อมูลใหม่',
        '- การคืนคะแนนในผัง 5.0 ส่งไป 2.0 ตาม Mermaid จึงไม่เพิ่มลูกศรตรงจาก 5.0 ไป D2/D3 ซึ่งจะเปลี่ยนขอบเขต Level 0',
        '- D3 ยังคงรวมบันทึกพฤติกรรมและเกณฑ์ ไม่เพิ่ม D9 เกณฑ์ที่ขัดกับทะเบียนของแผนภาพนี้',
        '- ลูกศรอ่านและเขียนถูกแยกเป็นคนละเส้นใน Level 1 เพื่อระบุข้อมูลเดิม/ข้อมูลที่ปรับปรุง',
        '- จุดเชื่อม L เป็นการต่อเส้นภายในผังเดียวกัน ไม่ใช่กระบวนการหรือหน่วยงานใหม่',
        '- รูปทรง/สีคงตามที่ผู้ใช้เลือก: วงกลมสีส้ม สี่เหลี่ยมขาว ทรงกระบอกเทา',
        '- ใช้การตรวจ SVG/palette/ความกว้างข้อความแบบออฟไลน์ ไม่ได้ยืนยันพรีวิวเบราว์เซอร์ เพราะเครื่องมือบล็อก file://',
        '', 'สร้างใหม่: `python docs/diagrams/build_dfd_level1.py`','']),encoding='utf-8')
    print('Created Level 1 HTML, Mermaid source, model, geometry, and design notes.')

if __name__=='__main__':
    build()
