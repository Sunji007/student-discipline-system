import json, re
from pathlib import Path
from PIL import Image, ImageFont

root = Path(__file__).resolve().parents[2]
tmp = root / 'tmp/research-slides'
slides = []
current = None
num = 0
GREEN='#143F38'; CREAM='#F8F6EE'; GOLD='#BD9C59'; INK=GREEN; MUTED='#62766F'

def rect(x,y,w,h,color):
    if color == GREEN: color = CREAM
    current['items'].append(dict(type='rect',x=x,y=y,w=w,h=h,color=color))

def line(x,y,x2,y2,color=GOLD,width=1):
    if color in ('#45675E','#34584F'): color='#D9DED6'
    current['items'].append(dict(type='line',x=x,y=y,w=x2-x,h=y2-y,color=color,weight=width))

def txt(s,x,y,size=28,color=INK,font='Thai',align='left'):
    s=re.sub(r' {2,}', ' ', s)
    if color in ('#D2DDD2','#C2D2C8','#B6C7BC'): color=MUTED
    if color == CREAM: color=GREEN
    bold = font == 'ThaiBold'
    family = 'Arial' if font == 'Latin' else 'Tahoma'
    ff = ('arial.ttf' if family=='Arial' else 'tahomabd.ttf' if bold else 'tahoma.ttf')
    f=ImageFont.truetype('C:/Windows/Fonts/'+ff,round(size))
    w=max(20,f.getlength(s)+18)
    if align=='left': w=min(w,1224-x)
    left=x if align=='left' else x-w/2 if align=='center' else x-w
    current['items'].append(dict(type='text',s=s,x=left,y=y,w=w,h=size*1.55,size=size,color=color,bold=bold,align=align,font=family))

def lines(ss,x,y,size=28,color=INK,font='Thai',leading=None):
    for j,s in enumerate(ss): txt(s,x,y+j*(leading or size*1.5),size,color,font)

def img(path,x,y,w,h,cover=False,top=False):
    path=Path(path)
    current['items'].append(dict(type='image',path=path.as_posix(),x=x,y=y,w=w,h=h,cover=cover,top=top))

def start(title=None,section='',source='',dark=False):
    global current,num
    num+=1;current={'number':num,'source':source,'title':title,'items':[]};slides.append(current)
    rect(0,0,1280,720,CREAM)
    txt('STUDENT DISCIPLINE / RESEARCH 2569',56,30,16,GOLD,'Latin')
    if section: txt(section,1224,30,17,GOLD,align='right')
    if title: txt(title,56,78,42,INK,'ThaiBold')
    line(56,661,1224,661,'#D9DED6',.8)
    if source: txt(source,56,678,13,MUTED)
    txt(f'{num:02d} / 18',1224,676,16,GOLD,'Latin',align='right')

def end(): pass

src=(tmp/'build_slides.py').read_text(encoding='utf-8-sig')
body=src[src.index('# 01 cover'):src.index('c.save()')]
exec(body,dict(ROOT=root,TMP=tmp,ASSET=root/'docs/presentation_assets',GREEN=GREEN,CREAM=CREAM,GOLD=GOLD,INK=INK,MUTED=MUTED,start=start,end=end,rect=rect,line=line,txt=txt,lines=lines,img=img))
(tmp/'native-layout.json').write_text(json.dumps(slides,ensure_ascii=False,indent=2),encoding='utf-8')
print(len(slides))
