import sys, math
from pathlib import Path
ROOT=Path(__file__).resolve().parents[2]
sys.path.insert(0,str(ROOT/'tmp/research-slides/lib'))
from reportlab.pdfgen import canvas
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.colors import HexColor
import pymupdf
pdfmetrics.registerFont(TTFont('Thai','C:/Windows/Fonts/LeelawUI.ttf'))
pdfmetrics.registerFont(TTFont('ThaiBold','C:/Windows/Fonts/LeelaUIb.ttf'))
pdfmetrics.registerFont(TTFont('Latin','C:/Users/บุสริน/.agents/skills/canvas-design/canvas-fonts/ArsenalSC-Regular.ttf'))
W,H=1280,720
GREEN='#143F38';CREAM='#F8F6EE';GOLD='#BD9C59';INK='#182F2A';MUTED='#62766F'
out=ROOT/'output/pdf/sdlc-page-8-redesign.pdf'
c=canvas.Canvas(str(out),pagesize=(W,H));c.setTitle('หน้า 8 - ขั้นตอนดำเนินงานตาม SDLC')
def text(s,x,y,size=24,color=INK,font='Thai',center=False):
    c.setFont(font,size);c.setFillColor(HexColor(color))
    (c.drawCentredString if center else c.drawString)(x,H-y-size*.85,s,shaping=True)
def line(x,y,xx,yy,color,width=1):
    c.setStrokeColor(HexColor(color));c.setLineWidth(width);c.line(x,H-y,xx,H-yy)
def circle(x,y,r,fill,stroke=None,width=1):
    c.setFillColor(HexColor(fill));c.setStrokeColor(HexColor(stroke or fill));c.setLineWidth(width);c.circle(x,H-y,r,fill=1,stroke=bool(stroke))
c.setFillColor(HexColor(CREAM));c.rect(0,0,W,H,fill=1,stroke=0)
text('STUDENT DISCIPLINE / RESEARCH 2569',56,30,16,GOLD,'Latin')
text('ขั้นตอนดำเนินงานตาม SDLC',56,75,42,GREEN,'ThaiBold')
text('7 ขั้นตอนเพื่อพัฒนาระบบวินัยและติดตามพฤติกรรมนักเรียน',57,133,23,MUTED)
line(56,176,1224,176,'#D9DED6')

# Clockwise cycle: seven numbered editable nodes, each with a short label.
cx,cy,r=298,405,192
c.setStrokeColor(HexColor('#D3C8A8'));c.setLineWidth(2);c.circle(cx,H-cy,r,fill=0,stroke=1)
names=['ปัญหา','ความเป็นไปได้','วิเคราะห์','ออกแบบ','พัฒนา / ทดสอบ','ติดตั้ง','บำรุงรักษา']
for i,name in enumerate(names):
    angle=-math.pi/2+i*2*math.pi/7
    x=cx+r*math.cos(angle);y=cy+r*math.sin(angle)
    circle(x,y,27,GREEN)
    text(f'{i+1:02}',x,y-16,32,CREAM,'Latin',True)
    lx=cx+(r-70)*math.cos(angle);ly=cy+(r-70)*math.sin(angle)
    if i==4:
        text('พัฒนา',lx,ly-18,18,GREEN,'ThaiBold',True)
        text('และทดสอบ',lx,ly+8,18,GREEN,'ThaiBold',True)
    else:
        text(name,lx,ly-8,18,GREEN,'ThaiBold',True)
    # The arrowheads follow the direction of the circle between nodes.
    a=angle+math.pi/7
    ax=cx+r*math.cos(a);ay=cy+r*math.sin(a)
    ux=-math.sin(a);uy=math.cos(a)
    px,py=-uy,ux
    p=c.beginPath();p.moveTo(ax+ux*7,H-(ay+uy*7))
    p.lineTo(ax-ux*7+px*5,H-(ay-uy*7+py*5))
    p.lineTo(ax-ux*7-px*5,H-(ay-uy*7-py*5));p.close()
    c.setFillColor(HexColor(GOLD));c.drawPath(p,stroke=0,fill=1)
text('SDLC',cx,358,53,GREEN,'Latin',True)
text('ระบบวินัยนักเรียน',cx,425,22,MUTED,center=True)
text('พัฒนาและปรับปรุงอย่างต่อเนื่อง',cx,622,20,MUTED,center=True)
line(557,205,557,629,'#D9DED6')

steps=[
('ค้นหาปัญหา','วิเคราะห์การบันทึกกระดาษและติดตามพฤติกรรม'),
('ศึกษาความเป็นไปได้','พิจารณาเครื่องมือ บุคลากร งบประมาณ และเวลา'),
('วิเคราะห์ระบบ','สัมภาษณ์ผู้ใช้ • กำหนดงานและสิทธิ์ 5 บทบาท'),
('ออกแบบระบบ','DFD • ER Diagram • ฐานข้อมูล • หน้าจอ'),
('พัฒนาและทดสอบ','PHP/MySQL • ทดสอบคะแนน สิทธิ์ และอุทธรณ์'),
('ติดตั้งระบบ','ทดลองใช้ด้วย XAMPP และแม่ข่ายมหาวิทยาลัย'),
('บำรุงรักษาระบบ','ใช้ผลประเมินและข้อเสนอแนะปรับปรุงระบบ'),
]
for i,(title,desc) in enumerate(steps):
    y=204+i*62
    text(f'{i+1:02}',597,y+1,28,GOLD,'Latin')
    text(title,657,y,24,GREEN,'ThaiBold')
    text(desc,657,y+31,21,MUTED)
line(56,661,1224,661,'#D9DED6',.8)
text('ที่มา: Pop del.pdf หน้า 24-25, 59-65, 102-104',56,678,13,MUTED)
text('08 / 18',1170,676,16,GOLD,'Latin')
c.showPage();c.save()
d=pymupdf.open(out);d[0].get_pixmap(matrix=pymupdf.Matrix(1.5,1.5),alpha=False).save(ROOT/'output/pdf/sdlc-page-8-redesign.png')
assert all(0<=b[0] and b[2]<=W and 0<=b[1] and b[3]<=H for b in d[0].get_text('blocks'))
print(out)
