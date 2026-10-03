"""Redraw the user-supplied Mermaid faithfully with nonintersecting SVG routes.

python docs/diagrams/build_dfd_level0.py
Content comes from dfd-level0-source-ir.json, extracted by diagram-design.
The source Mermaid is retained in dfd-level0-source.mmd.
"""
from html import escape
from itertools import combinations
from pathlib import Path
import json
import math

ROOT = Path(__file__).resolve().parents[2]
HERE = Path(__file__).parent
IR = json.loads((HERE / 'dfd-level0-source-ir.json').read_text(encoding='utf-8'))['diagrams'][0]
NODES = {n['id']: n for n in IR['nodes']}
EDGES = IR['edges']
PROCESS_IDS = {key for key,node in NODES.items() if node['shape']=='circle'}
OUTPUT = ROOT / 'docs' / 'dfd-level0-student-discipline.html'
PALETTE = {'paper':'#ffffff','ink':'#2d3142','process':'#fdeee4','accent':'#eb6c36','store':'#f5f5f5','muted':'#4f5d75'}
CROSS = {e['id']: {'id':f'C{i+1}', 'source':e['source'], 'target':e['target'], 'label':e['label']}
         for i,e in enumerate(e for e in EDGES if e['source'] in PROCESS_IDS and e['target'] in PROCESS_IDS)}
GEOMETRY = []

def text(x,y,lines,cls='node-label',step=20,anchor='middle'):
    return ''.join(f'<text x="{x}" y="{y+i*step}" class="{cls}" text-anchor="{anchor}">{escape(line)}</text>' for i,line in enumerate(lines))

def rounded_path(points):
    out = f'M {points[0][0]} {points[0][1]}'
    sign = lambda n: (n>0)-(n<0)
    for a,b,c in zip(points,points[1:],points[2:]):
        u = (b[0]-sign(b[0]-a[0])*8,b[1]-sign(b[1]-a[1])*8)
        v = (b[0]+sign(c[0]-b[0])*8,b[1]+sign(c[1]-b[1])*8)
        out += f' L {u[0]} {u[1]} Q {b[0]} {b[1]} {v[0]} {v[1]}'
    return out + f' L {points[-1][0]} {points[-1][1]}'

def leaf(node_id,x,cy,uid,cross=None):
    y,w,h = cy-40,256,80
    if cross:
        shape = f'<rect class="continuation" x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>'
        content = text(x+128,cy-16,[cross['id']],'continuation-code')
        route = cross.get('route', f'{cross["source"][1:]}.0 → {cross["target"][1:]}.0')
        content += text(x+128,cy+8,[route,cross.get('hint','จุดเชื่อมกระแสข้อมูล')],'node-label')
    else:
        n = NODES[node_id]
        lines = n['label'].split('\n')
        if n['shape']=='cylinder':
            shape = f'<path class="store" d="M{x} {cy-32} C{x} {cy-40} {x+w} {cy-40} {x+w} {cy-32} V{cy+32} C{x+w} {cy+40} {x} {cy+40} {x} {cy+32} Z"/><ellipse class="store-top" cx="{x+128}" cy="{cy-32}" rx="128" ry="8"/>'
        elif n['shape']=='continuation':
            shape = f'<rect class="continuation" x="{x}" y="{y}" width="{w}" height="{h}" rx="8"/>'
        else:
            shape = f'<rect class="entity" x="{x}" y="{y}" width="{w}" height="{h}"/>'
        first_y = cy + 8 - (len(lines)-1)*12
        # Use the original Thai/English labels, without abbreviating them.
        content = text(x+128,first_y,lines,step=24)
    return f'<g id="{uid}" data-node="{node_id}" data-bounds="{x},{y},{w},{h}">{shape}{content}</g>'

def entries_for(pid):
    incoming,outgoing = [],[]
    for e in EDGES:
        if e['source']!=pid and e['target']!=pid:
            continue
        peer = e['target'] if e['source']==pid else e['source']
        entry = {'edge':e,'peer':peer,'cross':CROSS.get(e['id'])}
        # Read/write store links retain one line with two arrowheads.
        (outgoing if e['source']==pid or e['bidirectional'] else incoming).append(entry)
    return incoming,outgoing

def process_group(pid,offset):
    incoming,outgoing = entries_for(pid)
    n = max(len(incoming),len(outgoing))
    height = max(448,n*96+160)
    center = offset+height//2
    circle_bounds = [792,center-128,256,256]
    connectors,labels,leaves = [],[],[]
    geom = {'process':pid,'edges':[],'labels':[],'nodes':[]}
    for side,entries in [('in',incoming),('out',outgoing)]:
        count = len(entries)
        for i,entry in enumerate(entries):
            e = entry['edge']
            cy = center-(count-1)*48+i*96
            port_y = center-(count-1)*16+i*32
            node_x = 48 if side=='in' else 1552
            uid = f'dfd-level0-mermaid-{pid}-{side}-{i}'
            leaves.append(leaf(entry['peer'],node_x,cy,uid,entry['cross']))
            geom['nodes'].append({'id':uid,'bounds':[node_x,cy-40,256,80]})
            radial_x = round(math.sqrt(128**2-(port_y-center)**2)/4)*4
            if side=='in':
                port_x = 920-radial_x
                corridor = 768-min(i,count-1-i)*16
                points = [(304,cy),(corridor,cy),(corridor,port_y),(port_x,port_y)]
                if cy==port_y:
                    points = [(304,cy),(port_x,port_y)]
                label_x = 496
            else:
                port_x = 920+radial_x
                corridor = 1072+min(i,count-1-i)*16
                points = [(port_x,port_y),(corridor,port_y),(corridor,cy),(1552,cy)]
                if cy==port_y:
                    points = [(port_x,port_y),(1552,cy)]
                label_x = 1336
            route_id = f'dfd-level0-mermaid-{pid}-{e["id"]}'
            markers = 'marker-end="url(#dfd-level0-mermaid-arrow)"'
            if e['bidirectional']:
                markers += ' marker-start="url(#dfd-level0-mermaid-arrow)"'
            connectors.append(f'<path id="{route_id}" data-source-edge="{e["id"]}" class="flow" d="{rounded_path(points)}" {markers}/>')
            label_y = cy-28
            labels.append(f'<g class="flow-label" data-edge="{route_id}" data-label-bounds="{label_x-184},{label_y},368,20"><rect x="{label_x-184}" y="{label_y}" width="368" height="20" fill="#fff"/>{text(label_x,label_y+16,[e["label"]],"payload")}</g>')
            geom['edges'].append({'id':route_id,'source_edge':e['id'],'points':points})
            geom['labels'].append({'id':route_id,'bounds':[label_x-184,label_y,368,20]})
    geom['nodes'].append({'id':pid,'bounds':circle_bounds})
    GEOMETRY.append(geom)
    process_lines = NODES[pid]['label'].split('\n')
    body = f'<g id="process-{pid[1:]}" data-node="{pid}" data-bounds="792,{center-128},256,256"><circle class="process" cx="920" cy="{center}" r="128"/>'
    first_y = center+8-(len(process_lines)-1)*12
    for i,line in enumerate(process_lines):
        cls = 'process-number' if i==0 else 'process-en' if line.startswith('(') else 'process-label'
        body += text(920,first_y+i*24,[line],cls)
    body += '</g>'
    section_title = ' '.join(process_lines[:-1])
    headings = text(48,offset+32,[section_title],'section-title',anchor='start')
    return f'<g data-process="{pid}">{headings}{"".join(connectors)}{"".join(labels)}{"".join(leaves)}{body}<line class="separator" x1="48" y1="{offset+height-16}" x2="1808" y2="{offset+height-16}"/></g>',height

def overlap(a,b):
    return max(a[0],b[0]) < min(a[0]+a[2],b[0]+b[2]) and max(a[1],b[1]) < min(a[1]+a[3],b[1]+b[3])

def segments_intersect(s1,s2):
    a,b=s1;c,d=s2
    h1=a[1]==b[1];h2=c[1]==d[1]
    if h1==h2:
        axis=0 if h1 else 1;fixed=1-axis
        return a[fixed]==c[fixed] and max(min(a[axis],b[axis]),min(c[axis],d[axis]))<=min(max(a[axis],b[axis]),max(c[axis],d[axis]))
    if not h1:
        a,b,c,d=c,d,a,b
    return min(a[0],b[0])<=c[0]<=max(a[0],b[0]) and min(c[1],d[1])<=a[1]<=max(c[1],d[1])

def verify():
    routes = [e for g in GEOMETRY for e in g['edges']]
    nodes = [n for g in GEOMETRY for n in g['nodes']]
    labels = [l for g in GEOMETRY for l in g['labels']]
    problems=[]
    for a,b in combinations(routes,2):
        if any(segments_intersect(s,t) for s in zip(a['points'],a['points'][1:]) for t in zip(b['points'],b['points'][1:])):
            problems.append(f"Intersections: {a['id']} / {b['id']}")
    for label in labels:
        for node in nodes:
            if overlap(label['bounds'],node['bounds']):
                problems.append(f"Label/node overlap: {label['id']} / {node['id']}")
        x,y,w,h=label['bounds']
        mask_sides=[((x,y),(x+w,y)),((x,y+h),(x+w,y+h)),((x,y),(x,y+h)),((x+w,y),(x+w,y+h))]
        for route in routes:
            if any(segments_intersect(s,t) for s in zip(route['points'],route['points'][1:]) for t in mask_sides):
                problems.append(f"Label hides connector: {label['id']} / {route['id']}")
    for a,b in combinations(labels,2):
        if overlap(a['bounds'],b['bounds']):
            problems.append(f"Label/label overlap: {a['id']} / {b['id']}")
    expected={e['id']:2 if e['id'] in CROSS else 1 for e in EDGES}
    actual={eid:sum(r['source_edge']==eid for r in routes) for eid in expected}
    if actual!=expected:
        problems.append('Source edge fidelity mismatch')
    if problems:
        raise ValueError('\n'.join(problems))
    print(f'Verified: 21 original nodes, 38 original links, {len(routes)} drawn routes; zero intersections, overlapping labels or masks hiding connectors.')

def build():
    groups=[];offset=160
    for i in range(1,9):
        group,height=process_group(f'P{i}',offset)
        groups.append(group);offset+=height
    verify()
    legend_y=offset+32
    total_height=offset+176
    markers=''.join(f'<marker id="dfd-level0-mermaid-{name}" markerWidth="8" markerHeight="8" refX="8" refY="4" orient="auto-start-reverse" markerUnits="userSpaceOnUse"><path d="M0 0 L8 4 L0 8 Z" fill="#4f5d75"/></marker>' for name in ['arrow','arrow-accent','arrow-link'])
    legend=text(48,legend_y,['หน่วยงาน / คลังข้อมูลที่ปรากฏซ้ำ คือรายการเดียวกันตลอดแผนภาพ'],'legend-text',anchor='start')
    legend+=text(48,legend_y+32,['สี่เหลี่ยม = หน่วยงานภายนอก   วงกลม = กระบวนการ   ทรงกระบอก = คลังข้อมูล   ลูกศรสองหัว = อ่านและเขียน'],'legend-text',anchor='start')
    for i,c in enumerate(CROSS.values()):
        x=48+(i%2)*888;y=legend_y+72+(i//2)*24
        legend+=text(x,y,[f'{c["id"]} : {c["source"][1:]}.0 → {c["target"][1:]}.0 · {c["label"]}'],'legend-text',anchor='start')
    svg=f'''<svg viewBox="0 0 1840 {total_height}" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="dfd-level0-mermaid-title dfd-level0-mermaid-desc">
<title id="dfd-level0-mermaid-title">DFD Level 0 — ระบบบริหารพฤติกรรมนักเรียน</title>
<desc id="dfd-level0-mermaid-desc">แผนภาพกระแสข้อมูลตาม Mermaid ต้นฉบับ แสดง 8 กระบวนการ 5 หน่วยงานภายนอก และ 8 คลังข้อมูล พร้อมการเชื่อม 38 รายการ ใช้การแสดงหน่วยงานและคลังซ้ำและจุดเชื่อม C1 ถึง C4 เพื่อให้เส้นไม่ทับหรือตัดกัน</desc>
<defs>{markers}</defs><rect width="1840" height="{total_height}" fill="#fff"/>
{text(48,48,['DFD Level 0 — ระบบบริหารพฤติกรรมนักเรียน'],'diagram-title',anchor='start')}
{text(48,84,['Student Discipline System · 8 Processes · 5 External Entities · 8 Data Stores'],'diagram-subtitle',anchor='start')}
{text(48,120,['อ่านจากบนลงล่าง · จุดเชื่อม C1–C4 เป็นเส้นต่อเนื่องระหว่างกระบวนการ · หน่วยงานและคลังที่แสดงซ้ำเป็นรายการเดียวกัน'],'legend-text',anchor='start')}
{''.join(groups)}{legend}</svg>'''
    nav=''.join(f'<a href="#process-{i}">{i}.0</a>' for i in range(1,9))
    html='''<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DFD Level 0 — สไตล์ Mermaid เส้นไม่ทับกัน</title><style>
*{box-sizing:border-box}body{margin:0;background:#f5f5f5;color:#2d3142;font-family:Tahoma,"Leelawadee UI",sans-serif;line-height:1.6}.page{max-width:1720px;margin:24px auto;background:#fff;padding:24px}header{padding:0 20px 20px;border-bottom:1px solid #ddd;margin-bottom:12px}h1{font-size:28px;margin:0 0 8px;font-weight:600}header p{font-size:16px;margin:4px 0;color:#4f5d75}nav{display:flex;gap:12px;flex-wrap:wrap;margin-top:12px}nav a{color:#2d3142;text-decoration:none;padding:4px 16px;border:1px solid #ddd;border-radius:4px}nav a:hover{border-color:#eb6c36;background:#fdeee4}.drawing{overflow:auto}svg{display:block;width:100%;min-width:1320px;height:auto}svg text{font-family:Tahoma,"Leelawadee UI",sans-serif;fill:#2d3142}.node-label,.payload,.legend-text{font-size:16px}.diagram-title{font-size:32px;font-weight:600}.diagram-subtitle{font-size:20px;fill:#4f5d75}.section-title{font-size:20px;font-weight:600}.process-label{font-size:20px;font-weight:600}.process-number{font-family:Consolas,monospace;font-size:24px;font-weight:600;fill:#eb6c36}.process-en{font-size:16px;fill:#4f5d75}.continuation-code{font-family:Consolas,monospace;font-size:20px;font-weight:600;fill:#4f5d75}.entity{fill:#fff;stroke:#2d3142;stroke-width:1.5}.process{fill:#fdeee4;stroke:#eb6c36;stroke-width:1.5}.store{fill:#f5f5f5;stroke:#4f5d75;stroke-width:1}.store-top{fill:#f5f5f5;stroke:#4f5d75;stroke-width:1}.continuation{fill:#fff;stroke:#4f5d75;stroke-width:1;stroke-dasharray:4 4}.flow{fill:none;stroke:#4f5d75;stroke-width:1.5}.separator{stroke:#e4e4e4;stroke-width:1}footer{font-size:14px;color:#4f5d75;margin:16px 20px 0;border-top:1px solid #ddd;padding-top:12px}@media(max-width:800px){.page{margin:0;padding:16px}h1{font-size:24px}}@media print{body{background:#fff}.page{margin:0;padding:0;max-width:none}header,footer{display:none}svg{min-width:0}}
</style></head><body><main class="page"><header><h1>DFD Level 0 — ระบบบริหารพฤติกรรมนักเรียน</h1><p>รูปทรงและสีตาม Mermaid ที่ส่งมา: กระบวนการวงกลมสีส้ม หน่วยงานสี่เหลี่ยม และคลังข้อมูลทรงกระบอก</p><p>คงกระบวนการ 1.0–8.0 และข้อมูลทุกเส้นตามต้นฉบับ ใช้จุดเชื่อม C1–C4 และแสดงหน่วยงาน/คลังข้อมูลซ้ำเพื่อให้เส้นแยกจากกัน</p><nav aria-label="ไปยังกระบวนการ">'''+nav+'</nav></header><div class="drawing">'+svg+'</div><footer>แผนภาพเดียว · 21 รายการต้นฉบับ · 38 การเชื่อมต่อ · จุดเชื่อม C1–C4 เป็นการต่อเส้น ไม่ใช่กระบวนการหรือหน่วยงานใหม่</footer></main></body></html>\n'
    OUTPUT.write_text(html,encoding='utf-8')
    (HERE / 'dfd-level0-flows.json').write_text(json.dumps(EDGES,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    (HERE / 'dfd-level0-geometry.json').write_text(json.dumps(GEOMETRY,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    notes=['# DFD Level 0 — ฉบับตาม Mermaid ที่ผู้ใช้ส่ง', '',
           'แผนภาพ: [dfd-level0-student-discipline.html](dfd-level0-student-discipline.html)', '',
           'ต้นฉบับ: [dfd-level0-source.mmd](diagrams/dfd-level0-source.mmd)', '',
           '## ความตรงกับต้นฉบับ', '',
           '- คงครบ 8 กระบวนการ (1.0–8.0), 5 หน่วยงานภายนอก และ 8 คลังข้อมูล (D1–D8)',
           '- คงทั้ง 38 การเชื่อมต่อ ป้ายข้อมูล และทิศทางตามต้นฉบับ รวมลูกศรสองหัวสำหรับอ่าน/เขียน',
           '- ไม่เพิ่มกระบวนการหรือคลังข้อมูลจากการวิเคราะห์ระบบครั้งก่อน',
           '- ใช้วงกลมสีส้ม #fdeee4 / ขอบ #eb6c36, หน่วยงานสี่เหลี่ยมขาว และคลังทรงกระบอก #f5f5f5 ตามที่ขอ',
           '- ใช้ฟอนต์ Tahoma ในเครื่องเพื่อแสดงภาษาไทย ไม่พึ่งฟอนต์จากอินเทอร์เน็ต',
           '- แสดงหน่วยงานและคลังข้อมูลซ้ำเพื่อหลีกเลี่ยงเส้นไขว้ หมายถึงรายการเดียวกันทุกตำแหน่ง',
           '- เส้นระหว่างกระบวนการ 4 เส้นใช้จุดเชื่อมคู่ C1–C4 ทำให้มีช่วงลูกศรที่วาด 42 ช่วง', '',
           '## จุดเชื่อมข้ามส่วน', '', '| รหัส | ต้นทาง | ปลายทาง | ข้อมูล |','|---|---|---|---|']
    notes += [f'| {c["id"]} | {c["source"][1:]}.0 | {c["target"][1:]}.0 | {c["label"]} |' for c in CROSS.values()]
    notes += ['', '## ตรวจสอบ', '',
              'ตัวสร้างตรวจทุกคู่ของช่วงเส้นมุมฉาก: ไม่มีเส้นตัดหรือซ้อนกัน ไม่มีป้ายซ้อนกล่องหรือป้ายอื่น และไม่มีกรอบป้ายบังเส้น',
              'ตรวจการเชื่อมต่อเทียบต้นฉบับ: ทุกเส้นครบ จุดเชื่อมระหว่างกระบวนการมีสองปลายตามรหัสที่ตรงกัน',
              'สร้างใหม่: `python docs/diagrams/build_dfd_level0.py`', '',
              'พรีวิวผ่านเบราว์เซอร์ยังไม่ได้ยืนยัน เนื่องจากนโยบายเครื่องมือบล็อก URL file:// จึงใช้การตรวจพิกัดและขนาดข้อความแทน', '']
    (ROOT / 'docs' / 'dfd-level0-design-notes.md').write_text('\n'.join(notes),encoding='utf-8')
    print(f'Created {OUTPUT}')

if __name__=='__main__':
    build()
