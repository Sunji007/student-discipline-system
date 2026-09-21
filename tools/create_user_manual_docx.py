# -*- coding: utf-8 -*-
import os
import time
import docx
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn

def set_cell_shading(cell, color_hex):
    """Set background color of a table cell."""
    shading = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{color_hex}"/>')
    cell._tc.get_or_add_tcPr().append(shading)

def set_cell_margins(cell, top=120, bottom=120, left=180, right=180):
    """Set inner padding of a table cell (in twips/dxa)."""
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = parse_xml(
        f'<w:tcMar {nsdecls("w")}>\n'
        f'  <w:top w:w="{top}" w:type="dxa"/>\n'
        f'  <w:bottom w:w="{bottom}" w:type="dxa"/>\n'
        f'  <w:left w:w="{left}" w:type="dxa"/>\n'
        f'  <w:right w:w="{right}" w:type="dxa"/>\n'
        f'</w:tcMar>'
    )
    tcPr.append(tcMar)

def set_cell_borders(cell, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC", sz="4"):
    """Set borders on a cell."""
    tcPr = cell._tc.get_or_add_tcPr()
    b_top = f'<w:top w:val="single" w:sz="{sz}" w:space="0" w:color="{top}"/>' if top else '<w:top w:val="none"/>'
    b_bottom = f'<w:bottom w:val="single" w:sz="{sz}" w:space="0" w:color="{bottom}"/>' if bottom else '<w:bottom w:val="none"/>'
    b_left = f'<w:left w:val="single" w:sz="{sz}" w:space="0" w:color="{left}"/>' if left else '<w:left w:val="none"/>'
    b_right = f'<w:right w:val="single" w:sz="{sz}" w:space="0" w:color="{right}"/>' if right else '<w:right w:val="none"/>'
    
    tcBorders = parse_xml(
        f'<w:tcBorders {nsdecls("w")}>\n'
        f'  {b_top}\n'
        f'  {b_bottom}\n'
        f'  {b_left}\n'
        f'  {b_right}\n'
        f'</w:tcBorders>'
    )
    tcPr.append(tcBorders)

def add_styled_paragraph(doc, text="", font_size=14, bold=False, italic=False, color_rgb=(0,0,0), 
                         align=WD_ALIGN_PARAGRAPH.LEFT, space_before=0, space_after=6, font_name="TH Sarabun New"):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if text:
        run = p.add_run(text)
        run.font.name = font_name
        run.font.size = Pt(font_size)
        run.bold = bold
        run.italic = italic
        run.font.color.rgb = RGBColor(*color_rgb)
        # Fix font for Asian text
        rPr = run._r.get_or_add_rPr()
        rFonts = parse_xml(f'<w:rFonts {nsdecls("w")} w:ascii="{font_name}" w:hAnsi="{font_name}" w:cs="{font_name}"/>')
        rPr.append(rFonts)
    return p

def add_run_to_p(p, text, font_size=14, bold=False, italic=False, color_rgb=(0,0,0), font_name="TH Sarabun New"):
    run = p.add_run(text)
    run.font.name = font_name
    run.font.size = Pt(font_size)
    run.bold = bold
    run.italic = italic
    run.font.color.rgb = RGBColor(*color_rgb)
    rPr = run._r.get_or_add_rPr()
    rFonts = parse_xml(f'<w:rFonts {nsdecls("w")} w:ascii="{font_name}" w:hAnsi="{font_name}" w:cs="{font_name}"/>')
    rPr.append(rFonts)
    return run

def add_image_placeholder(doc, fig_num, title, width_in=5.8, height_in=3.0, route_hint=""):
    """
    Inserts real screenshot from docs/manual_images/ if available,
    otherwise creates a clean placeholder box.
    """
    img_filename = f"fig_{fig_num:02d}.png"
    img_path = os.path.abspath(os.path.join("docs", "manual_images", img_filename))
    
    if os.path.exists(img_path):
        table = doc.add_table(rows=1, cols=1)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = table.cell(0, 0)
        cell.width = Inches(width_in)
        
        # Subtle clean border for the screenshot frame
        set_cell_shading(cell, "FFFFFF")
        set_cell_margins(cell, top=60, bottom=60, left=60, right=60)
        set_cell_borders(cell, top="B0BEC5", bottom="B0BEC5", left="B0BEC5", right="B0BEC5", sz="6")
        
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(2)
        p.paragraph_format.space_after = Pt(2)
        run = p.add_run()
        run.add_picture(img_path, width=Inches(width_in - 0.2))
        
        # Caption below image
        cap_p = add_styled_paragraph(doc, f"ภาพที่ {fig_num} ", font_size=13, bold=True, color_rgb=(0, 51, 102),
                                     align=WD_ALIGN_PARAGRAPH.CENTER, space_before=6, space_after=6)
        add_run_to_p(cap_p, title, font_size=13, bold=False, color_rgb=(50, 50, 50))
        return table
    else:
        table = doc.add_table(rows=1, cols=1)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = table.cell(0, 0)
        cell.width = Inches(width_in)
        
        set_cell_shading(cell, "F8F9FA")
        set_cell_margins(cell, top=260, bottom=260, left=200, right=200)
        set_cell_borders(cell, top="008080", bottom="008080", left="008080", right="008080", sz="6")
        
        p = cell.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(4)
        
        add_run_to_p(p, f"📷 [ กรุณาวางภาพหน้าจอที่ {fig_num} ที่นี่ ]\n", font_size=14, bold=True, color_rgb=(0, 102, 102))
        add_run_to_p(p, f"{title}\n", font_size=13, bold=False, color_rgb=(80, 80, 80))
        if route_hint:
            add_run_to_p(p, f"(เข้าแคปภาพได้ที่เมนู / URL: {route_hint})", font_size=11, italic=True, color_rgb=(130, 130, 130))
        
        cap_p = add_styled_paragraph(doc, f"ภาพที่ {fig_num} ", font_size=13, bold=True, color_rgb=(0, 51, 102),
                                     align=WD_ALIGN_PARAGRAPH.CENTER, space_before=4, space_after=6)
        add_run_to_p(cap_p, title, font_size=13, bold=False, color_rgb=(50, 50, 50))
        return table

def build_user_manual():
    doc = docx.Document()
    
    # Set page margins to standard A4 (2.54 cm / 1 inch)
    section = doc.sections[0]
    section.top_margin = Cm(2.54)
    section.bottom_margin = Cm(2.54)
    section.left_margin = Cm(2.54)
    section.right_margin = Cm(2.54)
    section.page_width = Cm(21.0)
    section.page_height = Cm(29.7)
    
    # Different first page for cover
    section.different_first_page_header_footer = True
    
    # Configure running header on subsequent pages
    header = section.header
    header_p = header.paragraphs[0]
    header_p.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    header_p.paragraph_format.space_after = Pt(2)
    hrun1 = add_run_to_p(header_p, "คู่มือการใช้งาน (User Manual) ", font_size=11, bold=True, color_rgb=(232, 119, 34))
    hrun2 = add_run_to_p(header_p, "| ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน", font_size=10, italic=True, color_rgb=(100, 100, 100))
    
    # Add bottom border to header
    pPr = header_p._p.get_or_add_pPr()
    pBdr = parse_xml(f'<w:pBdr {nsdecls("w")}><w:bottom w:val="single" w:sz="6" w:space="1" w:color="E87722"/></w:pBdr>')
    pPr.append(pBdr)

    # Configure running footer (page number)
    footer = section.footer
    footer_p = footer.paragraphs[0]
    footer_p.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    footer_p.paragraph_format.space_before = Pt(4)
    frun = add_run_to_p(footer_p, "หน้า ", font_size=10, color_rgb=(120, 120, 120))
    # XML page number
    fldSimple = parse_xml(f'<w:fldSimple {nsdecls("w")} w:instr="PAGE"/>')
    footer_p._p.append(fldSimple)

    # ==========================================
    # 1. COVER PAGE (หน้าปก)
    # ==========================================
    # Top Logos Table: University Logo & Department Logo side-by-side
    logo_uni_path = os.path.abspath("docs/manual_images/logo_university.jpg")
    logo_dept_path = os.path.abspath("docs/manual_images/logo_department.jpg")
    
    logo_tbl = doc.add_table(rows=1, cols=2)
    logo_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    c0 = logo_tbl.cell(0, 0)
    c1 = logo_tbl.cell(0, 1)
    c0.width = Inches(2.9)
    c1.width = Inches(2.9)
    
    p0 = c0.paragraphs[0]
    p0.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p0.paragraph_format.space_before = Pt(0)
    p0.paragraph_format.space_after = Pt(0)
    if os.path.exists(logo_uni_path):
        r0 = p0.add_run()
        r0.add_picture(logo_uni_path, height=Inches(1.35))
        
    p1 = c1.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.LEFT
    p1.paragraph_format.space_before = Pt(0)
    p1.paragraph_format.space_after = Pt(0)
    if os.path.exists(logo_dept_path):
        p1.add_run("   ")
        r1 = p1.add_run()
        r1.add_picture(logo_dept_path, height=Inches(1.30))
        
    p_title1 = add_styled_paragraph(doc, "คู่มือการใช้งาน (User Manual)", font_size=24, bold=True, 
                                    color_rgb=(15, 76, 129), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=8, space_after=4)
    p_title2 = add_styled_paragraph(doc, "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน", font_size=17, bold=True, 
                                    color_rgb=(30, 30, 30), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=2, space_after=2)
    p_title3 = add_styled_paragraph(doc, "กรณีศึกษา : โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี", font_size=13.5, bold=True, 
                                    color_rgb=(0, 102, 153), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=2, space_after=2)
    p_title4 = add_styled_paragraph(doc, "Information System Discipline and Monitoring Student Behavior : A Case Study of Sirirat Samakkhi School Pattani Province", font_size=10.5, italic=True, 
                                    color_rgb=(100, 100, 100), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=0, space_after=10)

    # Showcase Image on Cover Page
    cover_img_path = os.path.abspath(os.path.join("docs", "manual_images", "fig_02.png"))
    if os.path.exists(cover_img_path):
        c_tbl = doc.add_table(rows=1, cols=1)
        c_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        c_cell = c_tbl.cell(0, 0)
        c_cell.width = Inches(5.2)
        set_cell_shading(c_cell, "FFFFFF")
        set_cell_margins(c_cell, top=60, bottom=60, left=60, right=60)
        set_cell_borders(c_cell, top="0D6EFD", bottom="0D6EFD", left="0D6EFD", right="0D6EFD", sz="8")
        
        cp = c_cell.paragraphs[0]
        cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        cp.paragraph_format.space_before = Pt(2)
        cp.paragraph_format.space_after = Pt(2)
        crun = cp.add_run()
        crun.add_picture(cover_img_path, width=Inches(5.0))
    else:
        box_tbl = doc.add_table(rows=1, cols=1)
        box_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        b_cell = box_tbl.cell(0, 0)
        b_cell.width = Inches(5.2)
        set_cell_shading(b_cell, "F0F4F8")
        set_cell_margins(b_cell, top=200, bottom=200, left=200, right=200)
        set_cell_borders(b_cell, top="0D6EFD", bottom="0D6EFD", left="0D6EFD", right="0D6EFD", sz="8")
        
        bp = b_cell.paragraphs[0]
        bp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        add_run_to_p(bp, "🏫 ภาพระบบงานสารสนเทศวินัยและติดตามพฤติกรรม\n", font_size=15, bold=True, color_rgb=(13, 110, 180))
        add_run_to_p(bp, "ระบบบันทึกความประพฤติ • เช็คชื่อเข้าเรียน • สแกนละหมาด QR Code • วิเคราะห์กลุ่มเสี่ยง\n", font_size=12, color_rgb=(70, 70, 70))

    # Author section at bottom
    p_author_hdr = add_styled_paragraph(doc, "จัดทำโดย", font_size=15, bold=True, 
                                        color_rgb=(15, 76, 129), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=10, space_after=2)
    
    p_author = add_styled_paragraph(doc, "นายตอริก ลือแม               รหัสนักศึกษา 406665012\n"
                                         "นายบุสริน มูซอ                 รหัสนักศึกษา 406665027", 
                                    font_size=13, color_rgb=(40, 40, 40), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=0, space_after=8)
    
    p_dept = doc.add_paragraph()
    p_dept.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_dept.paragraph_format.space_before = Pt(0)
    p_dept.paragraph_format.space_after = Pt(0)
    p_dept.paragraph_format.line_spacing = 1.15
    r_dept1 = add_run_to_p(p_dept, "หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ\n", font_size=13, bold=True, color_rgb=(40, 40, 40))
    r_dept2 = add_run_to_p(p_dept, "คณะวิทยาศาสตร์เทคโนโลยีและการเกษตร มหาวิทยาลัยราชภัฏยะลา\nปีการศึกษา 2569", font_size=12, bold=False, color_rgb=(70, 70, 70))

    doc.add_page_break()

    # ==========================================
    # 2. TABLE OF CONTENTS (สารบัญ)
    # ==========================================
    add_styled_paragraph(doc, "สารบัญ", font_size=20, bold=True, color_rgb=(0, 51, 102), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=8, space_after=14)
    
    toc_data = [
        ("บทนำและข้อมูลเบื้องต้นของระบบ", "1"),
        ("     สิทธิ์การเข้าใช้งานระบบและบัญชีผู้ใช้งานทดสอบ (Default Accounts)", "1"),
        ("     หน้าเข้าสู่ระบบ (Login)", "2"),
        ("1. กลุ่มของผู้ดูแลระบบ (Admin)", "3"),
        ("     1.1) หน้าแดชบอร์ดผู้ดูแลระบบ (Admin Dashboard)", "3"),
        ("     1.2) การจัดการข้อมูลผู้ใช้งานระบบ (User Management)", "4"),
        ("     1.3) การจัดการข้อมูลนักเรียน (Student Management)", "5"),
        ("     1.4) การพิมพ์บัตรประจำตัวนักเรียนและ QR Code", "6"),
        ("     1.5) การนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel/CSV", "7"),
        ("     1.6) การจัดการข้อมูลผู้ปกครอง (Parent / Guardian)", "8"),
        ("     1.7) การจัดการข้อมูลครูและครูที่ปรึกษา (Teacher Management)", "9"),
        ("     1.8) การกำหนดสิทธิ์การใช้งานของบทบาท (Role & Permissions)", "10"),
        ("     1.9) การจัดการปีการศึกษาและภาคเรียน (Semester Setup)", "11"),
        ("2. กลุ่มของฝ่ายปกครอง (Discipline Officer)", "12"),
        ("     2.1) หน้าแดชบอร์ดฝ่ายปกครอง", "12"),
        ("     2.2) หน้ารายชื่อนักเรียนกลุ่มเสี่ยง (Risk Students Monitor)", "13"),
        ("     2.3) การตั้งค่าเกณฑ์กฎระเบียบคะแนนพฤติกรรม (Behavior Rules)", "14"),
        ("     2.4) การตรวจสอบและอนุมัติการบันทึกพฤติกรรม (Approve Records)", "15"),
        ("     2.5) การพิจารณาคำร้องขออุทธรณ์คะแนน (Review Appeals)", "16"),
        ("     2.6) การจัดการเรื่องแจ้งเบาะแสพฤติกรรม (Informant Reports)", "17"),
        ("     2.7) หน้ารายงานสรุปพฤติกรรมและการส่งออกข้อมูล (Reports & Export)", "18"),
        ("3. กลุ่มของครูผู้สอนและครูที่ปรึกษา (Teacher)", "19"),
        ("     3.1) หน้าแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา (My Classroom)", "19"),
        ("     3.2) การบันทึกเวลาเรียนและการเช็คชื่อประจำวัน (Daily Attendance)", "20"),
        ("     3.3) การบันทึกคะแนนพฤติกรรมนักเรียน (Log Behavior Record)", "21"),
        ("     3.4) การสแกนเช็คชื่อละหมาด (Prayer Scanner)", "22"),
        ("     3.5) ระบบกล่องข้อความติดต่อสื่อสาร (Teacher Messages)", "23"),
        ("4. กลุ่มของนักเรียน (Student)", "24"),
        ("     4.1) หน้าแดชบอร์ดนักเรียนและคะแนนคงเหลือ (Student Dashboard)", "24"),
        ("     4.2) การตรวจสอบประวัติการเข้าแถวและเข้าเรียน", "25"),
        ("     4.3) การตรวจสอบประวัติการตัด-เพิ่มคะแนนความประพฤติ", "25"),
        ("     4.4) การยื่นคำร้องขออุทธรณ์คะแนน (Submit Appeal)", "26"),
        ("     4.5) การเปิดบัตร QR Code ประจำตัวเพื่อเช็คชื่อละหมาด", "27"),
        ("     4.6) การส่งเรื่องแจ้งเบาะแสพฤติกรรมแบบไม่ระบุตัวตน", "28"),
        ("5. กลุ่มของผู้ปกครอง (Parent / Guardian)", "29"),
        ("     5.1) หน้าแดชบอร์ดผู้ปกครองและการสลับดูข้อมูลบุตรหลาน", "29"),
        ("     5.2) การตรวจสอบคะแนนพฤติกรรมและประวัติการทำทัณฑ์บน", "30"),
        ("     5.3) การติดตามประวัติการเข้าเรียนและการขาดแถว", "30"),
        ("     5.4) การส่งข้อความติดต่อครูที่ปรึกษาหรือฝ่ายปกครอง", "31"),
        ("ภาคผนวก: ตารางสรุปภาพหน้าจอและ URL สำหรับแคปภาพลงเล่ม", "32"),
    ]
    
    toc_tbl = doc.add_table(rows=len(toc_data) + 1, cols=2)
    toc_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    # Column widths
    col_w = [Inches(5.0), Inches(1.2)]
    for row in toc_tbl.rows:
        row.cells[0].width = col_w[0]
        row.cells[1].width = col_w[1]
        
    # Header row
    hdr0 = toc_tbl.cell(0, 0)
    hdr1 = toc_tbl.cell(0, 1)
    set_cell_shading(hdr0, "1B365D")
    set_cell_shading(hdr1, "1B365D")
    set_cell_margins(hdr0, top=140, bottom=140, left=160, right=160)
    set_cell_margins(hdr1, top=140, bottom=140, left=160, right=160)
    set_cell_borders(hdr0, top="1B365D", bottom="1B365D", left=None, right=None)
    set_cell_borders(hdr1, top="1B365D", bottom="1B365D", left=None, right=None)
    
    p0 = hdr0.paragraphs[0]
    p0.alignment = WD_ALIGN_PARAGRAPH.LEFT
    add_run_to_p(p0, "เรื่อง", font_size=14, bold=True, color_rgb=(255, 255, 255))
    
    p1 = hdr1.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    add_run_to_p(p1, "หน้า", font_size=14, bold=True, color_rgb=(255, 255, 255))
    
    for i, (title, page) in enumerate(toc_data):
        c0 = toc_tbl.cell(i + 1, 0)
        c1 = toc_tbl.cell(i + 1, 1)
        
        is_major = not title.startswith("     ")
        bg_color = "F0F4F8" if is_major else ("FFFFFF" if i % 2 == 0 else "FAFAFA")
        set_cell_shading(c0, bg_color)
        set_cell_shading(c1, bg_color)
        set_cell_margins(c0, top=100, bottom=100, left=140, right=140)
        set_cell_margins(c1, top=100, bottom=100, left=140, right=140)
        
        border_b = "DDDDDD" if is_major else "EEEEEE"
        set_cell_borders(c0, top=None, bottom=border_b, left=None, right=None)
        set_cell_borders(c1, top=None, bottom=border_b, left=None, right=None)
        
        p_t = c0.paragraphs[0]
        p_t.alignment = WD_ALIGN_PARAGRAPH.LEFT
        add_run_to_p(p_t, title, font_size=13, bold=is_major, color_rgb=(0, 51, 102) if is_major else (40, 40, 40))
        
        p_pg = c1.paragraphs[0]
        p_pg.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        add_run_to_p(p_pg, page, font_size=13, bold=is_major, color_rgb=(0, 51, 102) if is_major else (90, 90, 90))

    doc.add_page_break()

    # ==========================================
    # 3. INTRODUCTION & LOGIN
    # ==========================================
    add_styled_paragraph(doc, "บทนำและข้อมูลเบื้องต้นของระบบ", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    
    intro_txt = (
        "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน แบ่งการทำงานตามสิทธิ์การเข้าใช้งาน "
        "(Role-based Access Control) ออกเป็น 5 กลุ่มผู้ใช้งานหลัก ได้แก่ ผู้ดูแลระบบ (Admin), ฝ่ายปกครอง (Discipline Officer), "
        "ครูผู้สอน/ครูที่ปรึกษา (Teacher), นักเรียน (Student) และ ผู้ปกครอง (Parent) โดยมีรายละเอียดหน้าที่และความรับผิดชอบของแต่ละกลุ่มผู้ใช้งาน ดังต่อไปนี้"
    )
    add_styled_paragraph(doc, intro_txt, font_size=14, space_before=0, space_after=8)
    
    url_p = add_styled_paragraph(doc, "ลิงก์เข้าใช้งานระบบ (System URL): ", font_size=14, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_run_to_p(url_p, "https://406665027.site.yru.ac.th หรือ http://localhost/student-discipline-system/public", font_size=13, italic=True, color_rgb=(0, 102, 204))

    # Test Accounts Table
    add_styled_paragraph(doc, "ตารางบัญชีผู้ใช้งานจำลองสำหรับทดสอบระบบ (Default Test Accounts)", font_size=14, bold=True, color_rgb=(0, 51, 102), space_before=6, space_after=4)
    
    acc_data = [
        ("ผู้ดูแลระบบ (Admin)", "admin", "password123", "Admin System", "จัดการบัญชีผู้ใช้, นำเข้าข้อมูลนักเรียน ครู, กำหนดสิทธิ์, สลับภาคเรียน"),
        ("ฝ่ายปกครอง (Discipline)", "discipline1", "password123", "ครูสมชาย ฝ่ายปกครอง", "ตั้งเกณฑ์คะแนน, อนุมัติตัด/เพิ่มคะแนน, ตรวจคำร้องอุทธรณ์, ดูรายงาน"),
        ("ครูที่ปรึกษา (Teacher)", "teacher1", "password123", "ครูสมหญิง ที่ปรึกษา", "เช็คชื่อประจำวัน, บันทึกพฤติกรรมส่งฝ่ายปกครอง, สแกนละหมาด"),
        ("นักเรียน (Student)", "student1", "password123", "ด.ช. ทดสอบ ระบบ", "ตรวจสอบคะแนนคงเหลือ, ยื่นคำร้องอุทธรณ์, แสดง QR Code ละหมาด"),
        ("ผู้ปกครอง (Parent)", "parent1", "password123", "ผู้ปกครอง ทดสอบ", "ติดตามคะแนนพฤติกรรมบุตรหลาน, สถิติการเข้าเรียน, ส่งข้อความคุยกับครู"),
    ]
    
    acc_tbl = doc.add_table(rows=len(acc_data) + 1, cols=5)
    acc_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    widths = [Inches(1.5), Inches(1.0), Inches(1.1), Inches(1.3), Inches(1.8)]
    
    headers = ["กลุ่มผู้ใช้งาน (Role)", "ชื่อผู้ใช้ (User)", "รหัสผ่าน", "ชื่อในระบบ", "หน้าที่หลักในระบบ"]
    for j, h in enumerate(headers):
        c = acc_tbl.cell(0, j)
        c.width = widths[j]
        set_cell_shading(c, "1B365D")
        set_cell_margins(c, top=120, bottom=120, left=100, right=100)
        set_cell_borders(c, top="1B365D", bottom="1B365D", left="1B365D", right="1B365D")
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        add_run_to_p(p, h, font_size=12, bold=True, color_rgb=(255, 255, 255))
        
    for i, row in enumerate(acc_data):
        for j, val in enumerate(row):
            c = acc_tbl.cell(i + 1, j)
            c.width = widths[j]
            bg = "F9FBFD" if i % 2 == 0 else "FFFFFF"
            set_cell_shading(c, bg)
            set_cell_margins(c, top=100, bottom=100, left=100, right=100)
            set_cell_borders(c, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC")
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [1, 2] else WD_ALIGN_PARAGRAPH.LEFT
            add_run_to_p(p, val, font_size=11, bold=(j == 0), color_rgb=(0, 51, 102) if j == 0 else (40, 40, 40))
            
    add_styled_paragraph(doc, "", font_size=8, space_before=4, space_after=4)

    # Login Section
    add_styled_paragraph(doc, "หน้าเข้าสู่ระบบ (Login)", font_size=16, bold=True, color_rgb=(0, 51, 102), space_before=8, space_after=6)
    add_image_placeholder(doc, 1, "หน้าจอแสดงผลการเข้าสู่ระบบ (Login)", width_in=5.8, height_in=2.8, route_hint="/login")
    
    login_desc = (
        "จากภาพที่ 1 หน้าจอแสดงผลการเข้าสู่ระบบ ผู้ใช้งานทุกกลุ่มสามารถเข้าใช้งานโดยการป้อน "
        "ชื่อผู้ใช้ (Username) และ รหัสผ่าน (Password) จากนั้นคลิกปุ่ม \"เข้าสู่ระบบ\" เพื่อเข้าสู่ระบบ\n"
        "หมายเหตุ: เมื่อเข้าสู่ระบบสำเร็จ ระบบจะทำการตรวจสอบสิทธิ์และเปลี่ยนเส้นทางไปยังหน้าแดชบอร์ดตามบทบาทของผู้ใช้งานโดยอัตโนมัติ"
    )
    add_styled_paragraph(doc, login_desc, font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 4. GROUP 1: ADMIN (ผู้ดูแลระบบ)
    # ==========================================
    add_styled_paragraph(doc, "1. กลุ่มของผู้ดูแลระบบ (Admin)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "การทำงานของผู้ดูแลระบบครอบคลุมการจัดการโครงสร้างระบบ การจัดการข้อมูลผู้ใช้ ข้อมูลนักเรียน ข้อมูลครู การพิมพ์บัตร และการกำหนดสิทธิ์ของระบบ มีรายละเอียดดังนี้:", font_size=13, space_before=0, space_after=8)

    # 1.1 Dashboard
    add_styled_paragraph(doc, "1.1) หน้าแดชบอร์ดผู้ดูแลระบบ (Admin Dashboard)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_placeholder(doc, 2, "หน้าจอแสดงแดชบอร์ดผู้ดูแลระบบ", width_in=5.8, height_in=2.8, route_hint="/admin/dashboard")
    add_styled_paragraph(doc, "จากภาพที่ 2 หน้าจอแดชบอร์ดสำหรับผู้ดูแลระบบ ผู้ดูแลระบบสามารถดูสรุปจำนวนผู้ใช้งานในระบบ, สถิตินักเรียนทั้งหมด, จำนวนครู, บันทึกพฤติกรรมล่าสุด และเลือกสลับปีการศึกษา/ภาคเรียนปัจจุบันได้จากแถบเมนูด้านบน", font_size=13, space_before=4, space_after=10)

    # 1.2 User Management
    add_styled_paragraph(doc, "1.2) การจัดการข้อมูลผู้ใช้งานระบบ (User Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 3, "หน้าจอแสดงรายการข้อมูลผู้ใช้งานในระบบ", width_in=5.8, height_in=2.6, route_hint="/admin/users")
    add_styled_paragraph(doc, "จากภาพที่ 3 หน้าจอรายการผู้ใช้งาน ผู้ดูแลระบบสามารถค้นหาผู้ใช้งาน, ตรวจสอบบทบาท, สามารถเพิ่มผู้ใช้ใหม่โดยคลิกปุ่ม \"+ เพิ่มผู้ใช้งาน\", แก้ไขข้อมูลหรือรีเซ็ตรหัสผ่านโดยกดปุ่มแก้ไข (รูปดินสอ) และลบผู้ใช้งานโดยกดปุ่มลบ (รูปถังขยะ)", font_size=13, space_before=4, space_after=8)

    add_image_placeholder(doc, 4, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขข้อมูลผู้ใช้งาน", width_in=5.8, height_in=2.6, route_hint="/admin/users/create")
    add_styled_paragraph(doc, "จากภาพที่ 4 หน้าจอแบบฟอร์มเพิ่มข้อมูลผู้ใช้ ผู้ดูแลระบบกรอกข้อมูลชื่อ-สกุล, เลขบัตรประชาชน, กำหนดบทบาทสิทธิ์ (Admin, Teacher, Discipline, Student, Parent), บัญชีผู้ใช้ และรหัสผ่าน จากนั้นคลิกปุ่ม \"บันทึกข้อมูล\"", font_size=13, space_before=4, space_after=10)

    # 1.3 Student Management
    add_styled_paragraph(doc, "1.3) การจัดการข้อมูลนักเรียน (Student Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 5, "หน้าจอแสดงรายการข้อมูลนักเรียน", width_in=5.8, height_in=2.6, route_hint="/admin/students")
    add_styled_paragraph(doc, "จากภาพที่ 5 ผู้ดูแลระบบสามารถดูรายชื่อนักเรียนทั้งหมดตามชั้นเรียน ห้องเรียน ดูคะแนนพฤติกรรมคงเหลือ และเข้าสู่เมนูพิมพ์บัตรประจำตัวนักเรียน หรือผูกข้อมูลผู้ปกครอง", font_size=13, space_before=4, space_after=8)

    add_image_placeholder(doc, 6, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขข้อมูลนักเรียน", width_in=5.8, height_in=2.6, route_hint="/admin/students/create")
    add_styled_paragraph(doc, "จากภาพที่ 6 กรอกข้อมูลรหัสนักเรียน, เลขประจำตัวประชาชน, คำนำหน้า, ชื่อ-สกุล, ชั้นเรียน, ห้องเรียน และข้อมูลติดต่อ จากนั้นคลิกปุ่ม \"บันทึกข้อมูลนักเรียน\"", font_size=13, space_before=4, space_after=10)

    # 1.4 Student Card & QR
    add_styled_paragraph(doc, "1.4) การพิมพ์บัตรประจำตัวนักเรียนและ QR Code", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 7, "หน้าจอแสดงบัตรประจำตัวนักเรียนพร้อม QR Code", width_in=5.8, height_in=2.6, route_hint="/admin/students/{id}/card")
    add_styled_paragraph(doc, "จากภาพที่ 7 ระบบจะประมวลผลสร้างบัตรประจำตัวนักเรียนพร้อมรูปถ่ายและ QR Code ประจำตัว สำหรับใช้ในการสแกนเช็คชื่อละหมาดและเข้าแถว สามารถสั่งพิมพ์บัตรได้โดยคลิกปุ่ม \"พิมพ์บัตร\"", font_size=13, space_before=4, space_after=10)

    # 1.5 Student Import
    add_styled_paragraph(doc, "1.5) การนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel/CSV", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 8, "หน้าจอนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel", width_in=5.8, height_in=2.6, route_hint="/admin/students/import")
    add_styled_paragraph(doc, "จากภาพที่ 8 ผู้ดูแลระบบสามารถดาวน์โหลดแม่แบบตาราง (Template Excel) กรอกข้อมูลนักเรียนทั้งระดับชั้น แล้วอัปโหลดไฟล์เข้ามาเพื่อนำเข้าข้อมูลนักเรียนและสร้างบัญชีให้อัตโนมัติ", font_size=13, space_before=4, space_after=10)

    # 1.6 Parents
    add_styled_paragraph(doc, "1.6) การจัดการข้อมูลผู้ปกครอง (Parent / Guardian)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 9, "หน้าจอจัดการข้อมูลผู้ปกครองและเชื่อมโยงบุตรหลาน", width_in=5.8, height_in=2.6, route_hint="/admin/students/{id}/parents")
    add_styled_paragraph(doc, "จากภาพที่ 9 สามารถเพิ่มข้อมูลผู้ปกครอง ระบุความสัมพันธ์ (บิดา, มารดา, ผู้ปกครอง) และผูกรหัสนักเรียนเข้ากับผู้ปกครอง เพื่อให้ผู้ปกครองสามารถเข้าสู่ระบบมาติดตามพฤติกรรมบุตรหลานได้", font_size=13, space_before=4, space_after=10)

    # 1.7 Teachers
    add_styled_paragraph(doc, "1.7) การจัดการข้อมูลครูและครูที่ปรึกษา (Teacher Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 10, "หน้าจอแสดงข้อมูลครูและห้องเรียนประจำชั้น", width_in=5.8, height_in=2.6, route_hint="/admin/teachers")
    add_styled_paragraph(doc, "จากภาพที่ 10 ผู้ดูแลระบบสามารถจัดการรายชื่อครู และกำหนดห้องเรียนที่ปรึกษา (Advisory Class) เช่น กำหนดให้ครูดูแลห้อง ม.3/1 เพื่อให้ครูมีสิทธิ์ในการเช็คชื่อและดูข้อมูลของห้องดังกล่าว", font_size=13, space_before=4, space_after=10)

    # 1.8 Role & Permission
    add_styled_paragraph(doc, "1.8) การกำหนดสิทธิ์การใช้งานของบทบาท (Role & Permissions)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 11, "หน้าจอตารางกำหนดสิทธิ์การเข้าถึงเมนูของแต่ละบทบาท", width_in=5.8, height_in=2.6, route_hint="/admin/permissions")
    add_styled_paragraph(doc, "จากภาพที่ 11 ผู้ดูแลระบบสามารถติ๊กเลือกเปิดหรือปิดสิทธิ์ของแต่ละบทบาท เช่น สิทธิ์การเข้าถึงแดชบอร์ด, สิทธิ์การดูรายงาน, สิทธิ์การบันทึกพฤติกรรม แล้วคลิกปุ่ม \"บันทึกการตั้งค่าสิทธิ์\"", font_size=13, space_before=4, space_after=10)

    # 1.9 Semester
    add_styled_paragraph(doc, "1.9) การจัดการปีการศึกษาและภาคเรียน (Semester Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 12, "หน้าจอจัดการปีการศึกษาและภาคเรียน", width_in=5.8, height_in=2.6, route_hint="/admin/semesters")
    add_styled_paragraph(doc, "จากภาพที่ 12 สามารถสร้างภาคเรียนใหม่ และกดปุ่ม \"กำหนดเป็นภาคเรียนปัจจุบัน (Set Active)\" เพื่อให้ระบบคำนวณสถิติและคะแนนพฤติกรรมเริ่มต้น 100 คะแนนในเทอมนั้น ๆ", font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 5. GROUP 2: DISCIPLINE (ฝ่ายปกครอง)
    # ==========================================
    add_styled_paragraph(doc, "2. กลุ่มของฝ่ายปกครอง (Discipline Officer)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "ฝ่ายปกครองมีหน้าที่กำกับดูแลกฎระเบียบวินัย พิจารณาอนุมัติตัด/เพิ่มคะแนน ตรวจสอบเรื่องอุทธรณ์ และติดตามนักเรียนกลุ่มเสี่ยง มีรายละเอียดการใช้งานดังนี้:", font_size=13, space_before=0, space_after=8)

    # 2.1 Discipline Dashboard
    add_styled_paragraph(doc, "2.1) หน้าแดชบอร์ดฝ่ายปกครอง", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_placeholder(doc, 13, "หน้าจอแสดงแดชบอร์ดของฝ่ายปกครอง", width_in=5.8, height_in=2.6, route_hint="/discipline/dashboard")
    add_styled_paragraph(doc, "จากภาพที่ 13 ฝ่ายปกครองสามารถดูสรุปจำนวนบันทึกพฤติกรรมที่รอการอนุมัติ, คำร้องอุทธรณ์ที่รอดำเนินการ, สถิติการกระทำผิด และสถิตินักเรียนกลุ่มเสี่ยงที่มีคะแนนต่ำกว่าเกณฑ์", font_size=13, space_before=4, space_after=10)

    # 2.2 Risk Students
    add_styled_paragraph(doc, "2.2) หน้ารายชื่อนักเรียนกลุ่มเสี่ยง (Risk Students Monitor)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 14, "หน้าจอติดตามนักเรียนกลุ่มเสี่ยง (Risk Students)", width_in=5.8, height_in=2.6, route_hint="/discipline/risk-students")
    add_styled_paragraph(doc, "จากภาพที่ 14 แสดงรายชื่อนักเรียนที่มีคะแนนคงเหลือต่ำกว่าเกณฑ์ (เช่น ต่ำกว่า 50 คะแนน) พร้อมระดับแจ้งเตือน เพื่อให้ฝ่ายปกครองสามารถเรียกพบ ทำทัณฑ์บน หรือออกหนังสือแจ้งผู้ปกครอง", font_size=13, space_before=4, space_after=10)

    # 2.3 Behavior Rules
    add_styled_paragraph(doc, "2.3) การตั้งค่าเกณฑ์กฎระเบียบคะแนนพฤติกรรม (Behavior Rules)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 15, "หน้าจอแสดงรายการเกณฑ์กฎระเบียบวินัย", width_in=5.8, height_in=2.6, route_hint="/discipline/behavior-rules")
    add_styled_paragraph(doc, "จากภาพที่ 15 แสดงรายการกฎระเบียบทั้งหมด ฝ่ายปกครองสามารถเพิ่มเกณฑ์ใหม่โดยกดปุ่ม \"+ เพิ่มกฎระเบียบ\", แก้ไขคะแนน หรือลบเกณฑ์กฎระเบียบได้", font_size=13, space_before=4, space_after=8)

    add_image_placeholder(doc, 16, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขกฎระเบียบและคะแนน", width_in=5.8, height_in=2.6, route_hint="/discipline/behavior-rules/create")
    add_styled_paragraph(doc, "จากภาพที่ 16 ระบุชื่อข้อระเบียบ, หมวดหมู่ความประพฤติ, ประเภทการตัดคะแนน (-) หรือเพิ่มคะแนนความดี (+), จำนวนคะแนน แล้วคลิกปุ่ม \"บันทึกเกณฑ์\"", font_size=13, space_before=4, space_after=10)

    # 2.4 Approve Behavior Records
    add_styled_paragraph(doc, "2.4) การตรวจสอบและอนุมัติการบันทึกพฤติกรรม (Approve Records)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 17, "หน้าจอตรวจสอบและอนุมัติการบันทึกพฤติกรรม", width_in=5.8, height_in=2.6, route_hint="/discipline/behavior-records")
    add_styled_paragraph(doc, "จากภาพที่ 17 รายการพฤติกรรมที่ครูส่งเข้ามาจะแสดงสถานะ \"รออนุมัติ\" ฝ่ายปกครองสามารถตรวจสอบหลักฐานรูปถ่าย แล้วกดปุ่ม \"อนุมัติ\" เพื่อยืนยันการตัดคะแนน หรือกดปุ่ม \"ปฏิเสธ\" หากไม่เข้าข่าย", font_size=13, space_before=4, space_after=10)

    # 2.5 Appeals
    add_styled_paragraph(doc, "2.5) การพิจารณาคำร้องขออุทธรณ์คะแนน (Review Appeals)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 18, "หน้าจอพิจารณาคำร้องขออุทธรณ์คะแนนของนักเรียน", width_in=5.8, height_in=2.6, route_hint="/discipline/appeals")
    add_styled_paragraph(doc, "จากภาพที่ 18 ฝ่ายปกครองตรวจสอบเอกสารชี้แจงและหลักฐานที่นักเรียนแนบมา หากเห็นสมควรให้คลิกปุ่ม \"อนุมัติการอุทธรณ์\" ระบบจะคืนคะแนนให้นักเรียนทันที หรือคลิกปุ่ม \"ปฏิเสธคำร้อง\"", font_size=13, space_before=4, space_after=10)

    # 2.6 Informant Reports
    add_styled_paragraph(doc, "2.6) การจัดการเรื่องแจ้งเบาะแสพฤติกรรม (Informant Reports)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 19, "หน้าจอแสดงรายการแจ้งเบาะแสพฤติกรรม", width_in=5.8, height_in=2.6, route_hint="/discipline/informant-reports")
    add_styled_paragraph(doc, "จากภาพที่ 19 ฝ่ายปกครองเปิดดูเรื่องที่นักเรียนส่งแจ้งเบาะแสการกระทำผิด (เช่น สูบบุหรี่ หรือหนีเรียน) เพื่อดำเนินการสอบสวน บันทึกผล และคลิกปุ่ม \"รับเรื่อง\" หรือ \"ปิดเรื่อง\"", font_size=13, space_before=4, space_after=10)

    # 2.7 Reports & Export
    add_styled_paragraph(doc, "2.7) หน้ารายงานสรุปพฤติกรรมและการส่งออกข้อมูล (Reports & Export)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 20, "หน้าจอรายงานสรุปพฤติกรรมและการส่งออกข้อมูล", width_in=5.8, height_in=2.6, route_hint="/discipline/behavior-report")
    add_styled_paragraph(doc, "จากภาพที่ 20 สามารถเลือกช่วงวันที่, ระดับชั้น และคลิกปุ่ม \"พิมพ์รายงาน (PDF)\" เพื่อพิมพ์รายงานความประพฤติรายห้อง หรือคลิกปุ่ม \"ส่งออก Excel\"", font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 6. GROUP 3: TEACHER (ครูผู้สอน/ที่ปรึกษา)
    # ==========================================
    add_styled_paragraph(doc, "3. กลุ่มของครูผู้สอนและครูที่ปรึกษา (Teacher)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "ครูผู้สอนและครูที่ปรึกษาเป็นผู้ดูแลนักเรียนอย่างใกล้ชิด ทำหน้าที่เช็คชื่อประจำวัน บันทึกพฤติกรรม สแกนละหมาด และพูดคุยกับผู้ปกครอง ดังนี้:", font_size=13, space_before=0, space_after=8)

    # 3.1 Teacher Dashboard & Classroom
    add_styled_paragraph(doc, "3.1) หน้าแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา (My Classroom)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_placeholder(doc, 21, "หน้าจอแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา", width_in=5.8, height_in=2.6, route_hint="/teacher/dashboard")
    add_styled_paragraph(doc, "จากภาพที่ 21 ครูที่ปรึกษาดูภาพรวมสถิติของนักเรียนในห้องที่รับผิดชอบ คะแนนเฉลี่ยประจำห้อง และรายชื่อนักเรียนที่ต้องเฝ้าระวังพฤติกรรม", font_size=13, space_before=4, space_after=10)

    # 3.2 Attendance
    add_styled_paragraph(doc, "3.2) การบันทึกเวลาเรียนและการเช็คชื่อประจำวัน (Daily Attendance)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 22, "หน้าจอเช็คชื่อการเข้าเรียนและเข้าแถวประจำวัน", width_in=5.8, height_in=2.6, route_hint="/teacher/attendance")
    add_styled_paragraph(doc, "จากภาพที่ 22 ครูทำการเช็คชื่อนักเรียนตามห้องเรียน ทำเครื่องหมายเลือกสถานะ: มา, สาย, ขาด, ลา (มีปุ่มลัด \"มาทั้งหมด\") จากนั้นคลิกปุ่ม \"บันทึกการเช็คชื่อ\"", font_size=13, space_before=4, space_after=10)

    # 3.3 Log Behavior Record
    add_styled_paragraph(doc, "3.3) การบันทึกคะแนนพฤติกรรมนักเรียน (Log Behavior Record)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 23, "หน้าจอแบบฟอร์มบันทึกพฤติกรรมนักเรียน", width_in=5.8, height_in=2.6, route_hint="/teacher/behavior-records/create")
    add_styled_paragraph(doc, "จากภาพที่ 23 เมื่อพบน้องนักเรียนทำผิดหรือทำความดี ครูค้นหาชื่อนักเรียน, เลือกข้อกฎระเบียบ, ระบุสถานที่/เหตุการณ์, แนบรูปหลักฐาน แล้วกดปุ่ม \"ส่งบันทึกไปยังฝ่ายปกครอง\"", font_size=13, space_before=4, space_after=10)

    # 3.4 Prayer Scanner
    add_styled_paragraph(doc, "3.4) การสแกนเช็คชื่อละหมาด (Prayer Scanner)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 24, "หน้าจอเครื่องสแกน QR Code สำหรับเช็คชื่อละหมาด", width_in=5.8, height_in=2.6, route_hint="/prayer/scan")
    add_styled_paragraph(doc, "จากภาพที่ 24 ครูเปิดกล้องจากมือถือ เลือกรอบเวลาละหมาด (เช่น ซุฮรี หรือ อัศรี) แล้วนำไปสแกน QR Code บนบัตรนักเรียนเพื่อเช็คชื่อละหมาดทันที", font_size=13, space_before=4, space_after=10)

    # 3.5 Teacher Messages
    add_styled_paragraph(doc, "3.5) ระบบกล่องข้อความติดต่อสื่อสาร (Teacher Messages)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 25, "หน้าจอกล่องข้อความพูดคุยกับผู้ปกครอง", width_in=5.8, height_in=2.6, route_hint="/teacher/messages")
    add_styled_paragraph(doc, "จากภาพที่ 25 ครูรับและตอบข้อความแชทจากผู้ปกครอง ตรวจดูใบลาหรือเอกสารที่ส่งมา เพื่อให้การประสานงานระหว่างโรงเรียนกับครอบครัวเป็นไปอย่างมีประสิทธิภาพ", font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 7. GROUP 4: STUDENT (นักเรียน)
    # ==========================================
    add_styled_paragraph(doc, "4. กลุ่มของนักเรียน (Student)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "นักเรียนสามารถเข้ามาสอดส่องและตรวจสอบสถานะความประพฤติของตนเอง ยื่นคำร้องขออุทธรณ์ เปิด QR ละหมาด และแจ้งเบาะแส มีรายละเอียดดังนี้:", font_size=13, space_before=0, space_after=8)

    # 4.1 Dashboard
    add_styled_paragraph(doc, "4.1) หน้าแดชบอร์ดนักเรียนและคะแนนคงเหลือ (Student Dashboard)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_placeholder(doc, 26, "หน้าแดชบอร์ดแสดงคะแนนความประพฤตินักเรียน", width_in=5.8, height_in=2.6, route_hint="/student/dashboard")
    add_styled_paragraph(doc, "จากภาพที่ 26 นักเรียนสามารถดูคะแนนความประพฤติคงเหลือของตนเอง (เริ่มต้น 100 คะแนน) สถิติการมาเรียน การขาดแถว และรายการถูกตัดคะแนนล่าสุด", font_size=13, space_before=4, space_after=10)

    # 4.2 Appeals
    add_styled_paragraph(doc, "4.2) การยื่นคำร้องขออุทธรณ์คะแนน (Submit Appeal)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 27, "หน้าจอแบบฟอร์มยื่นคำร้องขออุทธรณ์คะแนน", width_in=5.8, height_in=2.6, route_hint="/student/appeals/create")
    add_styled_paragraph(doc, "จากภาพที่ 27 หากนักเรียนถูกตัดคะแนนโดยมีเหตุสุดวิสัย สามารถเลือกรายการที่ถูกตัด กรอกคำชี้แจงข้อเท็จจริง แนบใบรับรองแพทย์หรือหลักฐาน แล้วคลิกปุ่ม \"ส่งคำร้องอุทธรณ์\"", font_size=13, space_before=4, space_after=10)

    # 4.3 Prayer QR
    add_styled_paragraph(doc, "4.3) การเปิดบัตร QR Code ประจำตัวเพื่อเช็คชื่อละหมาด (Prayer QR Check-in)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 28, "หน้าจอแสดง QR Code ประจำตัวสำหรับเช็คชื่อละหมาด", width_in=5.8, height_in=2.6, route_hint="/student/prayer-checkin")
    add_styled_paragraph(doc, "จากภาพที่ 28 นักเรียนเปิดหน้านี้ผ่านโทรศัพท์มือถือ เพื่อแสดง QR Code ประจำตัวให้ครูสแกน ณ จุดเช็คชื่อละหมาด พร้อมดูประวัติการละหมาดของวันนี้ได้ทันที", font_size=13, space_before=4, space_after=10)

    # 4.4 Informant Reports
    add_styled_paragraph(doc, "4.4) การส่งเรื่องแจ้งเบาะแสพฤติกรรมแบบไม่ระบุตัวตน (Informant Report)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 29, "หน้าจอแจ้งเบาะแสพฤติกรรมที่ไม่เหมาะสม", width_in=5.8, height_in=2.6, route_hint="/student/informant-reports/create")
    add_styled_paragraph(doc, "จากภาพที่ 29 นักเรียนสามารถร่วมดูแลสังคมในโรงเรียน โดยแจ้งเบาะแสการกระทำผิด และเลือกได้ว่าจะ \"เปิดเผยชื่อ\" หรือ \"แจ้งแบบปกปิดชื่อ (Anonymous)\" เพื่อความปลอดภัย", font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 8. GROUP 5: PARENT (ผู้ปกครอง)
    # ==========================================
    add_styled_paragraph(doc, "5. กลุ่มของผู้ปกครอง (Parent / Guardian)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "ผู้ปกครองสามารถเข้ามาติดตามพฤติกรรมและเวลาเรียนของบุตรหลานผ่านระบบได้อย่างใกล้ชิด มีรายละเอียดดังนี้:", font_size=13, space_before=0, space_after=8)

    # 5.1 Parent Dashboard & Switch
    add_styled_paragraph(doc, "5.1) หน้าแดชบอร์ดผู้ปกครองและการสลับดูข้อมูลบุตรหลาน", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_placeholder(doc, 30, "หน้าจอแดชบอร์ดสำหรับผู้ปกครอง", width_in=5.8, height_in=2.6, route_hint="/parent/dashboard")
    add_styled_paragraph(doc, "จากภาพที่ 30 ผู้ปกครองดูคะแนนความประพฤติคงเหลือของบุตรหลาน หากมีบุตรหลานศึกษาอยู่หลายคน สามารถคลิกปุ่ม \"สลับดูบุตรหลาน\" เพื่อเลือกดูข้อมูลของแต่ละคนได้", font_size=13, space_before=4, space_after=10)

    # 5.2 Behavior & Attendance History
    add_styled_paragraph(doc, "5.2) การตรวจสอบคะแนนพฤติกรรมและประวัติการทำทัณฑ์บน", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 31, "หน้าจอประวัติพฤติกรรมและการเช็คชื่อบุตรหลาน", width_in=5.8, height_in=2.6, route_hint="/parent/behavior-records")
    add_styled_paragraph(doc, "จากภาพที่ 31 แสดงรายละเอียดการถูกตัดคะแนนย้อนหลัง วันที่เกิดเหตุ และประวัติการขาด-ลา-มาสาย เพื่อให้ผู้ปกครองรับทราบและร่วมตักเตือนบุตรหลาน", font_size=13, space_before=4, space_after=10)

    # 5.3 Messaging
    add_styled_paragraph(doc, "5.3) การส่งข้อความติดต่อครูที่ปรึกษาหรือฝ่ายปกครอง", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_placeholder(doc, 32, "หน้าจอส่งข้อความพูดคุยกับครูที่ปรึกษา", width_in=5.8, height_in=2.6, route_hint="/parent/messages")
    add_styled_paragraph(doc, "จากภาพที่ 32 ผู้ปกครองสามารถพิมพ์ข้อความสอบถามปัญหา หรือส่งเอกสารใบลาผ่านระบบแชทตรงถึงครูที่ปรึกษาได้อย่างสะดวกรวดเร็ว", font_size=13, space_before=4, space_after=14)

    doc.add_page_break()

    # ==========================================
    # 9. APPENDIX: SCREENSHOT CHECKLIST TABLE
    # ==========================================
    add_styled_paragraph(doc, "ภาคผนวก: ตารางสรุปภาพหน้าจอและ URL สำหรับแคปภาพลงเล่ม", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=8)
    add_styled_paragraph(doc, "ตารางสรุปรายการภาพหน้าจอทั้งหมด 32 ภาพ พร้อมเส้นทาง URL ในระบบจริง เพื่อความสะดวกในการเปิดระบบและจับภาพหน้าจอ (Screenshot) มาวางแทนที่กรอบในเอกสารคู่มือ:", font_size=13, space_before=0, space_after=8)

    check_list = [
        ("1", "หน้าจอแสดงผลการเข้าสู่ระบบ (Login)", "/login", "ทุกกลุ่ม"),
        ("2", "หน้าแดชบอร์ดสำหรับผู้ดูแลระบบ", "/admin/dashboard", "ผู้ดูแลระบบ"),
        ("3", "หน้าจอแสดงรายการข้อมูลผู้ใช้งาน", "/admin/users", "ผู้ดูแลระบบ"),
        ("4", "หน้าจอแบบฟอร์มเพิ่มข้อมูลผู้ใช้งาน", "/admin/users/create", "ผู้ดูแลระบบ"),
        ("5", "หน้าจอแสดงรายการข้อมูลนักเรียน", "/admin/students", "ผู้ดูแลระบบ"),
        ("6", "หน้าจอแบบฟอร์มเพิ่มข้อมูลนักเรียน", "/admin/students/create", "ผู้ดูแลระบบ"),
        ("7", "หน้าจอแสดงบัตรประจำตัวนักเรียนและ QR Code", "/admin/students/{id}/card", "ผู้ดูแลระบบ"),
        ("8", "หน้าจอนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel", "/admin/students/import", "ผู้ดูแลระบบ"),
        ("9", "หน้าจอจัดการข้อมูลผู้ปกครอง", "/admin/students/{id}/parents", "ผู้ดูแลระบบ"),
        ("10", "หน้าจอแสดงรายการข้อมูลครูและห้องเรียน", "/admin/teachers", "ผู้ดูแลระบบ"),
        ("11", "หน้าจอตารางกำหนดสิทธิ์การใช้งานบทบาท", "/admin/permissions", "ผู้ดูแลระบบ"),
        ("12", "หน้าจอจัดการปีการศึกษาและภาคเรียน", "/admin/semesters", "ผู้ดูแลระบบ"),
        ("13", "หน้าแดชบอร์ดฝ่ายปกครอง", "/discipline/dashboard", "ฝ่ายปกครอง"),
        ("14", "หน้าจอติดตามนักเรียนกลุ่มเสี่ยง (Risk Students)", "/discipline/risk-students", "ฝ่ายปกครอง"),
        ("15", "หน้าจอแสดงรายการเกณฑ์กฎระเบียบวินัย", "/discipline/behavior-rules", "ฝ่ายปกครอง"),
        ("16", "หน้าจอแบบฟอร์มเพิ่มกฎระเบียบและคะแนน", "/discipline/behavior-rules/create", "ฝ่ายปกครอง"),
        ("17", "หน้าจอตรวจสอบและอนุมัติบันทึกพฤติกรรม", "/discipline/behavior-records", "ฝ่ายปกครอง"),
        ("18", "หน้าจอพิจารณาคำร้องอุทธรณ์คะแนน", "/discipline/appeals", "ฝ่ายปกครอง"),
        ("19", "หน้าจอรายการแจ้งเบาะแสพฤติกรรม", "/discipline/informant-reports", "ฝ่ายปกครอง"),
        ("20", "หน้าจอรายงานสรุปพฤติกรรมและการส่งออกข้อมูล", "/discipline/behavior-report", "ฝ่ายปกครอง"),
        ("21", "หน้าแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา", "/teacher/dashboard", "ครู"),
        ("22", "หน้าจอเช็คชื่อการเข้าเรียนประจำวัน", "/teacher/attendance", "ครู"),
        ("23", "หน้าจอแบบฟอร์มบันทึกพฤติกรรมนักเรียน", "/teacher/behavior-records/create", "ครู"),
        ("24", "หน้าจอเครื่องสแกน QR Code เช็คชื่อละหมาด", "/prayer/scan", "ครู"),
        ("25", "หน้าจอกล่องข้อความติดต่อสื่อสารของครู", "/teacher/messages", "ครู"),
        ("26", "หน้าแดชบอร์ดแสดงคะแนนความประพฤตินักเรียน", "/student/dashboard", "นักเรียน"),
        ("27", "หน้าจอแบบฟอร์มยื่นคำร้องขออุทธรณ์คะแนน", "/student/appeals/create", "นักเรียน"),
        ("28", "หน้าจอแสดง QR Code ประจำตัวสำหรับเช็คชื่อละหมาด", "/student/prayer-checkin", "นักเรียน"),
        ("29", "หน้าจอแจ้งเบาะแสพฤติกรรมที่ไม่เหมาะสม", "/student/informant-reports/create", "นักเรียน"),
        ("30", "หน้าแดชบอร์ดผู้ปกครองและการสลับบุตรหลาน", "/parent/dashboard", "ผู้ปกครอง"),
        ("31", "หน้าจอประวัติพฤติกรรมและการเช็คชื่อบุตรหลาน", "/parent/behavior-records", "ผู้ปกครอง"),
        ("32", "หน้าจอส่งข้อความติดต่อครูที่ปรึกษา", "/parent/messages", "ผู้ปกครอง"),
    ]

    chk_tbl = doc.add_table(rows=len(check_list) + 1, cols=4)
    chk_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    c_widths = [Inches(1.0), Inches(2.7), Inches(2.0), Inches(1.3)]
    
    chk_headers = ["ภาพที่", "รายละเอียดหน้าจอ", "เมนู / Route ในระบบ", "สิทธิ์ผู้ใช้งาน"]
    for j, h in enumerate(chk_headers):
        c = chk_tbl.cell(0, j)
        c.width = c_widths[j]
        set_cell_shading(c, "1B365D")
        set_cell_margins(c, top=120, bottom=120, left=100, right=100)
        set_cell_borders(c, top="1B365D", bottom="1B365D", left="1B365D", right="1B365D")
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        add_run_to_p(p, h, font_size=12, bold=True, color_rgb=(255, 255, 255))
        
    for i, row in enumerate(check_list):
        for j, val in enumerate(row):
            c = chk_tbl.cell(i + 1, j)
            c.width = c_widths[j]
            bg = "F9FBFD" if i % 2 == 0 else "FFFFFF"
            set_cell_shading(c, bg)
            set_cell_margins(c, top=80, bottom=80, left=80, right=80)
            set_cell_borders(c, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC")
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [0, 3] else WD_ALIGN_PARAGRAPH.LEFT
            add_run_to_p(p, val, font_size=11, bold=(j == 0), color_rgb=(0, 51, 102) if j == 0 else (40, 40, 40))

    # Output file paths
    out_dir = os.path.abspath("docs")
    os.makedirs(out_dir, exist_ok=True)
    
    target_names = [
        "User_Manual_With_Logos.docx",
        "User_Manual_Student_Discipline_System_Latest.docx",
        "User_Manual_Student_Discipline_System_Complete.docx",
        "User_Manual_Student_Discipline_System.docx"
    ]
    
    saved_paths = []
    for fname in target_names:
        p = os.path.join(out_dir, fname)
        try:
            doc.save(p)
            saved_paths.append(p)
            print(f"Successfully saved: {p}")
        except Exception as e:
            print(f"Could not save to {fname} (likely locked by Word): {e}")
            
    if not saved_paths:
        fallback_path = os.path.join(out_dir, f"User_Manual_{int(time.time())}.docx")
        doc.save(fallback_path)
        print(f"Saved fallback to: {fallback_path}")
        return fallback_path
    return saved_paths[0]

if __name__ == "__main__":
    build_user_manual()
