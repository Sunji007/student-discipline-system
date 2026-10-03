import sys
from pathlib import Path
ROOT = Path(__file__).resolve().parents[2]
TMP = ROOT / 'tmp/research-slides'
sys.path.insert(0, str(TMP / 'lib'))
from reportlab.pdfgen import canvas
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib.colors import HexColor
from reportlab.lib.utils import ImageReader
from PIL import Image, ImageDraw
import pymupdf

OUT = ROOT / 'output/pdf/student-discipline-presentation.pdf'
ASSET = ROOT / 'docs/presentation_assets'
pdfmetrics.registerFont(TTFont('Thai', 'C:/Windows/Fonts/LeelawUI.ttf'))
pdfmetrics.registerFont(TTFont('ThaiBold', 'C:/Windows/Fonts/LeelaUIb.ttf'))
pdfmetrics.registerFont(TTFont('Latin', 'C:/Users/บุสริน/.agents/skills/canvas-design/canvas-fonts/ArsenalSC-Regular.ttf'))
W,H = 1280,720
GREEN='#143F38'; CREAM='#F8F6EE'; GOLD='#BD9C59'; INK='#182F2A'; MUTED='#62766F'
c = canvas.Canvas(str(OUT), pagesize=(W,H), pageCompression=1)
c.setTitle('ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน')
c.setAuthor('ตอริก ลือแมะ และ บุสริน มูซอ')
num=0
def rect(x,y,w,h,color):
    c.setFillColor(HexColor(color));c.rect(x,H-y-h,w,h,fill=1,stroke=0)
def line(x,y,x2,y2,color=GOLD,width=1):
    c.setStrokeColor(HexColor(color));c.setLineWidth(width);c.line(x,H-y,x2,H-y2)
def txt(s,x,y,size=28,color=INK,font='Thai',align='left'):
    c.setFillColor(HexColor(color));c.setFont(font,size)
    fn={'left':c.drawString,'center':c.drawCentredString,'right':c.drawRightString}[align]
    fn(x,H-y-size*0.85,s,shaping=True)
def lines(ss,x,y,size=28,color=INK,font='Thai',leading=None):
    for j,s in enumerate(ss):txt(s,x,y+j*(leading or size*1.5),size,color,font)
def img(path,x,y,w,h,cover=False,top=False):
    path=Path(path);im=Image.open(path);iw,ih=im.size
    scale=max(w/iw,h/ih) if cover else min(w/iw,h/ih)
    dw,dh=iw*scale,ih*scale
    dx=x+(w-dw)/2;dy=y if top and cover else y+(h-dh)/2
    c.saveState();p=c.beginPath();p.rect(x,H-y-h,w,h);c.clipPath(p,stroke=0,fill=0)
    c.drawImage(ImageReader(im),dx,H-dy-dh,dw,dh,mask='auto');c.restoreState()
def start(title=None,section='',source='',dark=False):
    global num
    num+=1;rect(0,0,W,H,GREEN if dark else CREAM)
    color=CREAM if dark else INK
    txt('STUDENT DISCIPLINE / RESEARCH 2569',56,30,16,GOLD,'Latin')
    if section:txt(section,1224,30,17,GOLD,align='right')
    if title:txt(title,56,78,42,color,'ThaiBold')
    line(56,661,1224,661, '#45675E' if dark else '#D9DED6',0.8)
    if source:txt(source,56,678,13,'#B6C7BC' if dark else MUTED)
    txt(f'{num:02d} / 18',1224,676,16,GOLD,'Latin',align='right')
def end():c.showPage()

# 01 cover
start(source='โครงงานทางเทคโนโลยีสารสนเทศ 2 | มหาวิทยาลัยราชภัฏยะลา',dark=True)
img(ROOT/'docs/manual_images/school_front.jpg',826,0,454,650,cover=True)
rect(0,0,826,660,GREEN)
txt('โครงงานวิจัย • ปีการศึกษา 2569',56,55,23,GOLD)
lines(['ระบบสารสนเทศ','การบริหารงานวินัย','และติดตามพฤติกรรมนักเรียน'],56,179,49,CREAM,'ThaiBold',75)
lines(['กรณีศึกษา: โรงเรียนศิริราษฎร์สามัคคี','จังหวัดปัตตานี'],60,431,28,'#D2DDD2',leading=45)
line(60,551,170,551,GOLD,3)
txt('ตอริก ลือแมะ  /  บุสริน มูซอ',60,576,25,CREAM)
end()

# 02 all four real portraits
start('ผู้วิจัยและอาจารย์ที่ปรึกษา','คณะผู้จัดทำ','ผู้วิจัย: PDF หน้า 205-206 | รูปที่ปรึกษา: YRU Profile')
txt('ผู้วิจัย',56,150,23,GREEN,'ThaiBold');txt('อาจารย์ที่ปรึกษา',674,150,23,GREEN,'ThaiBold')
people=[('portrait205.png','นายตอริก ลือแมะ','406665012'),('portrait206.png','นายบุสริน มูซอ','406665027'),('advisor0.jpg','อ.แพรวศรี เดิมราช','อาจารย์ที่ปรึกษา'),('advisor1.jpg','อ.รุสนี กาแมแล','อาจารย์ที่ปรึกษา')]
for i,(p,name,sub) in enumerate(people):
    x=[56,353,674,971][i]
    img(TMP/p,x,197,253,312,cover=True,top=True)
    txt(name,x,530,24,INK,'ThaiBold');txt(sub,x,575,20,MUTED)
line(639,185,639,610,'#D9DED6',1)
end()

# 03 problems
start('ความเป็นมาและปัญหา','บทนำ','ที่มา: Pop del.pdf หน้า 16-17, 59')
img(ROOT/'docs/manual_images/school_front.jpg',56,170,540,438,cover=True)
for j,(a,b) in enumerate([('ข้อมูลกระจัดกระจาย','จดบันทึกลงกระดาษ'),('สืบค้นและสรุปผลล่าช้า','รวบรวมข้อมูลจากหลายแฟ้ม'),('ติดตามกิจกรรมได้ไม่ทั่วถึง','ขาดหลักฐานที่ตรวจสอบได้')]):
    y=185+j*144;txt(f'0{j+1}',644,y,36,GOLD,'Latin');txt(a,724,y,31,INK,'ThaiBold');txt(b,724,y+56,25,MUTED)
end()

# 04 fishbone source image
start('แผนผังก้างปลา','วิเคราะห์ปัญหา','ที่มา: แผนภาพประกอบโครงงานระบบวินัยนักเรียน')
rect(40,176,1200,470,'#FFFFFF');img(ASSET/'fishbone.png',54,192,1172,430)
end()

# 05 research objectives
start('วัตถุประสงค์','การวิจัย','ที่มา: Pop del.pdf หน้า 17')
for j,(a,b) in enumerate([('วิเคราะห์ ออกแบบ และพัฒนาระบบ','บริหารงานวินัยและติดตามพฤติกรรมนักเรียน'),('ประเมินคุณภาพระบบ','โดยผู้เชี่ยวชาญ'),('ประเมินความพึงพอใจ','ของผู้ใช้งานระบบ')]):
    y=191+j*144;txt(f'0{j+1}',60,y,62,GOLD,'Latin');txt(a,213,y+3,36,INK,'ThaiBold');txt(b,216,y+67,27,MUTED)
    if j<2:line(213,y+119,1180,y+119,'#D9DED6')
end()

# 06 user roles
start('ขอบเขตผู้ใช้งาน','5 บทบาท','ที่มา: Pop del.pdf หน้า 18-19')
roles=[('ผู้ดูแลระบบ','ข้อมูลและสิทธิ์'),('ฝ่ายปกครอง','พฤติกรรมและรายงาน'),('ครูประจำชั้น','เช็กชื่อและบันทึก'),('นักเรียน','ประวัติและอุทธรณ์'),('ผู้ปกครอง','ติดตามบุตรหลาน')]
for i,(a,b) in enumerate(roles):
    y=167+i*89;txt(f'0{i+1}',70,y,36,GOLD,'Latin');txt(a,218,y+2,30,INK,'ThaiBold');txt(b,788,y+7,26,MUTED)
    if i<4:line(218,y+68,1180,y+68,'#D9DED6')
end()

# 07 authentic logos
start('เครื่องมือที่ใช้พัฒนา','ซอฟต์แวร์และภาษา','ที่มา: Pop del.pdf หน้า 4, 20-21 | โลโก้: ไฟล์ต้นฉบับในโครงงาน')
for i,(name,filename,role) in enumerate([('VS Code','vscode','เขียนโปรแกรม'),('MySQL','mysql','ฐานข้อมูล'),('PHP','php','ประมวลผลระบบ')]):
    x=225+i*414;img(ASSET/f'logos/{filename}.png',x-67,175,134,105);txt(name,x,298,28,INK,'ThaiBold','center');txt(role,x,342,22,MUTED,align='center')
line(100,398,1180,398,'#D9DED6')
for i,(name,filename) in enumerate([('HTML','html5'),('CSS','css'),('JavaScript','javascript'),('React','react')]):
    x=200+i*293;img(ASSET/f'logos/{filename}.png',x-48,438,96,96);txt(name,x,556,27,INK,'ThaiBold','center')
end()

# 08 typographic process
start('ขั้นตอนดำเนินงาน','SDLC','ที่มา: Pop del.pdf หน้า 24-25, 58',True)
steps=[('ศึกษาปัญหา','และความต้องการ'),('วิเคราะห์','และออกแบบ'),('พัฒนาระบบ','และฐานข้อมูล'),('ทดสอบ','และติดตั้ง'),('ประเมิน','และปรับปรุง')]
for i,(a,b) in enumerate(steps):
    x=56+i*244;txt(f'0{i+1}',x,223,78,GOLD,'Latin');txt(a,x,371,30,CREAM,'ThaiBold');txt(b,x,426,24,'#C2D2C8')
line(57,344,1208,344,'#6D887C',1)
txt('ใช้วงจรการพัฒนาระบบเป็นกรอบดำเนินงาน',56,559,25,'#C2D2C8')
end()

# 09 full DFD, no loss of labels
start('แผนภาพกระแสข้อมูล','DFD LEVEL 0','ที่มา: แผนภาพประกอบโครงงานระบบวินัยนักเรียน')
rect(28,142,1224,506,'#FFFFFF');img(ASSET/'dfd_level0.png',37,151,1206,488)
end()

def screenshot_slide(title,section,file,caption,source):
    start(title,section,source)
    img(TMP/file,56,166,1168,440)
    txt(caption,56,617,24,GREEN)
    end()

# 10 dashboard
screenshot_slide('ภาพรวมระบบ','ผลการพัฒนา','screen144_0.png','ดูคะแนน พฤติกรรม และสถิติในหน้าเดียว','ที่มา: Pop del.pdf หน้า 144 ภาพที่ 4.16')

# 11 tall forms
start('บันทึกพฤติกรรมนักเรียน','ผลการพัฒนา','ที่มา: Pop del.pdf หน้า 145 ภาพที่ 4.18-4.19')
txt('บันทึกพฤติกรรม',60,169,28,GREEN,'ThaiBold')
lines(['ด้านบวก / ด้านลบ','อ้างอิงเกณฑ์คะแนน','แนบหลักฐานประกอบ'],60,258,28,INK,leading=74)
img(TMP/'screen145_0.png',445,158,371,482)
img(TMP/'screen145_1.png',852,158,372,482)
end()

# 12 appeals
screenshot_slide('พิจารณาคำอุทธรณ์','ผลการพัฒนา','screen148_0.png','ยื่นคำร้อง • ตรวจสอบหลักฐาน • ติดตามสถานะ','ที่มา: Pop del.pdf หน้า 148 ภาพที่ 4.24')

# 13 attendance
screenshot_slide('เช็กชื่อเข้าแถว','ผลการพัฒนา','screen156_0.png','บันทึกการมาเรียน และตรวจสอบประวัติย้อนหลัง','ที่มา: Pop del.pdf หน้า 156 ภาพที่ 4.38')

# 14 prayer
screenshot_slide('ติดตามการละหมาด','ผลการพัฒนา','screen152_1.png','สรุปการเข้าร่วมกิจกรรม และตรวจสอบรายบุคคล','ที่มา: Pop del.pdf หน้า 152 ภาพที่ 4.33')

# 15 parents
screenshot_slide('ผู้ปกครองติดตามบุตรหลาน','ผลการพัฒนา','screen165_0.png','ดูประวัติพฤติกรรม และร่วมดูแลกับโรงเรียน','ที่มา: Pop del.pdf หน้า 165 ภาพที่ 4.55')

# 16 honest evaluation, no fabricated scores
start('การประเมินระบบ','กลุ่มตัวอย่าง','ที่มา: Pop del.pdf หน้า 58, 168-171')
txt('30',56,166,120,GREEN,'Latin');txt('คน',215,235,35,INK,'ThaiBold')
txt('เลือกแบบเจาะจง',60,340,27,MUTED)
for i,(n,name) in enumerate([('12','นักเรียน'),('12','ผู้ปกครอง'),('3','ครูประจำชั้น'),('3','ฝ่ายปกครอง')]):
    x=413+i*202;txt(n,x,180,69,GOLD,'Latin');txt(name,x,292,26,INK,'ThaiBold')
line(60,407,1220,407,'#D9DED6')
txt('คุณภาพระบบ',60,446,31,INK,'ThaiBold');txt('ประเมินโดยผู้เชี่ยวชาญ',60,499,25,MUTED)
txt('ความพึงพอใจ',674,446,31,INK,'ThaiBold');txt('ประเมินโดยผู้ใช้งาน',674,499,25,MUTED)
txt('รายงานแนบยังไม่ระบุค่าคะแนนผลประเมิน',60,594,25,GREEN)
end()

# 17 expected benefits
start('ประโยชน์ที่คาดว่าจะได้รับ','สรุป','ที่มา: Pop del.pdf หน้า 21',True)
for j,(a,b) in enumerate([('โรงเรียน','ข้อมูลวินัยเป็นระบบและตรวจสอบได้'),('ครู','ลดภาระเอกสารและติดตามได้ต่อเนื่อง'),('นักเรียนและผู้ปกครอง','รับรู้ข้อมูลและร่วมปรับพฤติกรรม')]):
    y=191+j*143;txt(f'0{j+1}',58,y,53,GOLD,'Latin');txt(a,218,y+3,34,CREAM,'ThaiBold');txt(b,220,y+64,28,'#C2D2C8')
end()

# 18 ending
start(source='ตอริก ลือแมะ  /  บุสริน มูซอ | สาขาเทคโนโลยีสารสนเทศ มหาวิทยาลัยราชภัฏยะลา',dark=True)
txt('ขอบคุณครับ',640,246,65,CREAM,'ThaiBold','center')
line(523,370,757,370,GOLD,3)
txt('ยินดีรับคำถามและข้อเสนอแนะ',640,414,33,'#C2D2C8',align='center')
end()
c.save()

doc=pymupdf.open(OUT)
for i,p in enumerate(doc):p.get_pixmap(matrix=pymupdf.Matrix(1,1),alpha=False).save(TMP/f'slide-{i+1:02}.png')
sheet=Image.new('RGB',(4*480,5*290),'#E4E7E0')
for i in range(len(doc)):
    im=Image.open(TMP/f'slide-{i+1:02}.png');im.thumbnail((470,265))
    x=(i%4)*480+5;y=(i//4)*290+5;sheet.paste(im,(x,y));ImageDraw.Draw(sheet).text((x+5,y+266),f'{i+1:02}',fill='#143F38')
sheet.save(TMP/'contact-sheet.png')
print(f'Created {len(doc)} slides: {OUT}')
