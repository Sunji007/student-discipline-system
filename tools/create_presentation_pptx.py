# -*- coding: utf-8 -*-
"""
Script: create_presentation_pptx.py
Generates an enhanced PowerPoint presentation (.pptx) strictly aligned with:
1. 'แนวทางในการทำสื่อประกอบการนำเสนอ' (7-10 minutes, 10-20 slides, 5 chapters).
2. 'docs/research_presentation_alignment.md' (Full research paper synthesis).
3. 'docs/dfd_level_1.md' (8 Processes, 5 Entities, 12 Data Stores).
4. Real, prominent system screenshots with 'ฟังก์ชันหลัก' and 'ช่วยแก้ปัญหาเดิมอย่างไร'.
"""

import os
import pptx
from pptx import Presentation
from pptx.util import Inches, Pt
from pptx.dml.color import RGBColor
from pptx.enum.text import PP_ALIGN
from pptx.enum.shapes import MSO_SHAPE

BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
DOCS_DIR = os.path.join(BASE_DIR, "docs")
IMAGES_DIR = os.path.join(DOCS_DIR, "manual_images")
OUTPUT_PPTX = os.path.join(DOCS_DIR, "Project_Presentation_Sirirat_Samakkhi.pptx")

# Color Palette (YRU Navy & IT Orange)
COLOR_NAVY = RGBColor(27, 54, 93)         # #1B365D Primary YRU
COLOR_ORANGE = RGBColor(232, 119, 34)     # #E87722 Accent IT Orange
COLOR_DARK = RGBColor(30, 41, 59)         # #1E293B Dark Slate text
COLOR_MUTED = RGBColor(100, 116, 139)     # #64748B Secondary text
COLOR_BG_LIGHT = RGBColor(248, 250, 252)   # #F8FAFC Card BG
COLOR_WHITE = RGBColor(255, 255, 255)
COLOR_CARD_BORDER = RGBColor(203, 213, 225) # #CBD5E1
COLOR_SUCCESS = RGBColor(22, 163, 74)     # #16A34A Green
COLOR_WARNING = RGBColor(217, 119, 6)     # #D97706 Amber
COLOR_DANGER = RGBColor(220, 38, 38)      # #DC2626 Red
COLOR_TEAL = RGBColor(13, 148, 136)       # #0D9488 Teal

FONT_FAMILY = "Leelawadee UI"

def set_slide_background(slide, color=COLOR_WHITE):
    background = slide.background
    fill = background.fill
    fill.solid()
    fill.fore_color.rgb = color

def add_header(slide, title_text, category_text, time_text=""):
    """Standardized top banner with category, title, and presentation timer."""
    # Top Accent Bar
    accent_bar = slide.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(13.333), Inches(0.12))
    accent_bar.fill.solid()
    accent_bar.fill.fore_color.rgb = COLOR_ORANGE
    accent_bar.line.fill.background()

    # Category Badge + Title
    tb = slide.shapes.add_textbox(Inches(0.8), Inches(0.32), Inches(9.6), Inches(1.15))
    tf = tb.text_frame
    tf.word_wrap = True
    tf.margin_left = tf.margin_top = tf.margin_right = tf.margin_bottom = 0
    
    # Subtitle / Category
    p_cat = tf.paragraphs[0]
    p_cat.text = category_text.upper()
    p_cat.font.name = FONT_FAMILY
    p_cat.font.size = Pt(11)
    p_cat.font.bold = True
    p_cat.font.color.rgb = COLOR_ORANGE
    p_cat.space_after = Pt(2)

    # Title
    p_title = tf.add_paragraph()
    p_title.text = title_text
    p_title.font.name = FONT_FAMILY
    p_title.font.size = Pt(22)
    p_title.font.bold = True
    p_title.font.color.rgb = COLOR_NAVY

    # Right indicator: Time Tag + School name
    tb_r = slide.shapes.add_textbox(Inches(10.5), Inches(0.32), Inches(2.1), Inches(1.15))
    tf_r = tb_r.text_frame
    tf_r.word_wrap = True
    tf_r.margin_left = tf_r.margin_top = tf_r.margin_right = tf_r.margin_bottom = 0
    p_time = tf_r.paragraphs[0]
    p_time.alignment = PP_ALIGN.RIGHT
    p_time.text = f"⏱ {time_text}" if time_text else ""
    p_time.font.name = FONT_FAMILY
    p_time.font.size = Pt(11.5)
    p_time.font.bold = True
    p_time.font.color.rgb = COLOR_ORANGE

    p_sch = tf_r.add_paragraph()
    p_sch.alignment = PP_ALIGN.RIGHT
    p_sch.text = "โรงเรียนศิริราษฎร์สามัคคี"
    p_sch.font.name = FONT_FAMILY
    p_sch.font.size = Pt(10)
    p_sch.font.color.rgb = COLOR_NAVY

def add_card(slide, left, top, width, height, bg_color=COLOR_BG_LIGHT, border_color=COLOR_CARD_BORDER):
    card = slide.shapes.add_shape(MSO_SHAPE.ROUNDED_RECTANGLE, left, top, width, height)
    card.fill.solid()
    card.fill.fore_color.rgb = bg_color
    if border_color:
        card.line.color.rgb = border_color
        card.line.width = Pt(1.2)
    else:
        card.line.fill.background()
    return card

def add_speaker_note(slide, note_text):
    notes_slide = slide.notes_slide
    tf = notes_slide.notes_text_frame
    tf.text = note_text

def build_presentation():
    prs = Presentation()
    prs.slide_width = Inches(13.333)
    prs.slide_height = Inches(7.5)
    blank_layout = prs.slide_layouts[6]

    # =========================================================
    # SLIDE 1: Title Slide (20-30 วินาที)
    # =========================================================
    s1 = prs.slides.add_slide(blank_layout)
    set_slide_background(s1, COLOR_WHITE)

    # Left Blue Hero Section
    bg_left = s1.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0), Inches(0), Inches(4.6), Inches(7.5))
    bg_left.fill.solid()
    bg_left.fill.fore_color.rgb = COLOR_NAVY
    bg_left.line.fill.background()

    school_img = os.path.join(IMAGES_DIR, "school_front.jpg")
    if os.path.exists(school_img):
        s1.shapes.add_picture(school_img, Inches(0.45), Inches(2.0), width=Inches(3.7))

    tb_l = s1.shapes.add_textbox(Inches(0.45), Inches(5.1), Inches(3.7), Inches(1.9))
    tf_l = tb_l.text_frame
    tf_l.word_wrap = True
    p = tf_l.paragraphs[0]
    p.text = "กรณีศึกษา\nโรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = COLOR_WHITE
    p.alignment = PP_ALIGN.CENTER

    p2 = tf_l.add_paragraph()
    p2.text = "Sirirat Samakkhi School, Pattani Province"
    p2.font.name = FONT_FAMILY
    p2.font.size = Pt(10.5)
    p2.font.color.rgb = RGBColor(190, 210, 240)
    p2.alignment = PP_ALIGN.CENTER

    # Right Content Area
    tb_title = s1.shapes.add_textbox(Inches(5.1), Inches(0.7), Inches(7.7), Inches(3.4))
    tf_t = tb_title.text_frame
    tf_t.word_wrap = True
    
    p = tf_t.paragraphs[0]
    p.text = "การนำเสนอโครงงานเทคโนโลยีสารสนเทศ (7–10 นาที)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(8)

    p = tf_t.add_paragraph()
    p.text = "ระบบสารสนเทศการบริหารงานวินัย\nและติดตามพฤติกรรมนักเรียน"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(28)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(4)

    p = tf_t.add_paragraph()
    p.text = "Student Discipline and Behavior Tracking System"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(14)
    p.font.color.rgb = COLOR_MUTED

    # Authors Card on Right Bottom
    add_card(s1, Inches(5.1), Inches(4.3), Inches(7.6), Inches(2.6), bg_color=COLOR_BG_LIGHT)
    tb_auth = s1.shapes.add_textbox(Inches(5.4), Inches(4.5), Inches(7.0), Inches(2.2))
    tf_a = tb_auth.text_frame
    tf_a.word_wrap = True

    p = tf_a.paragraphs[0]
    p.text = "คณะผู้จัดทำ (Researchers):"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY

    p = tf_a.add_paragraph()
    p.text = "• นายตอริก ลือแม          รหัสนักศึกษา 406665012\n• นายบุสริน มูซอ          รหัสนักศึกษา 406665027"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(13)
    p.font.bold = True
    p.font.color.rgb = COLOR_DARK
    p.space_after = Pt(6)

    p = tf_a.add_paragraph()
    p.text = "สาขาวิชาเทคโนโลยีสารสนเทศ คณะวิทยาศาสตร์เทคโนโลยีและการเกษตร\nมหาวิทยาลัยราชภัฏยะลา  |  ปีการศึกษา 2569"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(11)
    p.font.color.rgb = COLOR_MUTED

    uni_logo = os.path.join(IMAGES_DIR, "logo_university.png")
    dept_logo = os.path.join(IMAGES_DIR, "logo_department.png")
    if os.path.exists(uni_logo):
        s1.shapes.add_picture(uni_logo, Inches(10.7), Inches(0.6), height=Inches(1.05))
    if os.path.exists(dept_logo):
        s1.shapes.add_picture(dept_logo, Inches(11.8), Inches(0.6), height=Inches(1.05))

    add_speaker_note(s1, "⏱ เวลา: 20-30 วินาที\n"
                         "บทพูด: กราบเรียนท่านคณะกรรมการและอาจารย์ที่เคารพทุกท่านครับ พวกเราขอนำเสนอโครงงานระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษา โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี จัดทำโดย นายตอริก ลือแม และ นายบุสริน มูซอ นักศึกษาสาขาวิชาเทคโนโลยีสารสนเทศ มหาวิทยาลัยราชภัฏยะลา ครับ")

    # =========================================================
    # SLIDE 2: ที่มาและความสำคัญของปัญหา (บทที่ 1) (45-60 วินาที)
    # =========================================================
    s2 = prs.slides.add_slide(blank_layout)
    set_slide_background(s2, COLOR_WHITE)
    add_header(s2, "ที่มาและความสำคัญของปัญหา (Problem Background)", "บทที่ 1 : บทนำ", "45–60 วิ")

    add_card(s2, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), bg_color=RGBColor(254, 242, 242), border_color=RGBColor(254, 202, 202))
    tb_b = s2.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.4))
    tf_b = tb_b.text_frame
    tf_b.word_wrap = True
    p = tf_b.paragraphs[0]
    p.text = "❌ สภาพปัญหาของระบบเดิม (Manual Process)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(16)
    p.font.bold = True
    p.font.color.rgb = COLOR_DANGER
    p.space_after = Pt(12)

    problems = [
        ("การบันทึกด้วยเอกสารกระดาษ:", "เช็คชื่อเข้าแถวและละหมาดด้วยสมุดจด ข้อมูลกระจัดกระจาย ตกหล่นและสูญหายง่าย"),
        ("ค้นหาประวัติล่าช้าและซ้ำซ้อน:", "การตรวจสอบพฤติกรรมย้อนหลังต้องเปิดแฟ้มเอกสารทีละหน้า ไม่สามารถสรุปผลทันที"),
        ("คะแนนไม่สะท้อนสถานะปัจจุบัน:", "คะแนนคงเหลือไม่อัปเดตแบบ Real-time ฝ่ายปกครองตรวจจับเด็กกลุ่มเสี่ยงไม่ทันการณ์"),
        ("ผู้ปกครองขาดการรับรู้ทันท่วงที:", "มักจะทราบเมื่อสิ้นภาคเรียนหรือเมื่อมีหนังสือเชิญ ทำให้พลาดโอกาสตักเตือนบุตรหลาน")
    ]
    for title, desc in problems:
        p = tf_b.add_paragraph()
        p.text = f"• {title} "
        p.font.name = FONT_FAMILY
        p.font.size = Pt(12.5)
        p.font.bold = True
        p.font.color.rgb = COLOR_DARK
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(11.5)
        run.font.color.rgb = COLOR_MUTED
        p.space_after = Pt(8)

    add_card(s2, Inches(6.8), Inches(1.8), Inches(5.7), Inches(4.8), bg_color=RGBColor(240, 253, 244), border_color=RGBColor(187, 247, 208))
    tb_a = s2.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.4))
    tf_a = tb_a.text_frame
    tf_a.word_wrap = True
    p = tf_a.paragraphs[0]
    p.text = "✔ แนวทางแก้ไขด้วยระบบสารสนเทศ (Digital Solution)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(16)
    p.font.bold = True
    p.font.color.rgb = COLOR_SUCCESS
    p.space_after = Pt(12)

    solutions = [
        ("ระบบฐานข้อมูลรวมศูนย์ (Cloud Database):", "จัดเก็บข้อมูลนักเรียน คะแนนพฤติกรรม และเวลาเรียนไว้ที่เดียวอย่างปลอดภัย"),
        ("เช็คชื่อรวดเร็วด้วย QR Code / บาร์โค้ด:", "สแกนบัตรเช็คชื่อละหมาดและเข้าแถว บันทึกผลอัตโนมัติภายใน 1 วินาที/คน"),
        ("แดชบอร์ดนักเรียนกลุ่มเสี่ยง (Risk Monitor):", "คัดกรองนักเรียนที่คะแนนต่ำกว่า 50 อัตโนมัติ เพื่อเรียกพบ ทำทัณฑ์บน หรือตักเตือน"),
        ("พอร์ทัลผู้ปกครอง (Parent Portal):", "เข้าดูคะแนน เวลาเรียน สลับดูข้อมูลบุตรหลานได้หลายคนในบัญชีเดียว และแชทหาครู")
    ]
    for title, desc in solutions:
        p = tf_a.add_paragraph()
        p.text = f"• {title} "
        p.font.name = FONT_FAMILY
        p.font.size = Pt(12.5)
        p.font.bold = True
        p.font.color.rgb = COLOR_DARK
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(11.5)
        run.font.color.rgb = COLOR_MUTED
        p.space_after = Pt(8)

    add_speaker_note(s2, "⏱ เวลา: 45-60 วินาที\n"
                         "บทพูด: ที่มาและความสำคัญของปัญหาครับ โรงเรียนศิริราษฎร์สามัคคีเดิมใช้สมุดและกระดาษในการเช็คชื่อเข้าแถวหน้าเสาธง เช็คชื่อละหมาด และบันทึกคะแนนความประพฤติ ทำให้เกิดปัญหาสำคัญ 4 ประการ คือ ข้อมูลกระจัดกระจาย ค้นหาล่าช้า คะแนนไม่อัปเดตแบบเรียลไทม์ และผู้ปกครองไม่ทราบพฤติกรรมทันเวลา ทางคณะผู้จัดทำจึงพัฒนาระบบสารสนเทศบนเว็บ เพื่อรวมศูนย์ข้อมูล นำ QR Code มาช่วยสแกนเช็คชื่อละหมาด มีแดชบอร์ดติดตามนักเรียนกลุ่มเสี่ยง และเปิดให้ผู้ปกครองติดตามผลได้แบบเรียลไทม์ครับ")

    # =========================================================
    # SLIDE 3: วัตถุประสงค์ของโครงงาน (บทที่ 1) (30-45 วินาที)
    # =========================================================
    s3 = prs.slides.add_slide(blank_layout)
    set_slide_background(s3, COLOR_WHITE)
    add_header(s3, "วัตถุประสงค์ของโครงงาน (Project Objectives)", "บทที่ 1 : บทนำ", "30–45 วิ")

    obj_items = [
        ("01", "เพื่อออกแบบและพัฒนาระบบสารสนเทศ", "การบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษา โรงเรียนศิริราษฎร์สามัคคี ให้ทำงานได้อย่างถูกต้อง ครอบคลุม 5 กลุ่มผู้ใช้งานหลัก"),
        ("02", "เพื่อเพิ่มประสิทธิภาพการบริหารจัดการงานวินัย", "ช่วยให้ครูและฝ่ายปกครองสามารถเช็คชื่อเข้าแถว สแกนละหมาด บันทึกคะแนนพฤติกรรม และตรวจอุทธรณ์ได้อย่างรวดเร็ว ลดขั้นตอนเอกสาร"),
        ("03", "เพื่อประเมินประสิทธิภาพและความพึงพอใจ", "ทดสอบการทำงานของระบบตามเกณฑ์ Black Box Testing และประเมินความพึงพอใจจากผู้เชี่ยวชาญด้านไอทีและกลุ่มผู้ใช้งานจริงในโรงเรียน")
    ]

    for i, (num, title, desc) in enumerate(obj_items):
        top_pos = Inches(1.8 + i * 1.7)
        add_card(s3, Inches(0.8), top_pos, Inches(11.733), Inches(1.4), bg_color=COLOR_BG_LIGHT)
        
        num_box = s3.shapes.add_shape(MSO_SHAPE.RECTANGLE, Inches(0.8), top_pos, Inches(1.4), Inches(1.4))
        num_box.fill.solid()
        num_box.fill.fore_color.rgb = COLOR_NAVY if i != 1 else COLOR_ORANGE
        num_box.line.fill.background()
        tf_n = num_box.text_frame
        p_n = tf_n.paragraphs[0]
        p_n.text = num
        p_n.alignment = PP_ALIGN.CENTER
        p_n.font.name = FONT_FAMILY
        p_n.font.size = Pt(28)
        p_n.font.bold = True
        p_n.font.color.rgb = COLOR_WHITE

        tb_c = s3.shapes.add_textbox(Inches(2.4), top_pos + Inches(0.15), Inches(9.8), Inches(1.1))
        tf_c = tb_c.text_frame
        tf_c.word_wrap = True
        p_t = tf_c.paragraphs[0]
        p_t.text = title
        p_t.font.name = FONT_FAMILY
        p_t.font.size = Pt(16)
        p_t.font.bold = True
        p_t.font.color.rgb = COLOR_NAVY
        p_t.space_after = Pt(2)

        p_d = tf_c.add_paragraph()
        p_d.text = desc
        p_d.font.name = FONT_FAMILY
        p_d.font.size = Pt(12)
        p_d.font.color.rgb = COLOR_MUTED

    add_speaker_note(s3, "⏱ เวลา: 30-45 วินาที\n"
                         "บทพูด: วัตถุประสงค์ของโครงงานมี 3 ข้อหลักตามแบบเสนอโครงการครับ ข้อที่ 1 เพื่อออกแบบและพัฒนาระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน ข้อที่ 2 เพื่อเพิ่มประสิทธิภาพในการบันทึก ตัดคะแนน เช็คชื่อเข้าแถวและละหมาด และข้อที่ 3 เพื่อประเมินความถูกต้องของระบบด้วยวิธี Black Box Testing พร้อมประเมินคุณภาพจากผู้เชี่ยวชาญและผู้ใช้งานจริงครับ")

    # =========================================================
    # SLIDE 4: ขอบเขตของโครงงาน (บทที่ 1) (30-45 วินาที)
    # =========================================================
    s4 = prs.slides.add_slide(blank_layout)
    set_slide_background(s4, COLOR_WHITE)
    add_header(s4, "ขอบเขตของโครงงานและบทบาทผู้ใช้งาน (Project Scope)", "บทที่ 1 : บทนำ", "30–45 วิ")

    roles = [
        ("ผู้ดูแลระบบ\n(Admin)", "• จัดการผู้ใช้งานระบบ\n• จัดการข้อมูล นร./ครู\n• นำเข้าไฟล์ Excel/CSV\n• พิมพ์บัตรประจำตัว นร.", COLOR_NAVY),
        ("ฝ่ายปกครอง\n(Discipline)", "• กำหนดเกณฑ์กฎระเบียบ\n• อนุมัติตัด/เพิ่มคะแนน\n• มอนิเตอร์เด็กกลุ่มเสี่ยง\n• จัดการเรื่องอุทธรณ์/เบาะแส", RGBColor(14, 116, 144)),
        ("ครูที่ปรึกษา\n(Teacher)", "• เช็คชื่อเข้าเรียน/แถว\n• บันทึกพฤติกรรม+รูป\n• สแกน QR ละหมาด\n• แชทติดต่อผู้ปกครอง", COLOR_TEAL),
        ("นักเรียน\n(Student)", "• ดูคะแนนคงเหลือ (100)\n• เช็คประวัติเข้าแถว\n• บัตร QR ละหมาด\n• ยื่นอุทธรณ์/แจ้งเบาะแส", RGBColor(217, 119, 6)),
        ("ผู้ปกครอง\n(Parent)", "• แดชบอร์ดดูพฤติกรรม\n• ปุ่มสลับบุตรหลาน\n• ดูประวัติตัดคะแนน/เวลา\n• ส่งข้อความหาครูที่ปรึกษา", COLOR_ORANGE)
    ]

    card_w = Inches(2.2)
    gap = Inches(0.18)
    start_x = Inches(0.8)

    for i, (r_title, r_desc, r_color) in enumerate(roles):
        x = start_x + i * (card_w + gap)
        add_card(s4, x, Inches(1.8), card_w, Inches(4.8), bg_color=COLOR_BG_LIGHT)
        
        hb = s4.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, Inches(1.8), card_w, Inches(1.0))
        hb.fill.solid()
        hb.fill.fore_color.rgb = r_color
        hb.line.fill.background()
        tf_h = hb.text_frame
        p_h = tf_h.paragraphs[0]
        p_h.text = r_title
        p_h.font.name = FONT_FAMILY
        p_h.font.size = Pt(13)
        p_h.font.bold = True
        p_h.font.color.rgb = COLOR_WHITE
        p_h.alignment = PP_ALIGN.CENTER

        tb = s4.shapes.add_textbox(x + Inches(0.15), Inches(3.0), card_w - Inches(0.3), Inches(3.4))
        tf = tb.text_frame
        tf.word_wrap = True
        p_b = tf.paragraphs[0]
        p_b.text = r_desc
        p_b.font.name = FONT_FAMILY
        p_b.font.size = Pt(11.5)
        p_b.font.color.rgb = COLOR_DARK
        p_b.space_before = Pt(4)

    add_speaker_note(s4, "⏱ เวลา: 30-45 วินาที\n"
                         "บทพูด: ขอบเขตของโครงงานครอบคลุมผู้ใช้งาน 5 กลุ่มบทบาทในโรงเรียนศิริราษฎร์สามัคคีครับ กลุ่มที่ 1 แอดมิน ดูแลระบบและพิมพ์บัตร QR กลุ่มที่ 2 ฝ่ายปกครอง ตรวจอนุมัติตัดคะแนนและติดตามเด็กกลุ่มเสี่ยง กลุ่มที่ 3 ครูที่ปรึกษา เช็คชื่อเข้าแถวและสแกนละหมาด กลุ่มที่ 4 นักเรียน ดูคะแนนคงเหลือ ยื่นอุทธรณ์ และกลุ่มที่ 5 ผู้ปกครอง สลับดูข้อมูลบุตรหลานและติดต่อครูได้โดยตรงครับ")

    # =========================================================
    # SLIDE 5: แนวคิด ทฤษฎี และงานวิจัยที่เกี่ยวข้อง (บทที่ 2) (30-45 วินาที)
    # =========================================================
    s5 = prs.slides.add_slide(blank_layout)
    set_slide_background(s5, COLOR_WHITE)
    add_header(s5, "แนวคิด ทฤษฎี และงานวิจัยที่เกี่ยวข้อง", "บทที่ 2 : เอกสารและงานวิจัยที่เกี่ยวข้อง", "30–45 วิ")

    tech_items = [
        ("SDLC Waterfall Model", "วงจรการพัฒนาระบบ", "กำหนดขั้นตอนเป็นลำดับเชิงโครงสร้าง ตั้งแต่วางแผน วิเคราะห์ ออกแบบ พัฒนา ทดสอบ จนถึงติดตั้งใช้งาน"),
        ("Laravel Framework (MVC)", "สถาปัตยกรรมซอฟต์แวร์", "แยกส่วนข้อมูล (Model), แสดงผล (View) และควบคุม (Controller) ป้องกัน SQL Injection และ CSRF"),
        ("MySQL Database", "ระบบจัดการฐานข้อมูลเชิงสัมพันธ์", "จัดเก็บข้อมูลเชิงสัมพันธ์ 15 ตารางหลัก มีการทำ Indexing และ Foreign Key เพื่อความสมบูรณ์ของข้อมูล"),
        ("งานวิจัย: ระบบบริหารพฤติกรรมดิจิทัล", "การประยุกต์ใช้ในโครงงาน", "นำแนวคิดการจัดหมวดหมู่ข้อระเบียบ (บวก/ลบ) และระบบคัดกรองนักเรียนกลุ่มเสี่ยง (Risk Monitor) มาปรับใช้"),
        ("งานวิจัย: การเช็คชื่อด้วย QR Code", "การประยุกต์ใช้ในโครงงาน", "นำแนวคิดการสแกนรหัสภาพมาพัฒนาโมดูลสแกนละหมาดซุฮรี/อัศรี รวดเร็ว 1 วินาที/คน ป้องกันลงชื่อแทนกัน"),
        ("งานวิจัย: การสื่อสารรร.กับผู้ปกครอง", "การประยุกต์ใช้ในโครงงาน", "นำแนวคิดการมีส่วนร่วมของครอบครัวมาพัฒนาฟังก์ชันสลับดูข้อมูลบุตรหลานหลายคนและแชทกับครูที่ปรึกษา")
    ]

    for i, (title, sub, desc) in enumerate(tech_items):
        col = i % 2
        row = i // 2
        x = Inches(0.8 + col * 6.0)
        y = Inches(1.8 + row * 1.7)
        add_card(s5, x, y, Inches(5.7), Inches(1.5), bg_color=COLOR_BG_LIGHT)

        tb = s5.shapes.add_textbox(x + Inches(0.25), y + Inches(0.12), Inches(5.2), Inches(1.25))
        tf = tb.text_frame
        tf.word_wrap = True
        p = tf.paragraphs[0]
        p.text = f"{title} "
        p.font.name = FONT_FAMILY
        p.font.size = Pt(13.5)
        p.font.bold = True
        p.font.color.rgb = COLOR_NAVY
        
        run = p.add_run()
        run.text = f"({sub})"
        run.font.size = Pt(11)
        run.font.bold = False
        run.font.color.rgb = COLOR_ORANGE

        p_d = tf.add_paragraph()
        p_d.text = desc
        p_d.font.name = FONT_FAMILY
        p_d.font.size = Pt(11)
        p_d.font.color.rgb = COLOR_MUTED
        p_d.space_before = Pt(3)

    add_speaker_note(s5, "⏱ เวลา: 30-45 วินาที\n"
                         "บทพูด: แนวคิด ทฤษฎี และงานวิจัยที่เกี่ยวข้องครับ ด้านกระบวนการเราใช้ SDLC Waterfall ด้านระบบใช้ Laravel Framework สถาปัตยกรรม MVC และ MySQL 15 ตาราง นอกจากนี้ยังได้ศึกษางานวิจัยที่เกี่ยวข้อง 3 เรื่องหลัก คือ ระบบตัดคะแนนพฤติกรรมดิจิทัล, การเช็คชื่อด้วย QR Code และระบบการสื่อสารระหว่างโรงเรียนกับผู้ปกครอง ซึ่งนำมาประยุกต์ใช้เป็นหัวใจหลักของฟังก์ชันในระบบเราครับ")

    # =========================================================
    # SLIDE 6: วิธีดำเนินการพัฒนาโครงงาน / SDLC (บทที่ 3) (45-60 วินาที)
    # =========================================================
    s6 = prs.slides.add_slide(blank_layout)
    set_slide_background(s6, COLOR_WHITE)
    add_header(s6, "วิธีดำเนินการพัฒนาโครงงานตามวงจร SDLC", "บทที่ 3 : วิธีดำเนินการวิจัย", "45–60 วิ")

    sdlc_steps = [
        ("1. การวางแผน (Planning)", "• สำรวจสภาพแวดล้อม รร.ศิริราษฎร์สามัคคี\n• รวบรวมปัญหาการตัดคะแนนและเช็คชื่อ\n• กำหนดความเป็นไปได้และข้อเสนอโครงงาน"),
        ("2. การวิเคราะห์ (Analysis)", "• วิเคราะห์ความต้องการผู้ใช้ 5 กลุ่ม\n• ออกแบบแผนภาพบริบท (Context Diagram)\n• จัดทำผังการไหลของข้อมูล (DFD Level 1)"),
        ("3. การออกแบบ (Design)", "• ออกแบบฐานข้อมูล ERD (15 ตารางหลัก)\n• ออกแบบส่วนติดต่อผู้ใช้ (UI/UX Mockups)\n• ออกแบบสถาปัตยกรรมระบบ 3 ระดับ"),
        ("4. การพัฒนา (Development)", "• พัฒนาด้วย Laravel, Blade, Tailwind\n• เขียนระบบสิทธิ์ความปลอดภัย RBAC 5 ระดับ\n• พัฒนาระบบสแกน QR Code เช็คชื่อละหมาด"),
        ("5. การทดสอบ (Testing)", "• ทดสอบฟังก์ชันด้วย Black Box Testing\n• ทดสอบ 6 ด้าน รวม 15 กรณีทดสอบ\n• ปรับปรุงแก้ไขจุดบกพร่องของระบบ"),
        ("6. ติดตั้งและประเมิน (Deployment)", "• ติดตั้งบนเซิร์ฟเวอร์จริง (site.yru.ac.th)\n• ทดลองใช้งานจริงในโรงเรียน\n• ประเมินคุณภาพระบบและจัดทำคู่มือ")
    ]

    for i, (step_title, step_desc) in enumerate(sdlc_steps):
        col = i % 3
        row = i // 3
        x = Inches(0.8 + col * 4.0)
        y = Inches(1.8 + row * 2.5)
        add_card(s6, x, y, Inches(3.8), Inches(2.3), bg_color=COLOR_BG_LIGHT)

        tag = s6.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, y, Inches(3.8), Inches(0.55))
        tag.fill.solid()
        tag.fill.fore_color.rgb = COLOR_NAVY if i < 3 else COLOR_ORANGE
        tag.line.fill.background()
        tf_tag = tag.text_frame
        p_t = tf_tag.paragraphs[0]
        p_t.text = step_title
        p_t.font.name = FONT_FAMILY
        p_t.font.size = Pt(12.5)
        p_t.font.bold = True
        p_t.font.color.rgb = COLOR_WHITE
        p_t.alignment = PP_ALIGN.CENTER

        tb = s6.shapes.add_textbox(x + Inches(0.2), y + Inches(0.65), Inches(3.4), Inches(1.5))
        tf = tb.text_frame
        tf.word_wrap = True
        p_b = tf.paragraphs[0]
        p_b.text = step_desc
        p_b.font.name = FONT_FAMILY
        p_b.font.size = Pt(11)
        p_b.font.color.rgb = COLOR_DARK
        p_b.space_before = Pt(3)

    add_speaker_note(s6, "⏱ เวลา: 45-60 วินาที\n"
                         "บทพูด: วิธีดำเนินการพัฒนาแบ่งเป็น 6 ขั้นตอนตามหลัก SDLC ครับ ขั้นแรกวางแผนสำรวจปัญหาในโรงเรียน ขั้นที่สองวิเคราะห์ความต้องการผู้ใช้และทำ DFD ขั้นที่สามออกแบบ ER-Diagram 15 ตาราง ขั้นที่สี่เขียนโปรแกรมระบบงานด้วย Laravel และ Blade ขั้นที่ห้าทดสอบการทำงานด้วยวิธี Black Box 15 ข้อ และขั้นที่หกนำขึ้นติดตั้งบนเซิร์ฟเวอร์จริงของมหาวิทยาลัยราชภัฏยะลา พร้อมประเมินผลการใช้งานครับ")

    # =========================================================
    # SLIDE 7: ผลการวิเคราะห์และออกแบบระบบ (บทที่ 4) (45-60 วินาที)
    # =========================================================
    s7 = prs.slides.add_slide(blank_layout)
    set_slide_background(s7, COLOR_WHITE)
    add_header(s7, "ผลการวิเคราะห์และออกแบบระบบ (DFD & Architecture)", "บทที่ 4 : ผลการศึกษา", "45–60 วิ")

    add_card(s7, Inches(0.8), Inches(1.8), Inches(5.6), Inches(4.8), bg_color=COLOR_BG_LIGHT)
    tb_arch = s7.shapes.add_textbox(Inches(1.1), Inches(2.0), Inches(5.0), Inches(4.4))
    tf_arch = tb_arch.text_frame
    tf_arch.word_wrap = True
    p = tf_arch.paragraphs[0]
    p.text = "🏛 สถาปัตยกรรมและฐานข้อมูล (Architecture)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(15.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(10)

    arch_bullets = [
        ("สถาปัตยกรรม 3 ระดับ (3-Tier Web Architecture):", "Presentation Layer (Blade/Tailwind) -> Application Layer (Laravel MVC) -> Data Layer (MySQL)"),
        ("โครงสร้างฐานข้อมูลเชิงสัมพันธ์ 15 ตาราง:", "เช่น users, students, behavior_records, attendances, prayer_records, appeals, informant_reports, messages ฯลฯ"),
        ("ระบบจัดการสิทธิ์ Role-Based Access (RBAC):", "แบ่งระดับสิทธิ์ 5 กลุ่มอย่างเด็ดขาด ป้องกันการเข้าถึงข้อมูลข้ามบทบาท"),
        ("ความปลอดภัยระดับมาตรฐาน:", "เข้ารหัสรหัสผ่าน Bcrypt และป้องกัน SQL Injection อัตโนมัติด้วย Eloquent ORM")
    ]
    for title, desc in arch_bullets:
        p = tf_arch.add_paragraph()
        p.text = f"• {title} "
        p.font.name = FONT_FAMILY
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = COLOR_DARK
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(11)
        run.font.color.rgb = COLOR_MUTED
        p.space_after = Pt(6)

    add_card(s7, Inches(6.8), Inches(1.8), Inches(5.7), Inches(4.8), bg_color=COLOR_BG_LIGHT)
    tb_dfd = s7.shapes.add_textbox(Inches(7.1), Inches(2.0), Inches(5.1), Inches(4.4))
    tf_dfd = tb_dfd.text_frame
    tf_dfd.word_wrap = True
    p = tf_dfd.paragraphs[0]
    p.text = "🔄 ผังกระแสข้อมูล DFD Level 1 (8 กระบวนการหลัก)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(15.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(10)

    dfd_steps = [
        ("1.0 จัดการข้อมูลผู้ใช้และข้อมูลหลัก (D1-D4):", "จัดการบัญชีผู้ใช้ นร. ครู และผู้ปกครอง"),
        ("2.0 เช็กชื่อการเข้าแถวหน้าเสาธง (D5):", "บันทึกเวลาเรียน มา สาย ขาด ลา ประจำวัน"),
        ("3.0 จัดการคะแนนความประพฤติ (D6-D7):", "กำหนดเกณฑ์วินัย บันทึกตัด/เพิ่มคะแนน"),
        ("4.0 แจ้งและตรวจเบาะแสการทำผิด (D8):", "ตู้แดงออนไลน์ รับแจ้งพฤติกรรมไม่เหมาะสม"),
        ("5.0 ยื่นและพิจารณาคำร้องอุทธรณ์ (D9):", "ระบบขอคืนคะแนนเมื่อมีหลักฐาน/กิจกรรม"),
        ("6.0 บันทึกและติดตามผลการละหมาด (D10):", "สแกน QR Code ละหมาดซุฮรีและอัศรี"),
        ("7.0 ติดต่อสื่อสารและส่งข้อความ (D11):", "กล่องแชทสื่อสารระหว่างครูกับผู้ปกครอง"),
        ("8.0 รายงาน สถิติ และแดชบอร์ด:", "สรุปผลพฤติกรรมและส่งออก PDF/Excel")
    ]
    for title, desc in dfd_steps:
        p = tf_dfd.add_paragraph()
        p.text = f"{title} "
        p.font.name = FONT_FAMILY
        p.font.size = Pt(11.5)
        p.font.bold = True
        p.font.color.rgb = COLOR_DARK
        run = p.add_run()
        run.text = desc
        run.font.bold = False
        run.font.size = Pt(10.5)
        run.font.color.rgb = COLOR_MUTED
        p.space_after = Pt(4)

    add_speaker_note(s7, "⏱ เวลา: 45-60 วินาที\n"
                         "บทพูด: ผลการวิเคราะห์และออกแบบระบบครับ สถาปัตยกรรมทำงานแบบ 3-Tier บน Laravel MVC ฐานข้อมูลมี 15 ตารางหลัก มีการวิเคราะห์ DFD Level 1 ครอบคลุม 8 กระบวนการหลัก ตั้งแต่จัดการข้อมูลผู้ใช้ เช็คชื่อเข้าแถว ตัดคะแนน รับแจ้งเบาะแส ตรวจอุทธรณ์ สแกนละหมาด แชทติดต่อผู้ปกครอง และออกรายงานสรุปสถิติครับ")

    # =========================================================
    # SLIDE 8.1: ผลการพัฒนาระบบจริง (1/2) — ฝ่ายปกครอง & ครูผู้สอน ⭐
    # =========================================================
    s8_1 = prs.slides.add_slide(blank_layout)
    set_slide_background(s8_1, COLOR_WHITE)
    add_header(s8_1, "ผลการพัฒนาระบบจริง (1/3) : ฝ่ายปกครอง & ครูผู้สอน ⭐", "บทที่ 4 : ผลการศึกษา", "1–1.5 นาที")

    # Left Card: Risk Students Monitor
    add_card(s8_1, Inches(0.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_risk = os.path.join(IMAGES_DIR, "fig_13.png")
    if os.path.exists(img_risk):
        s8_1.shapes.add_picture(img_risk, Inches(0.98), Inches(1.85), width=Inches(5.37))

    tb_r = s8_1.shapes.add_textbox(Inches(0.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_r = tb_r.text_frame
    tf_r.word_wrap = True
    p = tf_r.paragraphs[0]
    p.text = "🎯 ระบบเฝ้าระวังนักเรียนกลุ่มเสี่ยง (Risk Students Monitor)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(2)
    p = tf_r.add_paragraph()
    p.text = "• ระบบทำอะไรได้: คัดกรองนักเรียนที่คะแนนต่ำกว่า 50 อัตโนมัติ พร้อมแจ้งเตือนสถานะความเสี่ยง\n• แก้ปัญหาเดิม: เดิมไม่ทราบว่าใครคะแนนวิกฤตจนสิ้นเทอม ระบบนี้ตรวจจับแบบเรียลไทม์เพื่อเรียกตักเตือนทันที"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    # Right Card: Prayer Scanner
    add_card(s8_1, Inches(6.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_pray = os.path.join(IMAGES_DIR, "fig_23.png")
    if os.path.exists(img_pray):
        s8_1.shapes.add_picture(img_pray, Inches(6.98), Inches(1.85), width=Inches(5.37))

    tb_p = s8_1.shapes.add_textbox(Inches(6.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_p = tb_p.text_frame
    tf_p.word_wrap = True
    p = tf_p.paragraphs[0]
    p.text = "🎯 เครื่องสแกน QR Code เช็คชื่อละหมาดและเข้าแถว"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(2)
    p = tf_p.add_paragraph()
    p.text = "• ระบบทำอะไรได้: ครูใช้กล้องมือถือหรือเครื่องยิงบาร์โค้ด สแกนบัตร นร. เช็คชื่อซุฮรี/อัศรี รวดเร็ว 1 วิ/คน\n• แก้ปัญหาเดิม: ขานชื่อด้วยกระดาษล่าช้าและลงชื่อแทนกัน ระบบนี้ยืนยันตัวตนด้วย QR พร้อมเสียงปี๊บตอบรับ"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    add_speaker_note(s8_1, "⏱ เวลา: 1-1.5 นาที (สำคัญที่สุด ⭐)\n"
                           "บทพูด: ผลการพัฒนาระบบจริงส่วนแรกครับ ภาพด้านซ้ายคือหน้ารายชื่อนักเรียนกลุ่มเสี่ยงสำหรับฝ่ายปกครอง ระบบจะคัดกรองนักเรียนที่คะแนนต่ำกว่า 50 ทันที เพื่อให้เรียกพบตักเตือนก่อนถูกตัดสิทธิ์สอบ แก้ปัญหาเดิมที่ไม่ทราบข้อมูลจนสิ้นเทอม ส่วนภาพด้านขวาคือระบบสแกน QR Code เช็คชื่อละหมาด ครูสามารถใช้สมาร์ตโฟนสแกนบัตรนักเรียนเพื่อเช็คชื่อละหมาดและเข้าแถวได้รวดเร็วเพียง 1 วินาทีต่อคน พร้อมเสียงปี๊บตอบรับ แก้ปัญหาการจดกระดาษล่าช้าได้สมบูรณ์ครับ")

    # =========================================================
    # SLIDE 8.2: ผลการพัฒนาระบบจริง (2/3) — นักเรียน & ผู้ปกครอง ⭐
    # =========================================================
    s8_2 = prs.slides.add_slide(blank_layout)
    set_slide_background(s8_2, COLOR_WHITE)
    add_header(s8_2, "ผลการพัฒนาระบบจริง (2/3) : นักเรียน & ผู้ปกครอง ⭐", "บทที่ 4 : ผลการศึกษา", "1–1.5 นาที")

    # Left Card: Student Dashboard
    add_card(s8_2, Inches(0.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_stu = os.path.join(IMAGES_DIR, "fig_25.png")
    if os.path.exists(img_stu):
        s8_2.shapes.add_picture(img_stu, Inches(0.98), Inches(1.85), width=Inches(5.37))

    tb_s = s8_2.shapes.add_textbox(Inches(0.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_s = tb_s.text_frame
    tf_s.word_wrap = True
    p = tf_s.paragraphs[0]
    p.text = "🎯 แดชบอร์ดนักเรียน (Student Dashboard & ID Card)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(2)
    p = tf_s.add_paragraph()
    p.text = "• ระบบทำอะไรได้: ดูคะแนนคงเหลือ (เริ่มต้น 100), ประวัติเข้าแถว, บัตร QR ละหมาด และยื่นคำร้องอุทธรณ์\n• แก้ปัญหาเดิม: นักเรียนไม่ทราบคะแนนตนเองและไม่มีช่องทางชี้แจงความบริสุทธิ์ ระบบนี้เพิ่มความโปร่งใส"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    # Right Card: Parent Dashboard with Switch Child Button
    add_card(s8_2, Inches(6.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_par = os.path.join(IMAGES_DIR, "fig_30.png")
    if os.path.exists(img_par):
        s8_2.shapes.add_picture(img_par, Inches(6.98), Inches(1.85), width=Inches(5.37))

    tb_pa = s8_2.shapes.add_textbox(Inches(6.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_pa = tb_pa.text_frame
    tf_pa.word_wrap = True
    p = tf_pa.paragraphs[0]
    p.text = "🎯 แดชบอร์ดผู้ปกครอง พร้อมปุ่ม [สลับดูบุตรหลาน]"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(2)
    p = tf_pa.add_paragraph()
    p.text = "• ระบบทำอะไรได้: มีปุ่มสลับดูบุตรหลานแต่ละคนในบัญชีเดียว ตรวจดูคะแนน เวลาเรียน และแชทหาครู\n• แก้ปัญหาเดิม: ผู้ปกครองที่มีลูกหลายคนไม่ต้องสลับหลายบัญชี ได้รับทราบพฤติกรรมทันทีจากที่บ้าน"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    add_speaker_note(s8_2, "⏱ เวลา: 1-1.5 นาที (สำคัญที่สุด ⭐)\n"
                           "บทพูด: ผลการพัฒนาระบบจริงส่วนที่สองครับ ภาพด้านซ้ายคือหน้าแดชบอร์ดนักเรียน แสดงคะแนนความประพฤติคงเหลือ สถิติการมาแถว มีระบบเปิดบัตร QR Code ประจำตัว และปุ่มยื่นอุทธรณ์คะแนน ส่วนภาพด้านขวาคือหน้าแดชบอร์ดผู้ปกครอง ซึ่งมีฟังก์ชันเด่นคือปุ่ม 'สลับดูบุตรหลาน' ทำให้ผู้ปกครองที่มีลูกเรียนอยู่หลายคน สามารถกดสลับดูประวัติคะแนนและเวลาเรียนของลูกแต่ละคนได้ในบัญชีเดียว พร้อมระบบส่งข้อความแชทตรงถึงครูที่ปรึกษาครับ")

    # =========================================================
    # SLIDE 8.3: ผลการพัฒนาระบบจริง (3/3) — การตรวจสอบและรายงานสรุป ⭐
    # =========================================================
    s8_3 = prs.slides.add_slide(blank_layout)
    set_slide_background(s8_3, COLOR_WHITE)
    add_header(s8_3, "ผลการพัฒนาระบบจริง (3/3) : การอนุมัติและรายงานสรุป ⭐", "บทที่ 4 : ผลการศึกษา", "45–60 วิ")

    # Left Card: Approve Records
    add_card(s8_3, Inches(0.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_app = os.path.join(IMAGES_DIR, "fig_16.png")
    if os.path.exists(img_app):
        s8_3.shapes.add_picture(img_app, Inches(0.98), Inches(1.85), width=Inches(5.37))

    tb_ap = s8_3.shapes.add_textbox(Inches(0.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_ap = tb_ap.text_frame
    tf_ap.word_wrap = True
    p = tf_ap.paragraphs[0]
    p.text = "🎯 ระบบตรวจสอบและอนุมัติการตัดคะแนนพฤติกรรม"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(2)
    p = tf_ap.add_paragraph()
    p.text = "• ระบบทำอะไรได้: ครูส่งเรื่องพร้อมรูปหลักฐาน ฝ่ายปกครองตรวจความถูกต้องก่อนกด [อนุมัติ] หรือ [ปฏิเสธ]\n• แก้ปัญหาเดิม: ป้องกันการตัดคะแนนผิดคน หรือการกลั่นแกล้ง ทุกการตัดคะแนนมีรูปหลักฐานยืนยันชัดเจน"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    # Right Card: Behavior Reports & Export
    add_card(s8_3, Inches(6.8), Inches(1.72), Inches(5.73), Inches(5.38), bg_color=COLOR_BG_LIGHT)
    img_rep = os.path.join(IMAGES_DIR, "fig_19.png")
    if os.path.exists(img_rep):
        s8_3.shapes.add_picture(img_rep, Inches(6.98), Inches(1.85), width=Inches(5.37))

    tb_rp = s8_3.shapes.add_textbox(Inches(6.98), Inches(5.38), Inches(5.37), Inches(1.65))
    tf_rp = tb_rp.text_frame
    tf_rp.word_wrap = True
    p = tf_rp.paragraphs[0]
    p.text = "🎯 หน้ารายงานสรุปพฤติกรรมและการส่งออกข้อมูล"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12.5)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(2)
    p = tf_rp.add_paragraph()
    p.text = "• ระบบทำอะไรได้: ประมวลผลสถิติพฤติกรรมรายชั้นเรียน/ห้องเรียน ส่งออกเป็นไฟล์ PDF และ Excel ได้ทันที\n• แก้ปัญหาเดิม: เดิมต้องใช้เวลาหลายวันรวมคะแนนจากสมุด ระบบนี้สรุปผลคะแนนเฉลี่ยอัตโนมัติในคลิกเดียว"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_DARK

    add_speaker_note(s8_3, "⏱ เวลา: 45-60 วินาที\n"
                           "บทพูด: ผลการพัฒนาระบบจริงส่วนที่สามครับ ด้านซ้ายคือระบบอนุมัติการตัดคะแนนพฤติกรรม เมื่อครูพบนักเรียนกระทำผิดจะส่งเรื่องพร้อมรูปหลักฐานเข้ามา ฝ่ายปกครองสามารถตรวจสอบความถูกต้องก่อนกดอนุมัติ ป้องกันการตัดคะแนนผิดพลาด และด้านขวาคือหน้ารายงานสรุปพฤติกรรม สามารถกรองข้อมูลตามช่วงวันที่ ระดับชั้น และสั่งพิมพ์เป็น PDF หรือส่งออกเป็นไฟล์ Excel เพื่อรายงานผู้อำนวยการโรงเรียนได้ทันทีครับ")

    # =========================================================
    # SLIDE 9: ผลการทดสอบระบบ (Black Box Testing) (บทที่ 4) (45-60 วินาที)
    # =========================================================
    s9 = prs.slides.add_slide(blank_layout)
    set_slide_background(s9, COLOR_WHITE)
    add_header(s9, "ผลการทดสอบระบบ (Black Box Testing)", "บทที่ 4 : ผลการศึกษา", "45–60 วิ")

    table_shape = s9.shapes.add_table(8, 4, Inches(0.8), Inches(1.8), Inches(7.8), Inches(4.8))
    table = table_shape.table
    table.columns[0].width = Inches(3.6)
    table.columns[1].width = Inches(1.4)
    table.columns[2].width = Inches(1.4)
    table.columns[3].width = Inches(1.4)

    headers = ["ฟังก์ชันทดสอบ (Test Scope)", "จำนวนข้อ", "ร้อยละ", "ผลการทดสอบ"]
    for j, h in enumerate(headers):
        cell = table.cell(0, j)
        cell.fill.solid()
        cell.fill.fore_color.rgb = COLOR_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.name = FONT_FAMILY
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = COLOR_WHITE
        p.alignment = PP_ALIGN.CENTER if j > 0 else PP_ALIGN.LEFT

    rows_data = [
        ("1. การเข้าสู่ระบบ (Login)", "3 ข้อ", "100.00%", "ผ่าน"),
        ("2. การจัดการข้อมูล (Data Management)", "2 ข้อ", "50.00%", "ไม่ผ่าน 1 ข้อ*"),
        ("3. การประมวลผลและการเชื่อมโยงข้อมูล", "3 ข้อ", "100.00%", "ผ่าน"),
        ("4. การค้นหาและการรายงาน (Search & Report)", "3 ข้อ", "66.67%", "ไม่ผ่าน 1 ข้อ*"),
        ("5. การกำหนดสิทธิ์ผู้ใช้และความปลอดภัย", "2 ข้อ", "100.00%", "ผ่าน"),
        ("6. การออกจากระบบ (Logout)", "2 ข้อ", "100.00%", "ผ่าน"),
        ("ผลรวมภาพรวม (Overall Summary)", "15 ข้อ", "86.67%", "ผ่านเกณฑ์ ✅")
    ]

    for i, row in enumerate(rows_data):
        is_total = (i == len(rows_data) - 1)
        for j, val in enumerate(row):
            cell = table.cell(i + 1, j)
            cell.fill.solid()
            cell.fill.fore_color.rgb = RGBColor(241, 245, 249) if not is_total else RGBColor(220, 252, 231)
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.name = FONT_FAMILY
            p.font.size = Pt(11.5)
            p.font.bold = is_total or (j == 0)
            if is_total:
                p.font.color.rgb = COLOR_SUCCESS if j == 3 else COLOR_NAVY
            else:
                p.font.color.rgb = COLOR_DANGER if "ไม่ผ่าน" in val else COLOR_DARK
            p.alignment = PP_ALIGN.CENTER if j > 0 else PP_ALIGN.LEFT

    add_card(s9, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), bg_color=COLOR_BG_LIGHT)
    tb_calc = s9.shapes.add_textbox(Inches(9.1), Inches(2.0), Inches(3.2), Inches(4.4))
    tf_c = tb_calc.text_frame
    tf_c.word_wrap = True

    p = tf_c.paragraphs[0]
    p.text = "📊 การคำนวณผลการทดสอบ"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(10)

    p = tf_c.add_paragraph()
    p.text = "สูตรหาร้อยละผลการทดสอบ:\n= (กรณีที่ผ่าน ÷ ทั้งหมด) × 100\n= (13 ÷ 15) × 100\n= 86.67%"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.space_after = Pt(12)

    p = tf_c.add_paragraph()
    p.text = "เกณฑ์การผ่านภาพรวม:\nกำหนดเกณฑ์ผ่านไม่น้อยกว่า 85.00%\n\nสรุปผล: ระบบได้ร้อยละ 86.67% ซึ่ง 'ผ่านเกณฑ์มาตรฐาน'\n\n*หมายเหตุ: ข้อที่ไม่ผ่านในรอบแรกได้รับการแก้ไขปรับปรุงสมบูรณ์แล้วในการทดสอบรอบถัดไป"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(11)
    p.font.color.rgb = COLOR_MUTED

    add_speaker_note(s9, "⏱ เวลา: 45-60 วินาที\n"
                         "บทพูด: ผลการทดสอบการทำงานของระบบด้วยเทคนิค Black Box Testing ตามแบบฟอร์ม P08-1 จำนวน 6 ด้าน 15 กรณีทดสอบครับ มีกรณีทดสอบที่ผ่าน 13 ข้อ คิดเป็นร้อยละ 86.67 ซึ่งสูงกว่าเกณฑ์ขั้นต่ำที่กำหนดไว้คือร้อยละ 85 จึงสรุปได้ว่าระบบผ่านเกณฑ์การทดสอบการทำงานจริงครับ โดยข้อที่พบจุดบกพร่องในตอนแรกทางคณะผู้จัดทำได้แก้ไขปรับปรุงจนทำงานได้ถูกต้องเรียบร้อยแล้วครับ")

    # =========================================================
    # SLIDE 10: ผลการประเมินระบบ ⭐ (บทที่ 4) (45-60 วินาที)
    # =========================================================
    s10 = prs.slides.add_slide(blank_layout)
    set_slide_background(s10, COLOR_WHITE)
    add_header(s10, "ผลการประเมินคุณภาพระบบโดยผู้เชี่ยวชาญ ⭐", "บทที่ 4 : ผลการศึกษา", "45–60 วิ")

    t10_shape = s10.shapes.add_table(6, 4, Inches(0.8), Inches(1.8), Inches(7.8), Inches(3.6))
    t10 = t10_shape.table
    t10.columns[0].width = Inches(3.6)
    t10.columns[1].width = Inches(1.4)
    t10.columns[2].width = Inches(1.4)
    t10.columns[3].width = Inches(1.4)

    eval_headers = ["รายการประเมิน (Evaluation Items)", "ค่าเฉลี่ย (x̄)", "S.D.", "ระดับคุณภาพ"]
    for j, h in enumerate(eval_headers):
        cell = t10.cell(0, j)
        cell.fill.solid()
        cell.fill.fore_color.rgb = COLOR_NAVY
        p = cell.text_frame.paragraphs[0]
        p.text = h
        p.font.name = FONT_FAMILY
        p.font.size = Pt(12)
        p.font.bold = True
        p.font.color.rgb = COLOR_WHITE
        p.alignment = PP_ALIGN.CENTER if j > 0 else PP_ALIGN.LEFT

    eval_rows = [
        ("1. ด้านความถูกต้องของระบบ (Functionality)", "4.52", "0.51", "มากที่สุด ⭐"),
        ("2. ด้านความปลอดภัยของข้อมูล (Security)", "4.45", "0.52", "มาก"),
        ("3. ด้านการนำไปใช้ประโยชน์ (Usability)", "4.40", "0.50", "มาก"),
        ("4. ด้านการออกแบบส่วนติดต่อผู้ใช้ (UI/UX)", "4.38", "0.54", "มาก"),
        ("รวมเฉลี่ยทุกด้าน (Overall Mean)", "4.44", "0.52", "ระดับ มาก")
    ]

    for i, row in enumerate(eval_rows):
        is_total = (i == len(eval_rows) - 1)
        for j, val in enumerate(row):
            cell = t10.cell(i + 1, j)
            cell.fill.solid()
            cell.fill.fore_color.rgb = RGBColor(241, 245, 249) if not is_total else RGBColor(254, 243, 199)
            p = cell.text_frame.paragraphs[0]
            p.text = val
            p.font.name = FONT_FAMILY
            p.font.size = Pt(12)
            p.font.bold = is_total or (j == 0)
            if is_total:
                p.font.color.rgb = COLOR_ORANGE if j >= 1 else COLOR_NAVY
            else:
                p.font.color.rgb = COLOR_SUCCESS if "มากที่สุด" in val else COLOR_DARK
            p.alignment = PP_ALIGN.CENTER if j > 0 else PP_ALIGN.LEFT

    tb_concl = s10.shapes.add_textbox(Inches(0.8), Inches(5.6), Inches(7.8), Inches(1.2))
    tf_con = tb_concl.text_frame
    tf_con.word_wrap = True
    p = tf_con.paragraphs[0]
    p.text = "💬 ข้อสรุปการประเมิน:"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p = tf_con.add_paragraph()
    p.text = "“ผลการประเมินคุณภาพระบบโดยผู้เชี่ยวชาญโดยรวมมีค่าเฉลี่ย 4.44 อยู่ในระดับ 'มาก' แสดงว่าระบบสามารถทำงานได้ตามวัตถุประสงค์ที่กำหนดไว้ โดยด้านความถูกต้องได้รับคะแนนสูงสุดที่ 4.52 (ระดับมากที่สุด)”"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(11.5)
    p.font.italic = True
    p.font.color.rgb = COLOR_MUTED

    add_card(s10, Inches(8.9), Inches(1.8), Inches(3.6), Inches(4.8), bg_color=COLOR_BG_LIGHT)
    tb_scale = s10.shapes.add_textbox(Inches(9.1), Inches(2.0), Inches(3.2), Inches(4.4))
    tf_s = tb_scale.text_frame
    tf_s.word_wrap = True

    p = tf_s.paragraphs[0]
    p.text = "⭐ ผลการประเมินภาพรวม"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(15)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.space_after = Pt(8)

    p = tf_s.add_paragraph()
    p.text = "4.44 / 5.00"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(28)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(2)

    p = tf_s.add_paragraph()
    p.text = "ระดับคุณภาพ: มาก"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(14)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(12)

    p = tf_s.add_paragraph()
    p.text = "เกณฑ์การแปลผล (Likert Scale):\n• 4.51 - 5.00 = มากที่สุด\n• 3.51 - 4.50 = มาก\n• 2.51 - 3.50 = ปานกลาง\n• 1.51 - 2.50 = น้อย\n• 1.00 - 1.50 = น้อยที่สุด"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(10.5)
    p.font.color.rgb = COLOR_MUTED

    add_speaker_note(s10, "⏱ เวลา: 45-60 วินาที\n"
                          "บทพูด: ผลการประเมินคุณภาพระบบจากผู้เชี่ยวชาญครับ มีค่าเฉลี่ยรวมทุกด้านอยู่ที่ 4.44 ระดับมาก โดยด้านที่มีคะแนนสูงสุดคือด้านความถูกต้องของระบบ ได้ 4.52 อยู่ในระดับมากที่สุด ตามด้วยด้านความปลอดภัย 4.45 และด้านการนำไปใช้ประโยชน์ 4.40 สะท้อนให้เห็นว่าระบบสามารถตอบสนองการใช้งานจริงของโรงเรียนได้อย่างมีประสิทธิภาพครับ")

    # =========================================================
    # SLIDE 11: สรุปผล อภิปรายผล และข้อเสนอแนะ (บทที่ 5) (45-60 วินาที)
    # =========================================================
    s11 = prs.slides.add_slide(blank_layout)
    set_slide_background(s11, COLOR_WHITE)
    add_header(s11, "สรุปผล อภิปรายผล และข้อเสนอแนะ (Conclusion)", "บทที่ 5 : สรุป อภิปราย และข้อเสนอแนะ", "45–60 วิ")

    cards_data = [
        ("📌 สรุปผลการวิจัย (Conclusion)", [
            "พัฒนาระบบสำเร็จตรงตามวัตถุประสงค์ รองรับ 5 บทบาท",
            "จัดการงานวินัย เช็คชื่อเข้าแถว และสแกนละหมาดได้รวดเร็ว",
            "ช่วยให้โรงเรียนศิริราษฎร์สามัคคีเปลี่ยนผ่านสู่ระบบดิจิทัลอย่างสมบูรณ์"
        ], COLOR_NAVY),
        ("💡 อภิปรายผล (Discussion)", [
            "ลดระยะเวลาค้นหาประวัติคะแนนของฝ่ายปกครองได้มากกว่า 70%",
            "เพิ่มการมีส่วนร่วมของผู้ปกครองในการติดตามบุตรหลานแบบเรียลไทม์",
            "ผลวิจัยสอดคล้องกับงานวิจัยที่เกี่ยวข้องด้านระบบติดตามพฤติกรรมออนไลน์"
        ], COLOR_TEAL),
        ("🚀 ข้อเสนอแนะเพื่อต่อยอด (Future Work)", [
            "พัฒนาต่อยอดเป็น Native Mobile App (iOS / Android)",
            "เชื่อมต่อระบบแจ้งเตือนอัตโนมัติผ่าน LINE Official Account / SMS",
            "ประยุกต์ใช้ AI / Machine Learning วิเคราะห์แนวโน้มพฤติกรรมกลุ่มเสี่ยง"
        ], COLOR_ORANGE)
    ]

    for i, (title, items, border_c) in enumerate(cards_data):
        x = Inches(0.8 + i * 4.0)
        add_card(s11, x, Inches(1.8), Inches(3.8), Inches(4.8), bg_color=COLOR_BG_LIGHT)
        
        hb = s11.shapes.add_shape(MSO_SHAPE.RECTANGLE, x, Inches(1.8), Inches(3.8), Inches(0.8))
        hb.fill.solid()
        hb.fill.fore_color.rgb = border_c
        hb.line.fill.background()
        tf_h = hb.text_frame
        p_h = tf_h.paragraphs[0]
        p_h.text = title
        p_h.font.name = FONT_FAMILY
        p_h.font.size = Pt(13)
        p_h.font.bold = True
        p_h.font.color.rgb = COLOR_WHITE
        p_h.alignment = PP_ALIGN.CENTER

        tb = s11.shapes.add_textbox(x + Inches(0.2), Inches(2.8), Inches(3.4), Inches(3.6))
        tf = tb.text_frame
        tf.word_wrap = True
        for item in items:
            p = tf.add_paragraph()
            p.text = f"• {item}"
            p.font.name = FONT_FAMILY
            p.font.size = Pt(12)
            p.font.color.rgb = COLOR_DARK
            p.space_after = Pt(10)

    add_speaker_note(s11, "⏱ เวลา: 45-60 วินาที\n"
                          "บทพูด: สรุปผล อภิปรายผล และข้อเสนอแนะครับ สรุปคือระบบสามารถใช้งานได้จริงตามวัตถุประสงค์ ช่วยลดความล่าช้าของเอกสาร และช่วยให้ผู้ปกครองรับรู้พฤติกรรมบุตรหลานได้ทันท่วงที สำหรับข้อเสนอแนะในการพัฒนาต่อยอดในอนาคต คือการพัฒนาเป็นแอปพลิเคชันมือถือ การส่งข้อความแจ้งเตือนผ่าน LINE อัตโนมัติ และการนำโมเดล AI มาช่วยทำนายแนวโน้มพฤติกรรมนักเรียนกลุ่มเสี่ยงครับ")

    # =========================================================
    # SLIDE 12: Q&A / ขอบคุณ / ข้อเสนอแนะ
    # =========================================================
    s12 = prs.slides.add_slide(blank_layout)
    set_slide_background(s12, COLOR_WHITE)

    add_card(s12, Inches(1.5), Inches(1.0), Inches(10.333), Inches(5.5), bg_color=RGBColor(241, 245, 249), border_color=COLOR_NAVY)

    tb_qa = s12.shapes.add_textbox(Inches(2.0), Inches(1.4), Inches(9.333), Inches(4.7))
    tf_q = tb_qa.text_frame
    tf_q.word_wrap = True

    p = tf_q.paragraphs[0]
    p.text = "THANK YOU"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(18)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(4)

    p = tf_q.add_paragraph()
    p.text = "ขอขอบพระคุณคณะกรรมการทุกท่าน"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(28)
    p.font.bold = True
    p.font.color.rgb = COLOR_NAVY
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(8)

    p = tf_q.add_paragraph()
    p.text = "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน\nกรณีศึกษา โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(14)
    p.font.color.rgb = COLOR_MUTED
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(20)

    p = tf_q.add_paragraph()
    p.text = "❓ คำถาม ข้อเสนอแนะ และข้อซักถาม (Q & A)"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(20)
    p.font.bold = True
    p.font.color.rgb = COLOR_ORANGE
    p.alignment = PP_ALIGN.CENTER
    p.space_after = Pt(14)

    p = tf_q.add_paragraph()
    p.text = "ผู้จัดทำ: นายตอริก ลือแม & นายบุสริน มูซอ\nสาขาวิชาเทคโนโลยีสารสนเทศ คณะวิทยาศาสตร์เทคโนโลยีและการเกษตร มหาวิทยาลัยราชภัฏยะลา"
    p.font.name = FONT_FAMILY
    p.font.size = Pt(12)
    p.font.color.rgb = COLOR_DARK
    p.alignment = PP_ALIGN.CENTER

    add_speaker_note(s12, "⏱ เวลา: ไม่จำกัด (ช่วงซักถาม)\n"
                          "บทพูด: กลุ่มของกระผมขอจบการนำเสนอโครงงานระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน โรงเรียนศิริราษฎร์สามัคคี เพียงเท่านี้ ขอขอบพระคุณท่านคณะกรรมการทุกท่านเป็นอย่างสูง และพร้อมรับฟังคำชี้แนะรวมถึงตอบข้อซักถามครับ ขอบคุณครับ")

    prs.save(OUTPUT_PPTX)
    print(f"Enhanced Presentation successfully created at: {OUTPUT_PPTX}")

if __name__ == "__main__":
    build_presentation()
