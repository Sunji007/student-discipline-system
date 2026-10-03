"""Draw the complete Level 0 on one canvas, with one copy of every node.

Route on a 24px orthogonal grid. Shared strokes are forbidden. Genuine
perpendicular crossings receive an 8px bridge on the later route.
"""
from html import escape
from itertools import combinations
from pathlib import Path
from PIL import ImageFont
import heapq
import json
import math
import random
import re
import build_dfd_level0 as source

ROOT=Path(__file__).resolve().parents[2]
HERE=Path(__file__).parent
GRID=24
WIDTH=3600
HEIGHT=3500
BOTTOM=3288
FONT=ImageFont.truetype('C:/Windows/Fonts/tahoma.ttf',18)
POSITIONS={
    'ADMIN':(288,168),'TEACH':(744,168),'DISC':(1200,168),'STU':(1656,168),'PAR':(2112,168),
    'P1':(288,624),'P3':(864,624),'P4':(1440,624),'P7':(2016,624),
    'P8':(288,1440),'P2':(864,1440),'P5':(1440,1440),'P6':(2016,1440),
    'D1':(288,1032),'D4':(864,1032),'D5':(1440,1032),'D8':(2016,1032),
    'D2':(624,1944),'D3':(1104,1944),'D6':(1584,1944),'D7':(2016,1944),
}
POSITIONS={
    'ADMIN':(432,240),'TEACH':(1056,240),'DISC':(1776,240),'STU':(2856,240),'PAR':(432,2160),
    'P1':(432,936),'P3':(1056,936),'P2':(1776,936),'P4':(2496,936),'P7':(3120,936),
    'P8':(1056,2160),'P5':(2160,2160),'P6':(3000,2160),
    'D1':(432,1488),'D4':(1056,1488),'D2':(1584,1488),'D3':(1992,1488),
    'D5':(2496,1488),'D8':(3120,1488),'D6':(2160,2760),'D7':(3000,2760),
}
NODES=source.NODES
EDGES=source.EDGES
BOUNDS={key:(cx-144,cy-(144 if NODES[key]['shape']=='circle' else 60),288,
             288 if NODES[key]['shape']=='circle' else 120) for key,(cx,cy) in POSITIONS.items()}
DIRECTIONS=[(1,0),(0,1),(-1,0),(0,-1)]
PORTS={}

def inflated(rect,pad):
    x,y,w,h=rect
    return x-pad,y-pad,w+2*pad,h+2*pad

def contains(rect,p):
    x,y,w,h=rect
    return x<=p[0]<=x+w and y<=p[1]<=y+h

def hit_segment(rect,a,b):
    x,y,w,h=rect
    if a[0]==b[0]:
        return x<=a[0]<=x+w and max(min(a[1],b[1]),y)<=min(max(a[1],b[1]),y+h)
    return y<=a[1]<=y+h and max(min(a[0],b[0]),x)<=min(max(a[0],b[0]),x+w)

def axis(a,b):
    return 1 if a[1]==b[1] else 2

def unit_key(a,b):
    return tuple(sorted((a,b)))

def compact(points):
    out=[]
    for p in points:
        if out and p==out[-1]:
            continue
        if len(out)>1 and axis(out[-2],out[-1])==axis(out[-1],p):
            out[-1]=p
        else:
            out.append(p)
    return out

def assign_ports():
    groups={}
    for e in EDGES:
        for node,peer in [(e['source'],e['target']),(e['target'],e['source'])]:
            cx,cy=POSITIONS[node];px,py=POSITIONS[peer]
            side=('S' if py>cy else 'N') if abs(py-cy)>abs(px-cx) else ('E' if px>cx else 'W')
            groups.setdefault((node,side),[]).append((e['id'],peer))
    for (node,side),entries in groups.items():
        cx,cy=POSITIONS[node]
        entries.sort(key=lambda item:(POSITIONS[item[1]][0 if side in ['N','S'] else 1],item[0]))
        count=len(entries)
        offsets=list(range(-(count//2)*GRID,(count//2)*GRID+1,GRID))
        if count%2==0:
            offsets.remove(0)
        for (eid,_),off in zip(entries,offsets):
            if NODES[node]['shape']=='circle':
                extent=round(math.sqrt(144*144-off*off)/4)*4
            else:
                extent=144 if side in ['E','W'] else 60
            if side=='E':
                point=(cx+extent,cy+off);lead=(cx+168,cy+off)
            elif side=='W':
                point=(cx-extent,cy+off);lead=(cx-168,cy+off)
            elif side=='S':
                point=(cx+off,cy+extent);lead=(cx+off,cy+(168 if NODES[node]['shape']=='circle' else 96))
            else:
                point=(cx+off,cy-extent);lead=(cx+off,cy-(168 if NODES[node]['shape']=='circle' else 96))
            PORTS[(eid,node)]={'point':point,'lead':lead,'side':side}
    assert len(PORTS)==2*len(EDGES)

assign_ports()
ENDPOINTS={v['lead'] for v in PORTS.values()}
STATIC_BLOCKED={(x,y) for x in range(48,WIDTH-47,GRID) for y in range(120,BOTTOM+1,GRID)
                if any(contains(inflated(b,12),(x,y)) for b in BOUNDS.values())}

def astar(start,end,occupied,flags,blocked,seed):
    initial=(start[0],start[1],-1)
    queue=[(0,0,initial)]
    distances={initial:0}
    previous={}
    end_state=None
    while queue:
        _,cost,state=heapq.heappop(queue)
        if cost!=distances.get(state):
            continue
        x,y,prior=state;p=(x,y)
        if p==end:
            end_state=state;break
        old=flags.get(p,0)
        for direction,(dx,dy) in enumerate(DIRECTIONS):
            if prior>=0 and direction==(prior+2)%4:
                continue
            current_axis=1 if direction%2==0 else 2
            if old and prior>=0 and direction%2!=prior%2:
                continue
            q=(x+dx*GRID,y+dy*GRID)
            if not (48<=q[0]<=WIDTH-48 and 120<=q[1]<=BOTTOM):
                continue
            if q in blocked or (q in ENDPOINTS and q not in [start,end]):
                continue
            if unit_key(p,q) in occupied:
                continue
            peer_flags=flags.get(q,0)
            if peer_flags==3 or peer_flags==current_axis:
                continue
            turn=2 if prior>=0 and prior!=direction else 0
            crossing=48 if peer_flags else 0
            nxt=(q[0],q[1],direction)
            nxt_cost=cost+1+turn+crossing
            if nxt_cost<distances.get(nxt,float('inf')):
                distances[nxt]=nxt_cost;previous[nxt]=state
                heuristic=(abs(q[0]-end[0])+abs(q[1]-end[1]))/GRID
                heapq.heappush(queue,(nxt_cost+heuristic,nxt_cost,nxt))
    if end_state is None:
        return None
    path=[];state=end_state
    while True:
        path.append((state[0],state[1]))
        if state==initial:
            break
        state=previous[state]
    return list(reversed(path))

def candidate_label(points,label,previous_routes,previous_labels):
    width=math.ceil((FONT.getlength(label)+24)/4)*4
    segments=list(zip(points,points[1:]))
    ranked=sorted(enumerate(segments),key=lambda pair:-(abs(pair[1][0][0]-pair[1][1][0])+abs(pair[1][0][1]-pair[1][1][1])))
    for index,(a,b) in ranked:
        horizontal=a[1]==b[1]
        low,high=sorted([a[0],b[0]] if horizontal else [a[1],b[1]])
        span=width if horizontal else 24
        if high-low<span+48:
            continue
        mid=round(((low+high)/2)/4)*4
        centers=[mid]
        for shift in range(GRID,int(high-low),GRID):
            centers.extend([mid-shift,mid+shift])
        for center in centers:
            if center-span/2<low+24 or center+span/2>high-24:
                continue
            rects=[(center-width/2,a[1]-32,width,24)] if horizontal else [
                (a[0]+8,center-12,width,24),(a[0]-width-8,center-12,width,24)]
            for rect in rects:
                x,y,w,h=rect
                if not (24<=x and x+w<=WIDTH-24 and 96<=y and y+h<=BOTTOM+32):
                    continue
                if any(contains(inflated(rect,24),p) for p in ENDPOINTS):
                    continue
                if any(source.overlap(inflated(rect,8),bnd) for bnd in BOUNDS.values()):
                    continue
                if any(source.overlap(inflated(rect,8),r['label_bounds']) for r in previous_labels):
                    continue
                if any(hit_segment(inflated(rect,10),u,v) for r in previous_routes for u,v in zip(r['points'],r['points'][1:])):
                    continue
                if any(j!=index and hit_segment(inflated(rect,8),u,v) for j,(u,v) in enumerate(segments)):
                    continue
                return tuple(int(v) for v in rect)
    return None

def intersection(a,b,c,d):
    if axis(a,b)==axis(c,d):
        return None
    if axis(a,b)==2:
        a,b,c,d=c,d,a,b
    p=(c[0],a[1])
    if min(a[0],b[0])<p[0]<max(a[0],b[0]) and min(c[1],d[1])<p[1]<max(c[1],d[1]):
        return p
    return None

def route_all(order,seed):
    occupied=set();flags={};blocked=STATIC_BLOCKED.copy();routes=[]
    for e in order:
        first=PORTS[(e['id'],e['source'])];last=PORTS[(e['id'],e['target'])]
        start,end=first['lead'],last['lead']
        extra=set();accepted=None
        for attempt in range(5):
            path=astar(start,end,occupied,flags,blocked|extra,seed)
            if path is None:
                break
            points=compact([first['point'],*path,last['point']])
            label_rect=candidate_label(points,e['label'],routes,routes)
            if label_rect is not None:
                accepted=(path,points,label_rect);break
            # A label needs open space; force another corridor if the first is tight.
            interior=path[2:-2]
            if not interior:
                break
            extra.add(interior[len(interior)//2])
        if accepted is None:
            print(f'Layout {seed}: retry required at {e["id"]} after {len(routes)} routes; path={path is not None}, start={start}, end={end}',flush=True)
            return None
        path,points,label_rect=accepted
        hops=[]
        for old in routes:
            for a,b in zip(points,points[1:]):
                for c,d in zip(old['points'],old['points'][1:]):
                    p=intersection(a,b,c,d)
                    if p:
                        hops.append({'point':p,'over':old['id'],'axis':axis(a,b)})
        for a,b in zip(path,path[1:]):
            occupied.add(unit_key(a,b))
            bit=axis(a,b)
            flags[a]=flags.get(a,0)|bit;flags[b]=flags.get(b,0)|bit
        routes.append({'id':e['id'],'points':points,'hops':hops,'label_bounds':label_rect,'label':e['label']})
        # Reserve the text and enough space for future 8px bridge arcs.
        blocked.update((x,y) for x in range(48,WIDTH-47,GRID) for y in range(120,BOTTOM+1,GRID)
                       if contains(inflated(label_rect,12),(x,y)))
    return routes

def bridge_path(route):
    points=route['points'];out=f'M {points[0][0]} {points[0][1]}'
    sign=lambda value:(value>0)-(value<0)
    for i,(a,b) in enumerate(zip(points,points[1:])):
        dx,dy=sign(b[0]-a[0]),sign(b[1]-a[1])
        end=b if i==len(points)-2 else (b[0]-dx*8,b[1]-dy*8)
        crossings=[h['point'] for h in route['hops'] if contains((min(a[0],b[0]),min(a[1],b[1]),abs(a[0]-b[0]),abs(a[1]-b[1])),h['point'])]
        crossings.sort(key=lambda p:abs(p[0]-a[0])+abs(p[1]-a[1]))
        for cx,cy in crossings:
            before=(cx-dx*8,cy-dy*8)
            out+=f' L {before[0]} {before[1]}'
            sweep=1 if dx>0 or dy<0 else 0
            out+=f' a 8 8 0 0 {sweep} {dx*16} {dy*16}'
        out+=f' L {end[0]} {end[1]}'
        if i<len(points)-2:
            c=points[i+2];nx,ny=sign(c[0]-b[0]),sign(c[1]-b[1])
            out+=f' Q {b[0]} {b[1]} {b[0]+nx*8} {b[1]+ny*8}'
    return out

def node_markup(key):
    cx,cy=POSITIONS[key];x,y,w,h=BOUNDS[key]
    n=NODES[key];lines=n['label'].split('\n')
    body=f'<g id="unified-node-{key}" data-node="{key}" data-bounds="{x},{y},{w},{h}">'
    if n['shape']=='circle':
        body+=f'<circle class="process" cx="{cx}" cy="{cy}" r="144"/>'
        first=cy+8-(len(lines)-1)*12
        for i,line in enumerate(lines):
            cls='process-number' if i==0 else 'process-en' if line.startswith('(') else 'process-label'
            body+=source.text(cx,first+i*24,[line],cls)
    else:
        if n['shape']=='cylinder':
            body+=f'<path class="store" d="M{x} {cy-48} C{x} {cy-60} {x+w} {cy-60} {x+w} {cy-48} V{cy+48} C{x+w} {cy+60} {x} {cy+60} {x} {cy+48} Z"/><ellipse class="store-top" cx="{cx}" cy="{cy-48}" rx="144" ry="12"/>'
            first=cy+16-(len(lines)-1)*12
        else:
            body+=f'<rect class="entity" x="{x}" y="{y}" width="{w}" height="{h}"/>'
            first=cy+8-(len(lines)-1)*12
        for i,line in enumerate(lines):
            cls='node-en' if line.startswith('(') else 'node-label'
            body+=source.text(cx,first+i*24,[line],cls)
    return body+'</g>'

def verify(routes):
    assert len(routes)==len(EDGES)==38
    assert {r['id'] for r in routes}=={e['id'] for e in EDGES}
    for a,b in combinations(routes,2):
        for u,v in zip(a['points'],a['points'][1:]):
            for p,q in zip(b['points'],b['points'][1:]):
                if axis(u,v)==axis(p,q):
                    assert not source.segments_intersect((u,v),(p,q)),('shared stroke',a['id'],b['id'])
                else:
                    crossing=intersection(u,v,p,q)
                    if crossing:
                        assert any(h['point']==crossing and h['over']==a['id'] for h in b['hops']),('missing bridge',a['id'],b['id'])
                    else:
                        assert not source.segments_intersect((u,v),(p,q)),('shared corner/endpoint',a['id'],b['id'])
    for r in routes:
        e=next(e for e in EDGES if e['id']==r['id'])
        for key,bnd in BOUNDS.items():
            if key not in [e['source'],e['target']]:
                assert not any(hit_segment(bnd,a,b) for a,b in zip(r['points'],r['points'][1:])),('route behind node',r['id'],key)
            assert not source.overlap(r['label_bounds'],bnd),('label/node',r['id'],key)
        for other in routes:
            assert not any(hit_segment(r['label_bounds'],a,b) for a,b in zip(other['points'],other['points'][1:])),('label hides stroke',r['id'],other['id'])
        for h in r['hops']:
            cx,cy=h['point']
            bridge=(cx-8,cy-8,16,8) if h['axis']==1 else (cx-8,cy-8,8,16)
            assert not any(source.overlap(bridge,b) for b in BOUNDS.values()),('bridge/node',r['id'])
            assert not any(source.overlap(bridge,other['label_bounds']) for other in routes),('bridge/label',r['id'])
    for a,b in combinations(routes,2):
        assert not source.overlap(a['label_bounds'],b['label_bounds']),('labels overlap',a['id'],b['id'])
    print(f'Unified verification: 21 unique nodes, 38 original links, {sum(len(r["hops"]) for r in routes)} bridges; no shared strokes, uncovered crossings, label collisions or routes behind nodes.',flush=True)

def build():
    candidates=[]
    for seed in range(12):
        rng=random.Random(seed)
        def length(e):
            a=POSITIONS[e['source']];b=POSITIONS[e['target']]
            return abs(a[0]-b[0])+abs(a[1]-b[1])
        if seed==0:
            order=sorted(EDGES,key=length)
        elif seed==1:
            order=sorted(EDGES,key=length,reverse=True)
        else:
            order=sorted(EDGES,key=lambda e:length(e)*(0.65+rng.random()*0.7))
        routes=route_all(order,seed)
        if routes is None:
            continue
        score=(sum(len(r['hops']) for r in routes),sum(abs(a[0]-b[0])+abs(a[1]-b[1]) for r in routes for a,b in zip(r['points'],r['points'][1:])))
        print(f'Layout {seed}: {score[0]} bridges, route length {score[1]}px',flush=True)
        candidates.append((score,routes))
    if not candidates:
        raise ValueError('Could not create a complete layout; existing HTML preserved.')
    _,routes=min(candidates,key=lambda candidate:candidate[0])
    verify(routes)
    prefix='dfd-level0-unified'
    markers=''.join(f'<marker id="{prefix}-{name}" markerWidth="10" markerHeight="8" refX="10" refY="4" orient="auto-start-reverse" markerUnits="userSpaceOnUse"><path d="M0 0 L10 4 L0 8 Z" fill="#4f5d75"/></marker>' for name in ['arrow','arrow-accent','arrow-link'])
    arrows=[];labels=[]
    for r in routes:
        e=next(e for e in EDGES if e['id']==r['id'])
        marker=f'marker-end="url(#{prefix}-arrow)"'
        if e['bidirectional']:
            marker+=f' marker-start="url(#{prefix}-arrow)"'
        arrows.append(f'<path id="unified-flow-{r["id"]}" class="flow" data-source-edge="{r["id"]}" d="{bridge_path(r)}" {marker}/>')
        x,y,w,h=r['label_bounds']
        labels.append(f'<g class="flow-label" data-edge="{r["id"]}" data-label-bounds="{x},{y},{w},{h}"><rect x="{x}" y="{y}" width="{w}" height="{h}" fill="#fff"/>{source.text(x+w/2,y+20,[r["label"]],"payload")}</g>')
    svg=f'<svg class="unified" viewBox="0 0 {WIDTH} {HEIGHT}" xmlns="http://www.w3.org/2000/svg" role="img" aria-labelledby="{prefix}-title {prefix}-desc"><title id="{prefix}-title">DFD Level 0 — ระบบบริหารพฤติกรรมนักเรียน</title><desc id="{prefix}-desc">ผังรวม DFD Level 0 แสดงกระบวนการ 1.0 ถึง 8.0 หน่วยงานภายนอก 5 กลุ่ม และคลังข้อมูล D1 ถึง D8 อย่างละหนึ่งตำแหน่ง เชื่อมครบ 38 รายการด้วยเส้นมุมฉากและโค้งข้ามในจุดตัด</desc><defs>{markers}</defs><rect width="{WIDTH}" height="{HEIGHT}" fill="#fff"/>{source.text(48,48,["DFD Level 0 — ระบบบริหารพฤติกรรมนักเรียน"],"diagram-title",anchor="start")}{source.text(48,84,["Student Discipline System · 8 Processes · 5 External Entities · 8 Data Stores"],"diagram-subtitle",anchor="start")}{"".join(arrows)}{"".join(labels)}{"".join(node_markup(key) for key in NODES)}<line x1="48" y1="3352" x2="3552" y2="3352" stroke="#ddd"/>{source.text(48,3388,["สี่เหลี่ยม = หน่วยงานภายนอก   วงกลม = กระบวนการ   ทรงกระบอก = คลังข้อมูล   ลูกศรสองหัว = อ่าน/เขียน"],"legend-text",anchor="start")}{source.text(48,3428,["โค้งข้ามเส้น = ทางเดินตัดกันโดยไม่เชื่อมต่อ · ทุกกระบวนการและคลังข้อมูลปรากฏครั้งเดียว"],"legend-text",anchor="start")}</svg>'
    css='''*{box-sizing:border-box}body{margin:0;background:#f5f5f5;color:#2d3142;font-family:Tahoma,"Leelawadee UI",sans-serif;line-height:1.6}.page{max-width:1800px;margin:20px auto;padding:24px;background:#fff}header{margin:0 12px 16px;padding-bottom:16px;border-bottom:1px solid #ddd}h1{margin:0 0 8px;font-size:28px;font-weight:600}header p{margin:4px 0;font-size:16px;color:#4f5d75}.drawing{overflow:auto}svg{display:block;width:100%;min-width:1560px;height:auto}svg text{font-family:Tahoma,"Leelawadee UI",sans-serif;fill:#2d3142}.node-label,.payload,.legend-text{font-size:16px}.unified .node-label{font-size:20px;font-weight:600}.unified .payload,.unified .legend-text{font-size:18px}.node-en,.process-en{font-size:16px;fill:#4f5d75}.process-label{font-size:20px;font-weight:600}.process-number{font-family:Consolas,monospace;font-size:24px;font-weight:600;fill:#eb6c36}.continuation-code{font-family:Consolas,monospace;font-size:20px;font-weight:600}.diagram-title{font-size:32px;font-weight:600}.diagram-subtitle,.section-title{font-size:20px;fill:#4f5d75}.entity{fill:#fff;stroke:#2d3142;stroke-width:1.5}.process{fill:#fdeee4;stroke:#eb6c36;stroke-width:1.5}.store,.store-top{fill:#f5f5f5;stroke:#4f5d75;stroke-width:1}.flow{fill:none;stroke:#4f5d75;stroke-width:1.5}.continuation{fill:#fff;stroke:#4f5d75;stroke-width:1;stroke-dasharray:4 4}.separator{stroke:#ddd;stroke-width:1}footer{margin:16px 12px 0;padding-top:12px;border-top:1px solid #ddd;font-size:14px;color:#4f5d75}@media(max-width:800px){.page{margin:0;padding:12px}h1{font-size:24px}}@page{size:A3 portrait;margin:10mm}@media print{body{background:#fff}.page{margin:0;padding:0}header,footer{display:none}svg{min-width:0}}'''
    html='<!doctype html><html lang="th"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DFD Level 0 — ผังรวม</title><style>'+css+'</style></head><body><main class="page"><header><h1>DFD Level 0 — ผังรวมทั้งระบบ</h1><p>รวมกระบวนการ 1.0–8.0 ในผังเดียว พร้อมคลังข้อมูล D1–D8 และผู้ใช้งาน 5 กลุ่ม ทุกสัญลักษณ์ปรากฏครั้งเดียว</p><p>เส้นเชื่อมต่อครบตาม Mermaid ต้นฉบับ แต่ละเส้นมีทางเดินของตนเอง โค้งข้ามช่วยแยกเส้นในจุดตัด</p></header><div class="drawing">'+svg+'</div><footer>21 สัญลักษณ์ · 38 การเชื่อมต่อ · ไม่มีจุดต่อข้ามผังหรือการแยกกระบวนการเป็นส่วน ๆ</footer></main></body></html>\n'
    source.OUTPUT.write_text(html,encoding='utf-8')
    (HERE/'dfd-level0-unified-geometry.json').write_text(json.dumps({'nodes':BOUNDS,'positions':POSITIONS,'routes':routes},ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
    (ROOT/'docs/dfd-level0-design-notes.md').write_text('\n'.join([
        '# DFD Level 0 — ผังรวมทั้งระบบ','',
        'แผนภาพ: [dfd-level0-student-discipline.html](dfd-level0-student-discipline.html)','',
        '- 8 กระบวนการ, 5 หน่วยงานภายนอก, 8 คลังข้อมูล อย่างละหนึ่งตำแหน่ง รวม 21 สัญลักษณ์',
        '- คงการเชื่อมต่อครบ 38 รายการและป้ายข้อมูลตาม Mermaid รวมลูกศรอ่าน/เขียนสองหัว',
        '- เชื่อมทุกกระบวนการโดยตรง ไม่มี C1–C4 หรือกรอบแยกกระบวนการ',
        '- วางเส้นมุมฉากบนกริด 24px ไม่อนุญาตให้สองเส้นใช้ทางเดินเดียวกัน',
        f'- ทางเดินตัดกัน {sum(len(r["hops"]) for r in routes)} จุด ใช้โค้งข้ามรัศมี 8px เพื่อให้เส้นแยกกัน ไม่มีจุดตัดที่ไม่ได้ทำโค้งข้าม',
        '- ตรวจไม่ให้เส้นพาดหลังกล่องอื่น ป้ายชนกล่อง/ป้ายอื่น หรือป้ายบังเส้น',
        '- คงสไตล์วงกลมสีส้ม สี่เหลี่ยมขาว และคลังทรงกระบอกเทา',
        '- พรีวิวเบราว์เซอร์ยังไม่ได้ยืนยันเพราะเครื่องมือบล็อก file://; ใช้การตรวจรูปทรง พิกัดและขนาดข้อความแบบออฟไลน์',
        '', 'สร้างใหม่: `python docs/diagrams/build_dfd_level0.py`','']),encoding='utf-8')
    print(f'Created unified diagram: {source.OUTPUT}',flush=True)

if __name__=='__main__':
    build()
