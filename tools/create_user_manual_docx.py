# -*- coding: utf-8 -*-
"""
Script: create_user_manual_docx.py
Generates the official User Manual (คู่มือการใช้งานระบบ) in Microsoft Word (.docx) format
matching the exact design and styling of the Yala Rajabhat University (YRU) IT Department standard.

Design features:
1. Cover page with YRU seal, blue title, composite showcase banner, author info, IT department logo, and geometric accent.
2. Running header with dual seals (University + Department), right-aligned manual/system title, and top-right orange signature block.
3. Running footer with Thai page numbering ("หน้า X").
4. Formatted Table of Contents (สารบัญ) grouped by user roles.
5. High-resolution screenshots centered in neat frames with figure captions ("ภาพที่ X ...").
6. Inline graphic button badges inside explanation sentences ([เข้าสู่ระบบ], [+ เพิ่มข้อมูล], [✏️], [🗑️], [👁️], [บันทึก], [กลับ], etc.).
"""

import os
import sys
import docx
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml, OxmlElement
from docx.oxml.ns import nsdecls, qn
from PIL import Image, ImageDraw, ImageFont
import cv2
import numpy as np

BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))
DOCS_DIR = os.path.join(BASE_DIR, "docs")
IMAGES_DIR = os.path.join(DOCS_DIR, "manual_images")
BUTTONS_DIR = os.path.join(IMAGES_DIR, "buttons")
OUTPUT_FILE = os.path.join(DOCS_DIR, "User_Manual_Student_Discipline_System.docx")
OUTPUT_FILE_ALT = os.path.join(DOCS_DIR, "User_Manual_With_Logos.docx")

# Ensure buttons directory exists
os.makedirs(BUTTONS_DIR, exist_ok=True)

# -------------------------------------------------------------
# 1. HELPER FUNCTIONS: XML SHADING, BORDERS, CELL FORMATTING
# -------------------------------------------------------------
def set_cell_shading(cell, color_hex):
    """Set background color of a table cell."""
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{color_hex}"/>')
    cell._tc.get_or_add_tcPr().append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=140, right=140):
    """Set inner cell padding in dxa."""
    tcMar = parse_xml(
        f'<w:tcMar {nsdecls("w")}>\n'
        f'  <w:top w:w="{top}" w:type="dxa"/>\n'
        f'  <w:bottom w:w="{bottom}" w:type="dxa"/>\n'
        f'  <w:left w:w="{left}" w:type="dxa"/>\n'
        f'  <w:right w:w="{right}" w:type="dxa"/>\n'
        f'</w:tcMar>'
    )
    cell._tc.get_or_add_tcPr().append(tcMar)

def set_cell_borders(cell, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC", sz="4"):
    """Set custom borders on a table cell."""
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
    cell._tc.get_or_add_tcPr().append(tcBorders)

def add_styled_paragraph(doc, text="", font_size=14, bold=False, italic=False, color_rgb=(0,0,0), 
                         align=WD_ALIGN_PARAGRAPH.LEFT, space_before=0, space_after=6, font_name="TH Sarabun New"):
    p = doc.add_paragraph()
    p.alignment = align
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if text:
        add_run_to_p(p, text, font_size=font_size, bold=bold, italic=italic, color_rgb=color_rgb, font_name=font_name)
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

# -------------------------------------------------------------
# 2. INLINE BUTTON & ICON BADGE GENERATION (USING PILLOW)
# -------------------------------------------------------------
def ensure_button_badges():
    """Generates retina-crisp button and icon badges for inline embedding."""
    font_path = "C:/Windows/Fonts/tahomabd.ttf"
    if not os.path.exists(font_path):
        font_path = "C:/Windows/Fonts/tahoma.ttf"
    
    scale = 2
    f_size = int(13 * scale)
    try:
        font = ImageFont.truetype(font_path, f_size)
    except:
        font = ImageFont.load_default()

    def make_btn(name, text, bg_color, text_color=(255,255,255), height=26, radius=4, pad_x=10):
        h = height * scale
        r = radius * scale
        px = pad_x * scale
        dummy = Image.new('RGBA', (1, 1))
        d_dummy = ImageDraw.Draw(dummy)
        bbox = d_dummy.textbbox((0, 0), text, font=font)
        tw = bbox[2] - bbox[0]
        th = bbox[3] - bbox[1]
        w = tw + px * 2
        img = Image.new('RGBA', (w, h), (0, 0, 0, 0))
        d = ImageDraw.Draw(img)
        d.rounded_rectangle([0, 0, w-1, h-1], radius=r, fill=bg_color)
        x = (w - tw) // 2 - bbox[0]
        y = (h - th) // 2 - bbox[1] - (2 * scale)
        d.text((x, y), text, font=font, fill=text_color)
        img.save(os.path.join(BUTTONS_DIR, f"{name}.png"))

    def make_icon(name, icon_type, bg_color, size=26, radius=4):
        s = size * scale
        r = radius * scale
        img = Image.new('RGBA', (s, s), (0, 0, 0, 0))
        d = ImageDraw.Draw(img)
        d.rounded_rectangle([0, 0, s-1, s-1], radius=r, fill=bg_color)
        c = s // 2
        if icon_type == 'view': # Eye icon
            d.ellipse([c-13*scale, c-7*scale, c+13*scale, c+7*scale], outline=(255,255,255), width=2*scale)
            d.ellipse([c-4*scale, c-4*scale, c+4*scale, c+4*scale], fill=(255,255,255))
        elif icon_type == 'edit': # Pencil icon
            d.line([c-7*scale, c+7*scale, c+6*scale, c-6*scale], fill=(40,40,40), width=4*scale)
            d.polygon([(c-10*scale, c+10*scale), (c-9*scale, c+3*scale), (c-3*scale, c+9*scale)], fill=(40,40,40))
            d.polygon([(c+5*scale, c-5*scale), (c+9*scale, c-9*scale), (c+11*scale, c-7*scale), (c+7*scale, c-3*scale)], fill=(40,40,40))
        elif icon_type == 'delete': # Trash icon
            d.line([c-9*scale, c-5*scale, c+9*scale, c-5*scale], fill=(255,255,255), width=2*scale)
            d.line([c-3*scale, c-7*scale, c+3*scale, c-7*scale], fill=(255,255,255), width=2*scale)
            d.polygon([(c-6*scale, c-3*scale), (c+6*scale, c-3*scale), (c+4*scale, c+8*scale), (c-4*scale, c+8*scale)], outline=(255,255,255), fill=None, width=2*scale)
            d.line([c-2*scale, c-1*scale, c-2*scale, c+5*scale], fill=(255,255,255), width=1*scale)
            d.line([c+2*scale, c-1*scale, c+2*scale, c+5*scale], fill=(255,255,255), width=1*scale)
        elif icon_type == 'add': # Plus icon
            d.line([c-7*scale, c, c+7*scale, c], fill=(255,255,255), width=3*scale)
            d.line([c, c-7*scale, c, c+7*scale], fill=(255,255,255), width=3*scale)
        img.save(os.path.join(BUTTONS_DIR, f"{name}.png"))

    # Text buttons
    make_btn('btn_login', 'เข้าสู่ระบบ', (13, 148, 136))
    make_btn('btn_add_data', 'เพิ่มข้อมูล +', (40, 167, 69))
    make_btn('btn_add_user', '+ เพิ่มผู้ใช้งาน', (40, 167, 69))
    make_btn('btn_add_rule', '+ เพิ่มกฎระเบียบ', (40, 167, 69))
    make_btn('btn_save', 'บันทึก', (23, 162, 184))
    make_btn('btn_save_rule', 'บันทึกเกณฑ์', (23, 162, 184))
    make_btn('btn_back', 'กลับ', (220, 53, 69))
    make_btn('btn_cancel', 'ยกเลิก', (108, 117, 125))
    make_btn('btn_print_card', 'พิมพ์บัตร', (23, 162, 184))
    make_btn('btn_print_pdf', 'พิมพ์รายงาน (PDF)', (23, 162, 184))
    make_btn('btn_export_excel', 'ส่งออก Excel', (40, 167, 69))
    make_btn('btn_approve', 'อนุมัติ', (40, 167, 69))
    make_btn('btn_reject', 'ปฏิเสธ', (220, 53, 69))
    make_btn('btn_send_appeal', 'ส่งคำร้องอุทธรณ์', (255, 153, 0))
    make_btn('btn_send_record', 'ส่งบันทึกไปยังฝ่ายปกครอง', (13, 110, 253))
    make_btn('btn_checkin_all', 'มาทั้งหมด', (40, 167, 69))
    make_btn('btn_qr', 'แสดง QR Code', (13, 110, 253))
    make_btn('btn_set_active', 'กำหนดเป็นภาคเรียนปัจจุบัน', (40, 167, 69))
    make_btn('btn_send_informant', 'ส่งรายงานเบาะแส', (220, 53, 69))
    make_btn('btn_switch_child', 'สลับดูบุตรหลาน', (13, 110, 253))
    make_btn('btn_monthly_stats', 'สรุปสถิติแต่ละเดือน (ร้อยละ %)', (79, 70, 229))

    # Icon buttons
    make_icon('btn_icon_view', 'view', (23, 162, 184))
    make_icon('btn_icon_edit', 'edit', (255, 193, 7))
    make_icon('btn_icon_delete', 'delete', (220, 53, 69))
    make_icon('btn_icon_add', 'add', (40, 167, 69))

def ensure_cover_showcase():
    """Builds a composite hero showcase banner for the cover page if not already created."""
    showcase_path = os.path.join(IMAGES_DIR, "cover_showcase.png")
    if os.path.exists(showcase_path):
        return showcase_path
    
    img1_path = os.path.join(IMAGES_DIR, "fig_02.png")
    img2_path = os.path.join(IMAGES_DIR, "fig_07.png")
    img3_path = os.path.join(IMAGES_DIR, "fig_28.png")
    if not (os.path.exists(img1_path) and os.path.exists(img2_path) and os.path.exists(img3_path)):
        return None
        
    w, h = 1600, 750
    canvas = Image.new('RGB', (w, h), (245, 247, 250))
    img1 = Image.open(img1_path)
    img2 = Image.open(img2_path)
    img3 = Image.open(img3_path)
    
    # Left main: Dashboard
    img1_c = img1.crop((160, 100, 1760, 1100)).resize((900, 750), Image.Resampling.LANCZOS)
    canvas.paste(img1_c, (0, 0))
    # Right top: ID Card
    img2_c = img2.crop((300, 150, 1600, 1000)).resize((700, 370), Image.Resampling.LANCZOS)
    canvas.paste(img2_c, (900, 0))
    # Right bottom: Student QR
    img3_c = img3.crop((400, 150, 1500, 1000)).resize((700, 380), Image.Resampling.LANCZOS)
    canvas.paste(img3_c, (900, 370))
    
    # White separators
    d = ImageDraw.Draw(canvas)
    d.line([(900, 0), (900, h)], fill=(255, 255, 255), width=6)
    d.line([(900, 370), (w, 370)], fill=(255, 255, 255), width=6)
    d.rectangle([(0, 0), (w-1, h-1)], outline=(180, 200, 220), width=4)
    canvas.save(showcase_path, quality=95)
    return showcase_path

def ensure_transparent_logos():
    """Converts university and department logos to transparent PNG with smooth anti-aliasing."""
    def process_logo(jpg_name, png_name, bg_color_bgr, pad=12, diff=22):
        in_path = os.path.join(IMAGES_DIR, jpg_name)
        out_path = os.path.join(IMAGES_DIR, png_name)
        if not os.path.exists(in_path):
            return out_path
        img = cv2.imread(in_path)
        h, w, _ = img.shape
        padded = cv2.copyMakeBorder(img, pad, pad, pad, pad, cv2.BORDER_CONSTANT, value=bg_color_bgr)
        hp, wp, _ = padded.shape
        mask = np.zeros((hp+2, wp+2), np.uint8)
        flags = 4 | (255 << 8) | cv2.FLOODFILL_MASK_ONLY | cv2.FLOODFILL_FIXED_RANGE
        cv2.floodFill(padded, mask, (0, 0), (0,0,0), (diff, diff, diff), (diff, diff, diff), flags)
        bg_mask = mask[1+pad:1+pad+h, 1+pad:1+pad+w]
        fg_binary = (bg_mask == 0).astype(np.float32)
        alpha = cv2.GaussianBlur(fg_binary, (3, 3), 0.7)
        img_float = img.astype(np.float32)
        bg_arr = np.array(bg_color_bgr, dtype=np.float32).reshape(1, 1, 3)
        alpha_3d = np.repeat(alpha[:, :, np.newaxis], 3, axis=2)
        clean_bgr = np.where(
            alpha_3d > 0.05,
            (img_float - (1.0 - alpha_3d) * bg_arr) / np.maximum(alpha_3d, 0.05),
            img_float
        )
        clean_bgr = np.clip(clean_bgr, 0, 255).astype(np.uint8)
        alpha_uint8 = np.clip(alpha * 255.0, 0, 255).astype(np.uint8)
        b, g, r = cv2.split(clean_bgr)
        rgba = cv2.merge([b, g, r, alpha_uint8])
        cv2.imwrite(out_path, rgba)
        return out_path

    u_png = process_logo('logo_university.jpg', 'logo_university.png', [255, 255, 255], diff=20)
    d_png = process_logo('logo_department.jpg', 'logo_department.png', [245, 245, 245], diff=22)
    return u_png, d_png

# -------------------------------------------------------------
# 3. CONTENT BUILDER WITH INLINE BADGES
# -------------------------------------------------------------
def add_explanation(doc, items, font_size=13, space_before=3, space_after=8):
    """
    Adds a paragraph with text and inline button/icon badges.
    `items` is a list of strings or tuples:
    - str: normal text
    - ('btn', 'btn_name'): inline text button image
    - ('icon', 'icon_name'): inline square icon button image
    """
    p = doc.add_paragraph()
    p.paragraph_format.space_before = Pt(space_before)
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.25
    
    for item in items:
        if isinstance(item, str):
            add_run_to_p(p, item, font_size=font_size, color_rgb=(40, 40, 40))
        elif isinstance(item, tuple):
            kind, name = item
            b_path = os.path.join(BUTTONS_DIR, f"{name}.png")
            if os.path.exists(b_path):
                r = p.add_run(" ")
                r_img = p.add_run()
                h_pt = Pt(13.5) if kind == 'btn' else Pt(13.0)
                r_img.add_picture(b_path, height=h_pt)
                p.add_run(" ")
            else:
                add_run_to_p(p, f"[{name}]", font_size=font_size, bold=True, color_rgb=(0, 102, 153))
    return p

def add_image_box(doc, fig_num, title, width_in=5.6, img_filename=None):
    """Inserts a screenshot in a clean bordered frame with centered caption below."""
    if img_filename is None:
        img_filename = f"fig_{fig_num:02d}.png"
    img_path = os.path.join(IMAGES_DIR, img_filename)
    
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.cell(0, 0)
    cell.width = Inches(width_in)
    
    # Border & padding
    set_cell_shading(cell, "FFFFFF")
    set_cell_margins(cell, top=60, bottom=60, left=60, right=60)
    set_cell_borders(cell, top="B0BEC5", bottom="B0BEC5", left="B0BEC5", right="B0BEC5", sz="6")
    
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(2)
    p.paragraph_format.space_after = Pt(2)
    
    if os.path.exists(img_path):
        run = p.add_run()
        run.add_picture(img_path, width=Inches(width_in - 0.2))
    else:
        add_run_to_p(p, f"\n[ รูปภาพหน้าจอที่ {fig_num} : {title} ]\n", font_size=13, bold=True, color_rgb=(100, 100, 100))
        
    cap_p = add_styled_paragraph(doc, f"ภาพที่ {fig_num} ", font_size=13, bold=True, color_rgb=(0, 51, 102),
                                 align=WD_ALIGN_PARAGRAPH.CENTER, space_before=4, space_after=4)
    add_run_to_p(cap_p, title, font_size=13, bold=False, color_rgb=(60, 60, 60))
    return table

# -------------------------------------------------------------
# 4. MAIN BUILD PROCESS
# -------------------------------------------------------------
def build_full_user_manual():
    print("Building full User Manual (.docx)...")
    ensure_button_badges()
    ensure_transparent_logos()
    cover_showcase_path = ensure_cover_showcase()
    
    doc = docx.Document()
    
    # Standard A4 Margins
    section = doc.sections[0]
    section.top_margin = Cm(2.2)
    section.bottom_margin = Cm(2.2)
    section.left_margin = Cm(2.2)
    section.right_margin = Cm(2.2)
    section.page_width = Cm(21.0)
    section.page_height = Cm(29.7)
    
    # First page header/footer disabled for cover
    section.different_first_page_header_footer = True
    
    # ---------------------------------------------------------
    # RUNNING HEADER (Logos on Left, Title in Middle, Orange Block on Right)
    # ---------------------------------------------------------
    header = section.header
    p_orig = header.paragraphs[0]
    p_orig.text = "" # Clear default paragraph
    
    hdr_tbl = header.add_table(rows=1, cols=3, width=Inches(6.4))
    hdr_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    
    c_logo, c_txt, c_orange = hdr_tbl.cell(0, 0), hdr_tbl.cell(0, 1), hdr_tbl.cell(0, 2)
    c_logo.width = Inches(1.8)
    c_txt.width = Inches(4.0)
    c_orange.width = Inches(0.6)
    
    # Cell 0: Both Logos (YRU and IT)
    p0 = c_logo.paragraphs[0]
    p0.alignment = WD_ALIGN_PARAGRAPH.LEFT
    p0.paragraph_format.space_before = Pt(0)
    p0.paragraph_format.space_after = Pt(0)
    uni_logo_path = os.path.join(IMAGES_DIR, "logo_university.png")
    dept_logo_path = os.path.join(IMAGES_DIR, "logo_department.png")
    if os.path.exists(uni_logo_path):
        r_u = p0.add_run()
        r_u.add_picture(uni_logo_path, height=Inches(0.55))
    if os.path.exists(dept_logo_path):
        p0.add_run("  ")
        r_d = p0.add_run()
        r_d.add_picture(dept_logo_path, height=Inches(0.55))
        
    # Cell 1: Header Text Right-Aligned
    p1 = c_txt.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p1.paragraph_format.space_before = Pt(2)
    p1.paragraph_format.space_after = Pt(0)
    p1.paragraph_format.line_spacing = 1.15
    add_run_to_p(p1, "คู่มือการใช้งาน (User Manual)\n", font_size=11, bold=True, color_rgb=(0, 51, 102))
    add_run_to_p(p1, "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน ศ.ส.", font_size=9.5, italic=False, color_rgb=(90, 90, 90))
    
    # Cell 2: Solid Orange Tab
    set_cell_shading(c_orange, "E87722")
    p2 = c_orange.paragraphs[0]
    p2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p2.paragraph_format.space_before = Pt(4)
    p2.paragraph_format.space_after = Pt(4)
    add_run_to_p(p2, " ", font_size=8)
    
    # Remove all cell borders in header table
    for c in [c_logo, c_txt, c_orange]:
        set_cell_borders(c, top=None, bottom=None, left=None, right=None)

    # ---------------------------------------------------------
    # RUNNING FOOTER (Thai Page Number)
    # ---------------------------------------------------------
    footer = section.footer
    footer_p = footer.paragraphs[0]
    footer_p.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    footer_p.paragraph_format.space_before = Pt(4)
    add_run_to_p(footer_p, "หน้า ", font_size=10, color_rgb=(120, 120, 120))
    fldSimple = parse_xml(f'<w:fldSimple {nsdecls("w")} w:instr="PAGE"/>')
    footer_p._p.append(fldSimple)

    # =========================================================
    # 1. COVER PAGE (หน้าปก)
    # =========================================================
    # Top YRU Logo
    if os.path.exists(uni_logo_path):
        p_cov_logo = doc.add_paragraph()
        p_cov_logo.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_cov_logo.paragraph_format.space_before = Pt(0)
        p_cov_logo.paragraph_format.space_after = Pt(2)
        r_c_logo = p_cov_logo.add_run()
        r_c_logo.add_picture(uni_logo_path, height=Inches(1.25))

    add_styled_paragraph(doc, "คู่มือการใช้งาน (User Manual)", font_size=25, bold=True, 
                         color_rgb=(15, 76, 129), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=4, space_after=3)
    add_styled_paragraph(doc, "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน", font_size=17, bold=True, 
                         color_rgb=(30, 30, 30), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=2, space_after=2)
    add_styled_paragraph(doc, "กรณีศึกษา โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี", font_size=14, bold=True, 
                         color_rgb=(0, 102, 153), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=2, space_after=2)
    add_styled_paragraph(doc, "Student Discipline and Behavior Tracking System\nCase Study of Sirirat Samakkhi School Pattani Province", font_size=10.5, italic=True, 
                         color_rgb=(110, 110, 110), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=0, space_after=10)

    # Hero School Image
    school_img_path = os.path.join(IMAGES_DIR, "school_front.jpg")
    cover_img_to_use = school_img_path if os.path.exists(school_img_path) else cover_showcase_path

    if cover_img_to_use and os.path.exists(cover_img_to_use):
        c_tbl = doc.add_table(rows=1, cols=1)
        c_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        c_cell = c_tbl.cell(0, 0)
        c_cell.width = Inches(5.2)
        set_cell_shading(c_cell, "FFFFFF")
        set_cell_margins(c_cell, top=40, bottom=40, left=40, right=40)
        set_cell_borders(c_cell, top="0F4C81", bottom="0F4C81", left="0F4C81", right="0F4C81", sz="8")
        cp = c_cell.paragraphs[0]
        cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        cp.paragraph_format.space_before = Pt(2)
        cp.paragraph_format.space_after = Pt(2)
        crun = cp.add_run()
        crun.add_picture(cover_img_to_use, width=Inches(4.6))

    # Authors section
    add_styled_paragraph(doc, "จัดทำโดย", font_size=16, bold=True, 
                         color_rgb=(15, 76, 129), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=12, space_after=3)
    add_styled_paragraph(doc, "นายตอริก ลือแม               รหัสนักศึกษา 406665012\n"
                              "นายบุสริน มูซอ                 รหัสนักศึกษา 406665027", 
                         font_size=13.5, color_rgb=(40, 40, 40), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=0, space_after=12)

    # Bottom IT logo & University table
    bot_tbl = doc.add_table(rows=1, cols=2)
    bot_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    c_b0, c_b1 = bot_tbl.cell(0, 0), bot_tbl.cell(0, 1)
    c_b0.width = Inches(3.6)
    c_b1.width = Inches(2.8)
    
    # Left: IT Logo + Program text
    p_b0 = c_b0.paragraphs[0]
    p_b0.alignment = WD_ALIGN_PARAGRAPH.LEFT
    p_b0.paragraph_format.space_before = Pt(0)
    p_b0.paragraph_format.space_after = Pt(0)
    if os.path.exists(dept_logo_path):
        r_it = p_b0.add_run()
        r_it.add_picture(dept_logo_path, height=Inches(0.85))
        p_b0.add_run("  ")
    r_it_txt = add_run_to_p(p_b0, "เทคโนโลยีสารสนเทศ\nInformation Technology", font_size=12, bold=True, color_rgb=(0, 102, 153))
    
    # Right: Faculty and University Info
    p_b1 = c_b1.paragraphs[0]
    p_b1.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p_b1.paragraph_format.space_before = Pt(4)
    p_b1.paragraph_format.space_after = Pt(0)
    p_b1.paragraph_format.line_spacing = 1.15
    add_run_to_p(p_b1, "คณะวิทยาศาสตร์เทคโนโลยีและการเกษตร\nมหาวิทยาลัยราชภัฏยะลา\nปีการศึกษา 2569", 
                 font_size=11, bold=False, color_rgb=(80, 80, 80))
                 
    set_cell_borders(c_b0, top=None, bottom=None, left=None, right=None)
    set_cell_borders(c_b1, top=None, bottom=None, left=None, right=None)

    doc.add_page_break()

    # =========================================================
    # 2. TABLE OF CONTENTS (สารบัญ)
    # =========================================================
    add_styled_paragraph(doc, "สารบัญ", font_size=20, bold=True, color_rgb=(0, 51, 102), align=WD_ALIGN_PARAGRAPH.CENTER, space_before=4, space_after=12)
    
    toc_data = [
        ("บทนำและข้อมูลเบื้องต้นของระบบ", "4"),
        ("     สิทธิ์การเข้าใช้งานระบบและบัญชีผู้ใช้งานทดสอบ (Default Accounts)", "4"),
        ("     หน้าเข้าสู่ระบบ (Login)", "5"),
        ("1. กลุ่มของผู้ดูแลระบบ (Admin)", "6"),
        ("     1.1) หน้าแดชบอร์ดผู้ดูแลระบบ (Admin Dashboard)", "6"),
        ("     1.2) การจัดการข้อมูลผู้ใช้งานระบบ (User Management)", "7"),
        ("     1.3) การจัดการข้อมูลนักเรียน (Student Management)", "8"),
        ("     1.4) การพิมพ์บัตรประจำตัวนักเรียนและ QR Code", "9"),
        ("     1.5) การนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel/CSV", "10"),
        ("     1.6) การจัดการข้อมูลผู้ปกครอง (Parent / Guardian)", "11"),
        ("     1.7) การจัดการข้อมูลครูและครูที่ปรึกษา (Teacher Management)", "11"),
        ("     1.8) การแสดงสิทธิ์การเข้าถึงโมดูลของแต่ละบทบาท (Role & Permissions)", "12"),
        ("2. กลุ่มของฝ่ายปกครอง (Discipline Officer)", "13"),
        ("     2.1) หน้าแดชบอร์ดฝ่ายปกครอง", "13"),
        ("     2.2) หน้ารายชื่อนักเรียนกลุ่มเสี่ยง (Risk Students Monitor)", "14"),
        ("     2.3) การตั้งค่าเกณฑ์กฎระเบียบคะแนนพฤติกรรม (Behavior Rules)", "14"),
        ("     2.4) การตรวจสอบและอนุมัติการบันทึกพฤติกรรม (Approve Records)", "16"),
        ("     2.5) การพิจารณาคำร้องขออุทธรณ์คะแนน (Review Appeals)", "16"),
        ("     2.6) การจัดการเรื่องแจ้งเบาะแสพฤติกรรม (Informant Reports)", "17"),
        ("     2.7) หน้ารายงานสรุปพฤติกรรมและการส่งออกข้อมูล (Reports & Export)", "18"),
        ("3. กลุ่มของครูผู้สอนและครูที่ปรึกษา (Teacher)", "19"),
        ("     3.1) หน้าแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา (My Classroom)", "19"),
        ("     3.2) การบันทึกเวลาเรียนและการเช็คชื่อประจำวัน (Daily Attendance)", "20"),
        ("     3.3) การบันทึกคะแนนพฤติกรรมนักเรียน (Log Behavior Record)", "20"),
        ("     3.4) การสแกนเช็คชื่อละหมาด (Prayer Scanner)", "21"),
        ("     3.5) ระบบกล่องข้อความติดต่อสื่อสาร (Teacher Messages)", "22"),
        ("4. กลุ่มของนักเรียน (Student)", "23"),
        ("     4.1) หน้าแดชบอร์ดนักเรียนและคะแนนคงเหลือ (Student Dashboard)", "23"),
        ("     4.2) การตรวจสอบประวัติและสถิติการเข้าแถว (Student Attendance)", "24"),
        ("     4.3) การยื่นคำร้องขออุทธรณ์คะแนน (Submit Appeal)", "24"),
        ("     4.4) การเปิดบัตร QR Code ประจำตัวเพื่อเช็คชื่อละหมาด", "25"),
        ("     4.5) การส่งเรื่องแจ้งเบาะแสพฤติกรรมแบบไม่ระบุตัวตน", "26"),
        ("5. กลุ่มของผู้ปกครอง (Parent / Guardian)", "27"),
        ("     5.1) หน้าแดชบอร์ดผู้ปกครองและการสลับดูข้อมูลบุตรหลาน", "27"),
        ("     5.2) การตรวจสอบคะแนนพฤติกรรมและเวลาเรียน", "28"),
        ("     5.3) การส่งข้อความติดต่อครูที่ปรึกษา (Parent Messaging)", "29"),
    ]
    
    toc_tbl = doc.add_table(rows=len(toc_data) + 1, cols=2)
    toc_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    col_w = [Inches(5.2), Inches(1.2)]
    
    # TOC Header
    hdr0, hdr1 = toc_tbl.cell(0, 0), toc_tbl.cell(0, 1)
    set_cell_shading(hdr0, "1B365D")
    set_cell_shading(hdr1, "1B365D")
    set_cell_margins(hdr0, top=120, bottom=120, left=140, right=140)
    set_cell_margins(hdr1, top=120, bottom=120, left=140, right=140)
    set_cell_borders(hdr0, top="1B365D", bottom="1B365D", left=None, right=None)
    set_cell_borders(hdr1, top="1B365D", bottom="1B365D", left=None, right=None)
    
    p0 = hdr0.paragraphs[0]
    add_run_to_p(p0, "เรื่อง", font_size=13.5, bold=True, color_rgb=(255, 255, 255))
    p1 = hdr1.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    add_run_to_p(p1, "หน้า", font_size=13.5, bold=True, color_rgb=(255, 255, 255))
    
    for i, (title, page) in enumerate(toc_data):
        c0 = toc_tbl.cell(i + 1, 0)
        c1 = toc_tbl.cell(i + 1, 1)
        c0.width = col_w[0]
        c1.width = col_w[1]
        
        is_major = not title.startswith("     ")
        bg_color = "F0F4F8" if is_major else ("FFFFFF" if i % 2 == 0 else "FAFAFA")
        set_cell_shading(c0, bg_color)
        set_cell_shading(c1, bg_color)
        set_cell_margins(c0, top=35, bottom=35, left=100, right=100)
        set_cell_margins(c1, top=35, bottom=35, left=100, right=100)
        
        border_b = "DDDDDD" if is_major else "EEEEEE"
        set_cell_borders(c0, top=None, bottom=border_b, left=None, right=None)
        set_cell_borders(c1, top=None, bottom=border_b, left=None, right=None)
        
        p_t = c0.paragraphs[0]
        p_t.paragraph_format.space_before = Pt(0)
        p_t.paragraph_format.space_after = Pt(0)
        p_t.paragraph_format.line_spacing = 1.05
        add_run_to_p(p_t, title, font_size=12.5, bold=is_major, color_rgb=(0, 51, 102) if is_major else (40, 40, 40))
        
        p_pg = c1.paragraphs[0]
        p_pg.paragraph_format.space_before = Pt(0)
        p_pg.paragraph_format.space_after = Pt(0)
        p_pg.paragraph_format.line_spacing = 1.05
        p_pg.alignment = WD_ALIGN_PARAGRAPH.RIGHT
        add_run_to_p(p_pg, page, font_size=12.5, bold=is_major, color_rgb=(0, 51, 102) if is_major else (80, 80, 80))

    doc.add_page_break()

    # =========================================================
    # 3. INTRODUCTION & LOGIN
    # =========================================================
    add_styled_paragraph(doc, "บทนำและข้อมูลเบื้องต้นของระบบ", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    
    intro_txt = (
        "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษา โรงเรียนศิริราษฎร์สามัคคี "
        "แบ่งการทำงานตามสิทธิ์การเข้าใช้งาน (Role-based Access Control) ออกเป็น 5 กลุ่มผู้ใช้งานหลัก ได้แก่ "
        "ผู้ดูแลระบบ (Admin), ฝ่ายปกครอง (Discipline Officer), ครูผู้สอน/ครูที่ปรึกษา (Teacher), นักเรียน (Student) "
        "และ ผู้ปกครอง (Parent) โดยมีรายละเอียดและบัญชีทดสอบดังต่อไปนี้"
    )
    add_styled_paragraph(doc, intro_txt, font_size=13.5, space_before=0, space_after=6)
    
    url_p = add_styled_paragraph(doc, "ลิงก์เข้าใช้งานระบบ (System URL): ", font_size=13.5, bold=True, color_rgb=(0, 102, 102), space_before=2, space_after=4)
    add_run_to_p(url_p, "https://406665027.site.yru.ac.th หรือ http://localhost/student-discipline-system/public", font_size=13, italic=True, color_rgb=(0, 102, 204))
    
    # Test Accounts Table
    add_styled_paragraph(doc, "ตารางบัญชีผู้ใช้งานจำลองสำหรับทดสอบระบบ (Default Test Accounts)", font_size=14, bold=True, color_rgb=(0, 51, 102), space_before=6, space_after=4)
    acc_data = [
        ("ผู้ดูแลระบบ (Admin)", "admin", "password123", "Admin System", "จัดการผู้ใช้, นักเรียน, ครู, กำหนดสิทธิ์, นำเข้าข้อมูล"),
        ("ฝ่ายปกครอง (Discipline)", "discipline1", "password123", "ครูสมชาย ฝ่ายปกครอง", "กำหนดเกณฑ์, อนุมัติตัดคะแนน, ตรวจอุทธรณ์, พิมพ์รายงาน"),
        ("ครูที่ปรึกษา (Teacher)", "teacher1", "password123", "ครูสมหญิง ที่ปรึกษา", "เช็คชื่อเข้าเรียน, บันทึกพฤติกรรมส่งฝ่ายปกครอง, สแกนละหมาด"),
        ("นักเรียน (Student)", "student1", "password123", "ด.ช. ทดสอบ ระบบ", "ตรวจสอบคะแนนคงเหลือ, ยื่นอุทธรณ์, แสดง QR Code ละหมาด"),
        ("ผู้ปกครอง (Parent)", "parent1", "password123", "ผู้ปกครอง ทดสอบ", "ติดตามคะแนนพฤติกรรมบุตรหลาน, ดูเวลาเรียน, แชทกับครู"),
    ]
    
    acc_tbl = doc.add_table(rows=len(acc_data) + 1, cols=5)
    acc_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    widths = [Inches(1.5), Inches(1.0), Inches(1.1), Inches(1.3), Inches(1.8)]
    headers = ["กลุ่มผู้ใช้งาน", "ชื่อผู้ใช้ (User)", "รหัสผ่าน", "ชื่อในระบบ", "หน้าที่หลักในระบบ"]
    for j, h in enumerate(headers):
        c = acc_tbl.cell(0, j)
        c.width = widths[j]
        set_cell_shading(c, "1B365D")
        set_cell_margins(c, top=100, bottom=100, left=80, right=80)
        set_cell_borders(c, top="1B365D", bottom="1B365D", left="1B365D", right="1B365D")
        p = c.paragraphs[0]
        p.alignment = WD_ALIGN_PARAGRAPH.CENTER
        add_run_to_p(p, h, font_size=11.5, bold=True, color_rgb=(255, 255, 255))
        
    for i, row in enumerate(acc_data):
        for j, val in enumerate(row):
            c = acc_tbl.cell(i + 1, j)
            c.width = widths[j]
            set_cell_shading(c, "F9FBFD" if i % 2 == 0 else "FFFFFF")
            set_cell_margins(c, top=80, bottom=80, left=80, right=80)
            set_cell_borders(c, top="CCCCCC", bottom="CCCCCC", left="CCCCCC", right="CCCCCC")
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER if j in [1, 2] else WD_ALIGN_PARAGRAPH.LEFT
            add_run_to_p(p, val, font_size=11, bold=(j == 0), color_rgb=(0, 51, 102) if j == 0 else (40, 40, 40))

    # Figure 1: Login
    add_styled_paragraph(doc, "หน้าเข้าสู่ระบบ (Login)", font_size=16, bold=True, color_rgb=(0, 51, 102), space_before=10, space_after=4)
    add_image_box(doc, 1, "หน้าจอแสดงผลการเข้าสู่ระบบ (Login)")
    add_explanation(doc, [
        "จากภาพที่ 1 หน้าจอแสดงผลการเข้าสู่ระบบ ผู้ใช้งานทุกกลุ่มสามารถเข้าสู่ระบบโดยการป้อน ชื่อผู้ใช้ (Username) และ รหัสผ่าน (Password) จากนั้นคลิกปุ่ม",
        ('btn', 'btn_login'),
        "เพื่อเข้าสู่ระบบ\n",
        "หมายเหตุ : เมื่อเข้าสู่ระบบสำเร็จ ระบบจะทำการตรวจสอบสิทธิ์และเปลี่ยนเส้นทางไปยังหน้าแดชบอร์ดตามบทบาทของผู้ใช้งานโดยอัตโนมัติ"
    ])

    doc.add_page_break()

    # =========================================================
    # 4. GROUP 1: ADMIN (ผู้ดูแลระบบ)
    # =========================================================
    add_styled_paragraph(doc, "1. กลุ่มของผู้ดูแลระบบ (Admin)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    add_styled_paragraph(doc, "การทำงานของผู้ดูแลระบบครอบคลุมการจัดการโครงสร้างระบบ การจัดการข้อมูลผู้ใช้ ข้อมูลนักเรียน ข้อมูลครู การพิมพ์บัตร และการกำหนดสิทธิ์ของระบบ ดังนี้:", font_size=13.5, space_before=0, space_after=6)

    # 1.1 Dashboard
    add_styled_paragraph(doc, "1.1) หน้าแดชบอร์ดผู้ดูแลระบบ (Admin Dashboard)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_box(doc, 2, "หน้าจอแสดงแดชบอร์ดผู้ดูแลระบบ")
    add_explanation(doc, [
        "จากภาพที่ 2 ผู้ดูแลระบบสามารถดูสรุปจำนวนผู้ใช้งานในระบบ, สถิตินักเรียนทั้งหมด, จำนวนครู, บันทึกพฤติกรรมล่าสุด และเมนูการจัดการระบบด้านข้าง"
    ])

    # 1.2 User Management
    add_styled_paragraph(doc, "1.2) การจัดการข้อมูลผู้ใช้งานระบบ (User Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 3, "หน้าจอแสดงรายการข้อมูลผู้ใช้งานในระบบ")
    add_explanation(doc, [
        "จากภาพที่ 3 ผู้ดูแลระบบสามารถจัดการผู้ใช้งานในระบบ โดยสามารถเพิ่มผู้ใช้ใหม่โดยกดปุ่ม",
        ('btn', 'btn_add_user'),
        "สามารถดูรายละเอียดผู้ใช้โดยกดปุ่ม",
        ('icon', 'btn_icon_view'),
        "สามารถแก้ไขข้อมูลหรือรีเซ็ตรหัสผ่านโดยกดปุ่ม",
        ('icon', 'btn_icon_edit'),
        "และสามารถลบผู้ใช้งานโดยกดปุ่ม",
        ('icon', 'btn_icon_delete')
    ])

    add_image_box(doc, 4, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขข้อมูลผู้ใช้งาน")
    add_explanation(doc, [
        "จากภาพที่ 4 หน้าจอแบบฟอร์มเพิ่มข้อมูลผู้ใช้ ผู้ดูแลระบบกรอกข้อมูลชื่อ-สกุล, เลขประจำตัวประชาชน, กำหนดบทบาทสิทธิ์ (Admin, Discipline, Teacher, Student, Parent) และตั้งรหัสผ่าน จากนั้นคลิกปุ่ม",
        ('btn', 'btn_save'),
        "เพื่อบันทึกข้อมูล และถ้าหากไม่ต้องการเพิ่มสามารถคลิกปุ่ม",
        ('btn', 'btn_back'),
        "เพื่อปิดหน้าต่าง"
    ])

    # 1.3 Student Management
    add_styled_paragraph(doc, "1.3) การจัดการข้อมูลนักเรียน (Student Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 5, "หน้าจอแสดงรายการข้อมูลนักเรียน")
    add_explanation(doc, [
        "จากภาพที่ 5 ผู้ดูแลระบบสามารถดูรายชื่อนักเรียนทั้งหมดตามระดับชั้นและห้องเรียน ค้นหาตามรหัสนักเรียน สามารถเพิ่มนักเรียนใหม่โดยกดปุ่ม",
        ('btn', 'btn_add_data'),
        "สามารถดูข้อมูลและพิมพ์บัตรโดยกดปุ่ม",
        ('icon', 'btn_icon_view'),
        "สามารถแก้ไขข้อมูลโดยกดปุ่ม",
        ('icon', 'btn_icon_edit'),
        "และลบข้อมูลโดยกดปุ่ม",
        ('icon', 'btn_icon_delete')
    ])

    add_image_box(doc, 6, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขข้อมูลนักเรียน")
    add_explanation(doc, [
        "จากภาพที่ 6 กรอกรหัสนักเรียน, เลขประจำตัวประชาชน, คำนำหน้า, ชื่อ-สกุล, ชั้นเรียน, ห้องเรียน และข้อมูลติดต่อ จากนั้นคลิกปุ่ม",
        ('btn', 'btn_save'),
        "และถ้าต้องการยกเลิกให้คลิกปุ่ม",
        ('btn', 'btn_back')
    ])

    # 1.4 Student Card & QR
    add_styled_paragraph(doc, "1.4) การพิมพ์บัตรประจำตัวนักเรียนและ QR Code (Student ID Card)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 7, "หน้าจอแสดงบัตรประจำตัวนักเรียนพร้อม QR Code")
    add_explanation(doc, [
        "จากภาพที่ 7 ระบบจะประมวลผลสร้างบัตรประจำตัวนักเรียนพร้อมรูปถ่ายและ QR Code ประจำตัว สำหรับใช้ในการสแกนเช็คชื่อละหมาดและเข้าแถว สามารถสั่งพิมพ์บัตรได้โดยคลิกปุ่ม",
        ('btn', 'btn_print_card')
    ])

    # 1.5 Student Import
    add_styled_paragraph(doc, "1.5) การนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel/CSV", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 8, "หน้าจอนำเข้าข้อมูลนักเรียนผ่านไฟล์ Excel")
    add_explanation(doc, [
        "จากภาพที่ 8 ผู้ดูแลระบบสามารถดาวน์โหลดไฟล์แม่แบบ Excel กรอกข้อมูลนักเรียนทั้งระดับชั้น แล้วเลือกไฟล์อัปโหลดเข้ามา จากนั้นคลิกปุ่ม",
        ('btn', 'btn_save'),
        "ระบบจะสร้างข้อมูลนักเรียนและบัญชีผู้ใช้ให้อัตโนมัติ"
    ])

    # 1.6 Parents
    add_styled_paragraph(doc, "1.6) การจัดการข้อมูลผู้ปกครอง (Parent / Guardian)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 9, "หน้าจอจัดการข้อมูลผู้ปกครองและเชื่อมโยงบุตรหลาน")
    add_explanation(doc, [
        "จากภาพที่ 9 สามารถเพิ่มข้อมูลผู้ปกครอง ระบุความสัมพันธ์ (บิดา, มารดา, ผู้ปกครอง) และผูกรหัสนักเรียนเข้ากับบัญชีผู้ปกครอง เมื่อบันทึกเรียบร้อยให้คลิกปุ่ม",
        ('btn', 'btn_save')
    ])

    # 1.7 Teachers
    add_styled_paragraph(doc, "1.7) การจัดการข้อมูลครูและครูที่ปรึกษา (Teacher Management)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 10, "หน้าจอแสดงข้อมูลครูและห้องเรียนประจำชั้น")
    add_explanation(doc, [
        "จากภาพที่ 10 ผู้ดูแลระบบจัดการข้อมูลครูและกำหนดห้องเรียนประจำชั้นที่ปรึกษา (Advisory Class) เช่น กำหนดให้ครูดูแลห้อง ม.3/1 สามารถเพิ่มครูโดยกดปุ่ม",
        ('btn', 'btn_add_data'),
        "และแก้ไขห้องเรียนที่ปรึกษาโดยกดปุ่ม",
        ('icon', 'btn_icon_edit')
    ])

    # 1.8 Role & Permissions
    add_styled_paragraph(doc, "1.8) การแสดงสิทธิ์การเข้าถึงโมดูลของแต่ละบทบาท (Role & Permissions)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 11, "หน้าจอตารางแสดงสิทธิ์การเข้าถึงโมดูลของแต่ละบทบาท")
    add_explanation(doc, [
        "จากภาพที่ 11 หน้าจอแสดงตารางสิทธิ์การเข้าถึงแต่ละโมดูลในระบบ (Permission Matrix) สำหรับแต่ละบทบาท ได้แก่ ผู้ดูแลระบบ, ฝ่ายปกครอง, ครู, นักเรียน และผู้ปกครอง โดยระบบจะแสดงสัญลักษณ์แสดงสิทธิ์การอ่าน/ดูข้อมูล (รูปตา), สิทธิ์การบันทึกหรือจัดการข้อมูล (รูปดินสอ) และไม่อนุญาตให้เข้าถึง (เครื่องหมายกากบาท) เพื่อให้ผู้ดูแลระบบตรวจสอบความถูกต้องของโครงสร้างสิทธิ์ในระบบได้อย่างชัดเจน"
    ])

    doc.add_page_break()

    # =========================================================
    # 5. GROUP 2: DISCIPLINE (ฝ่ายปกครอง)
    # =========================================================
    add_styled_paragraph(doc, "2. กลุ่มของฝ่ายปกครอง (Discipline Officer)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    add_styled_paragraph(doc, "ฝ่ายปกครองมีหน้าที่กำกับดูแลกฎระเบียบวินัย พิจารณาอนุมัติตัด/เพิ่มคะแนน ตรวจสอบเรื่องอุทธรณ์ และติดตามนักเรียนกลุ่มเสี่ยง ดังนี้:", font_size=13.5, space_before=0, space_after=6)

    # 2.1 Discipline Dashboard
    add_styled_paragraph(doc, "2.1) หน้าแดชบอร์ดฝ่ายปกครอง", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_box(doc, 12, "หน้าจอแสดงแดชบอร์ดของฝ่ายปกครอง", img_filename="fig_13.png")
    add_explanation(doc, [
        "จากภาพที่ 12 ฝ่ายปกครองสามารถดูสรุปจำนวนบันทึกพฤติกรรมที่รอการอนุมัติ, คำร้องอุทธรณ์ที่รอดำเนินการ, สถิติการกระทำผิด และสถิตินักเรียนกลุ่มเสี่ยงที่มีคะแนนต่ำกว่าเกณฑ์"
    ])

    # 2.2 Risk Students
    add_styled_paragraph(doc, "2.2) หน้ารายชื่อนักเรียนกลุ่มเสี่ยง (Risk Students Monitor)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 13, "หน้าจอติดตามนักเรียนกลุ่มเสี่ยง (Risk Students)", img_filename="fig_14.png")
    add_explanation(doc, [
        "จากภาพที่ 13 แสดงรายชื่อนักเรียนที่มีคะแนนคงเหลือต่ำกว่าเกณฑ์ (เช่น ต่ำกว่า 50 คะแนน) พร้อมระดับแจ้งเตือน เพื่อให้ฝ่ายปกครองสามารถเรียกพบ ทำทัณฑ์บน หรือออกหนังสือเชิญผู้ปกครอง"
    ])

    # 2.3 Behavior Rules
    add_styled_paragraph(doc, "2.3) การตั้งค่าเกณฑ์กฎระเบียบคะแนนพฤติกรรม (Behavior Rules)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 14, "หน้าจอแสดงรายการเกณฑ์กฎระเบียบวินัย", img_filename="fig_15.png")
    add_explanation(doc, [
        "จากภาพที่ 14 แสดงรายการกฎระเบียบทั้งหมด ฝ่ายปกครองสามารถเพิ่มเกณฑ์ข้อระเบียบใหม่โดยกดปุ่ม",
        ('btn', 'btn_add_rule'),
        "สามารถแก้ไขคะแนนโดยกดปุ่ม",
        ('icon', 'btn_icon_edit'),
        "และลบข้อระเบียบโดยกดปุ่ม",
        ('icon', 'btn_icon_delete')
    ])

    add_image_box(doc, 15, "หน้าจอแบบฟอร์มเพิ่ม/แก้ไขกฎระเบียบและคะแนน", img_filename="fig_16.png")
    add_explanation(doc, [
        "จากภาพที่ 15 ระบุชื่อข้อระเบียบ, หมวดหมู่ความประพฤติ, ประเภทการตัดคะแนน (-) หรือเพิ่มคะแนนความดี (+), จำนวนคะแนน แล้วคลิกปุ่ม",
        ('btn', 'btn_save_rule'),
        "เพื่อบันทึก และถ้าไม่ต้องการเพิ่มให้คลิกปุ่ม",
        ('btn', 'btn_back')
    ])

    # 2.4 Approve Behavior Records
    add_styled_paragraph(doc, "2.4) การตรวจสอบและอนุมัติการบันทึกพฤติกรรม (Approve Records)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 16, "หน้าจอตรวจสอบและอนุมัติการบันทึกพฤติกรรม", img_filename="fig_17.png")
    add_explanation(doc, [
        "จากภาพที่ 16 รายการพฤติกรรมที่ครูส่งเข้ามาจะแสดงสถานะรอการตรวจสอบ ฝ่ายปกครองสามารถตรวจสอบหลักฐานรูปถ่าย แล้วกดปุ่ม",
        ('btn', 'btn_approve'),
        "เพื่อยืนยันการตัดคะแนน หรือกดปุ่ม",
        ('btn', 'btn_reject'),
        "หากพิจารณาแล้วไม่เข้าข่ายความผิด"
    ])

    # 2.5 Appeals
    add_styled_paragraph(doc, "2.5) การพิจารณาคำร้องขออุทธรณ์คะแนน (Review Appeals)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 17, "หน้าจอพิจารณาคำร้องขออุทธรณ์คะแนนของนักเรียน", img_filename="fig_18.png")
    add_explanation(doc, [
        "จากภาพที่ 17 ฝ่ายปกครองตรวจสอบเอกสารคำชี้แจงและหลักฐานที่นักเรียนแนบมา หากเห็นสมควรให้คลิกปุ่ม",
        ('btn', 'btn_approve'),
        "ระบบจะคืนคะแนนให้นักเรียนทันที หรือคลิกปุ่ม",
        ('btn', 'btn_reject'),
        "เพื่อปฏิเสธคำร้อง"
    ])

    # 2.6 Informant Reports
    add_styled_paragraph(doc, "2.6) การจัดการเรื่องแจ้งเบาะแสพฤติกรรม (Informant Reports)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 18, "หน้าจอแสดงรายการแจ้งเบาะแสพฤติกรรม", img_filename="fig_19.png")
    add_explanation(doc, [
        "จากภาพที่ 18 ฝ่ายปกครองเปิดดูเรื่องที่นักเรียนส่งแจ้งเบาะแสการกระทำผิด (เช่น การสูบบุหรี่ หรือการหนีเรียน) เพื่อดำเนินการตรวจสอบข้อเท็จจริง และคลิกปุ่ม",
        ('btn', 'btn_save'),
        "เพื่อบันทึกผลการดำเนินการ"
    ])

    # 2.7 Reports & Export
    add_styled_paragraph(doc, "2.7) หน้ารายงานสรุปพฤติกรรมและการส่งออกข้อมูล (Reports & Export)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 19, "หน้าจอรายงานสรุปพฤติกรรมและการส่งออกข้อมูล", img_filename="fig_20.png")
    add_explanation(doc, [
        "จากภาพที่ 19 สามารถเลือกช่วงวันที่, ระดับชั้น และคลิกปุ่ม",
        ('btn', 'btn_print_pdf'),
        "เพื่อพิมพ์รายงานความประพฤติรายห้อง หรือคลิกปุ่ม",
        ('btn', 'btn_export_excel'),
        "เพื่อส่งออกข้อมูลเป็นไฟล์ Excel"
    ])

    doc.add_page_break()

    # =========================================================
    # 6. GROUP 3: TEACHER (ครูผู้สอน/ที่ปรึกษา)
    # =========================================================
    add_styled_paragraph(doc, "3. กลุ่มของครูผู้สอนและครูที่ปรึกษา (Teacher)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    add_styled_paragraph(doc, "ครูผู้สอนและครูที่ปรึกษาเป็นผู้ดูแลนักเรียนอย่างใกล้ชิด ทำหน้าที่เช็คชื่อประจำวัน บันทึกพฤติกรรม สแกนละหมาด และประสานงานกับผู้ปกครอง ดังนี้:", font_size=13.5, space_before=0, space_after=6)

    # 3.1 Teacher Dashboard & Classroom
    add_styled_paragraph(doc, "3.1) หน้าแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา (My Classroom)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_box(doc, 20, "หน้าจอแดชบอร์ดครูและข้อมูลห้องเรียนที่ปรึกษา", img_filename="fig_21.png")
    add_explanation(doc, [
        "จากภาพที่ 20 ครูที่ปรึกษาดูภาพรวมสถิติของนักเรียนในห้องที่รับผิดชอบ คะแนนเฉลี่ยประจำห้อง และรายชื่อนักเรียนที่ต้องเฝ้าระวังพฤติกรรม"
    ])

    # 3.2 Attendance
    add_styled_paragraph(doc, "3.2) การบันทึกเวลาเรียนและการเช็คชื่อประจำวัน (Daily Attendance)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 21, "หน้าจอเช็คชื่อการเข้าเรียนและเข้าแถวประจำวัน", img_filename="fig_22.png")
    add_explanation(doc, [
        "จากภาพที่ 21 ครูทำการเช็คชื่อนักเรียนตามห้องเรียน สามารถคลิกปุ่มลัด",
        ('btn', 'btn_checkin_all'),
        "เพื่อให้สถานะนักเรียนทุกคนเป็นมาเรียน จากนั้นปรับเฉพาะคนที่ขาดหรือลา แล้วคลิกปุ่ม",
        ('btn', 'btn_save'),
        "เพื่อบันทึกการเช็คชื่อ"
    ])

    # 3.3 Log Behavior Record
    add_styled_paragraph(doc, "3.3) การบันทึกคะแนนพฤติกรรมนักเรียน (Log Behavior Record)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 22, "หน้าจอแบบฟอร์มบันทึกพฤติกรรมนักเรียน", img_filename="fig_23.png")
    add_explanation(doc, [
        "จากภาพที่ 22 เมื่อพบนักเรียนทำผิดหรือทำความดี ครูค้นหาชื่อนักเรียน, เลือกข้อกฎระเบียบ, ระบุสถานที่/เหตุการณ์, แนบรูปหลักฐาน แล้วกดปุ่ม",
        ('btn', 'btn_send_record'),
        "เรื่องจะถูกส่งต่อไปยังฝ่ายปกครองเพื่อรอการอนุมัติ"
    ])

    # 3.4 Prayer Scanner
    add_styled_paragraph(doc, "3.4) การสแกนเช็คชื่อละหมาด (Prayer Scanner)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 23, "หน้าจอเครื่องสแกน QR Code สำหรับเช็คชื่อละหมาด", img_filename="fig_24.png")
    add_explanation(doc, [
        "จากภาพที่ 23 ครูเปิดกล้องจากโทรศัพท์มือถือ เลือกรอบเวลาละหมาด (เช่น ซุฮรี หรือ อัศรี) แล้วนำไปสแกน QR Code บนบัตรนักเรียนเพื่อเช็คชื่อละหมาดทันที"
    ])

    # 3.5 Teacher Messages
    add_styled_paragraph(doc, "3.5) ระบบกล่องข้อความติดต่อสื่อสาร (Teacher Messages)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 24, "หน้าจอกล่องข้อความพูดคุยกับผู้ปกครอง", img_filename="fig_25.png")
    add_explanation(doc, [
        "จากภาพที่ 24 ครูรับและตอบข้อความแชทจากผู้ปกครอง ตรวจดูใบลาหรือเอกสารที่ส่งมา เพื่อให้การประสานงานระหว่างโรงเรียนกับครอบครัวเป็นไปอย่างมีประสิทธิภาพ"
    ])

    doc.add_page_break()

    # =========================================================
    # 7. GROUP 4: STUDENT (นักเรียน)
    # =========================================================
    add_styled_paragraph(doc, "4. กลุ่มของนักเรียน (Student)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    add_styled_paragraph(doc, "นักเรียนสามารถเข้ามาตรวจสอบสถานะคะแนนความประพฤติของตนเอง ติดตามประวัติการเข้าแถว ยื่นคำร้องขออุทธรณ์ เปิด QR Code ละหมาด และแจ้งเบาะแส ดังนี้:", font_size=13.5, space_before=0, space_after=6)

    # 4.1 Dashboard
    add_styled_paragraph(doc, "4.1) หน้าแดชบอร์ดนักเรียนและคะแนนคงเหลือ (Student Dashboard)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_box(doc, 25, "หน้าแดชบอร์ดแสดงคะแนนความประพฤตินักเรียน", img_filename="fig_26.png")
    add_explanation(doc, [
        "จากภาพที่ 25 นักเรียนสามารถดูคะแนนความประพฤติคงเหลือของตนเอง (เริ่มต้น 100 คะแนน) สถิติการมาเรียน การขาดแถว และรายการถูกตัดคะแนนล่าสุด"
    ])

    # 4.2 Attendance
    add_styled_paragraph(doc, "4.2) การตรวจสอบประวัติและสถิติการเข้าแถว (Student Attendance)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 26, "หน้าจอปฏิทินและสถิติการเข้าแถวของนักเรียน", img_filename="fig_student_attendance.png")
    add_explanation(doc, [
        "จากภาพที่ 26 นักเรียนสามารถตรวจสอบปฏิทินการเข้าแถวประจำวัน สถิติการมาเรียน มาสาย และขาดแถวในแต่ละเดือน พร้อมเกณฑ์วินัยการตัดคะแนนความประพฤติอัตโนมัติ (ขาดสะสมครบทุก 3 ครั้ง ตัด 5 คะแนน) โดยสามารถคลิกปุ่ม",
        ('btn', 'btn_monthly_stats'),
        "เพื่อดูรายงานสรุปสถิติการเข้าแถวแบบคิดเป็นร้อยละในแต่ละเดือนได้"
    ])

    # 4.3 Appeals
    add_styled_paragraph(doc, "4.3) การยื่นคำร้องขออุทธรณ์คะแนน (Submit Appeal)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 27, "หน้าจอแบบฟอร์มยื่นคำร้องขออุทธรณ์คะแนน", img_filename="fig_27.png")
    add_explanation(doc, [
        "จากภาพที่ 27 หากนักเรียนถูกตัดคะแนนโดยมีเหตุสุดวิสัย สามารถเลือกรายการที่ถูกตัด กรอกคำชี้แจงข้อเท็จจริง แนบใบรับรองแพทย์หรือหลักฐาน แล้วคลิกปุ่ม",
        ('btn', 'btn_send_appeal'),
        "เพื่อส่งเรื่องให้ฝ่ายปกครองพิจารณา"
    ])

    # 4.4 Prayer QR
    add_styled_paragraph(doc, "4.4) การเปิดบัตร QR Code ประจำตัวเพื่อเช็คชื่อละหมาด (Prayer QR Check-in)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 28, "หน้าจอแสดง QR Code ประจำตัวสำหรับเช็คชื่อละหมาด", img_filename="fig_28.png")
    add_explanation(doc, [
        "จากภาพที่ 28 นักเรียนเปิดหน้านี้ผ่านโทรศัพท์มือถือ เพื่อแสดง QR Code ประจำตัวให้ครูสแกน ณ จุดเช็คชื่อละหมาด พร้อมดูประวัติการละหมาดของวันนี้ได้ทันที"
    ])

    # 4.5 Informant Reports
    add_styled_paragraph(doc, "4.5) การส่งเรื่องแจ้งเบาะแสพฤติกรรมแบบไม่ระบุตัวตน (Informant Report)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 29, "หน้าจอแจ้งเบาะแสพฤติกรรมที่ไม่เหมาะสม", img_filename="fig_29.png")
    add_explanation(doc, [
        "จากภาพที่ 29 นักเรียนสามารถร่วมดูแลสังคมในโรงเรียน โดยแจ้งเบาะแสการกระทำผิด และเลือกได้ว่าจะ \"เปิดเผยชื่อ\" หรือ \"แจ้งแบบปกปิดชื่อ (Anonymous)\" แล้วคลิกปุ่ม",
        ('btn', 'btn_send_informant')
    ])

    doc.add_page_break()

    # =========================================================
    # 8. GROUP 5: PARENT (ผู้ปกครอง)
    # =========================================================
    add_styled_paragraph(doc, "5. กลุ่มของผู้ปกครอง (Parent / Guardian)", font_size=18, bold=True, color_rgb=(0, 51, 102), space_before=4, space_after=6)
    add_styled_paragraph(doc, "ผู้ปกครองสามารถเข้ามาติดตามพฤติกรรมและเวลาเรียนของบุตรหลานผ่านระบบได้อย่างใกล้ชิด ดังนี้:", font_size=13.5, space_before=0, space_after=6)

    # 5.1 Parent Dashboard & Switch
    add_styled_paragraph(doc, "5.1) หน้าแดชบอร์ดผู้ปกครองและการสลับดูข้อมูลบุตรหลาน", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=4, space_after=4)
    add_image_box(doc, 30, "หน้าแดชบอร์ดสำหรับผู้ปกครอง", img_filename="fig_30.png")
    add_explanation(doc, [
        "จากภาพที่ 30 ผู้ปกครองดูภาพรวมความประพฤติของบุตรหลาน คะแนนคงเหลือ และกรณีมีบุตรหลานเรียนอยู่มากกว่า 1 คน สามารถคลิกปุ่ม",
        ('btn', 'btn_switch_child'),
        "เพื่อเลือกสลับดูข้อมูลของแต่ละคนได้อย่างสะดวก"
    ])

    doc.add_page_break()

    # 5.2 Behavior History
    add_styled_paragraph(doc, "5.2) การตรวจสอบคะแนนพฤติกรรมและเวลาเรียน", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 31, "หน้าจอประวัติพฤติกรรมและการเช็คชื่อบุตรหลาน", img_filename="fig_31.png")
    add_explanation(doc, [
        "จากภาพที่ 31 แสดงรายละเอียดบันทึกการตัดคะแนนย้อนหลัง สาเหตุ วันที่ และสถิติการขาด-ลา-มาสาย เพื่อให้ผู้ปกครองสามารถติดตามและตักเตือนบุตรหลานได้ทันท่วงที"
    ])

    doc.add_page_break()

    # 5.3 Parent Messages
    add_styled_paragraph(doc, "5.3) การส่งข้อความติดต่อครูที่ปรึกษา (Parent Messaging)", font_size=15, bold=True, color_rgb=(0, 102, 102), space_before=6, space_after=4)
    add_image_box(doc, 32, "หน้าจอส่งข้อความติดต่อครูที่ปรึกษา", img_filename="fig_32.png")
    add_explanation(doc, [
        "จากภาพที่ 32 ผู้ปกครองสามารถพิมพ์ข้อความสอบถามปัญหา หรือแจ้งการลากิจ/ลาป่วย พร้อมแนบเอกสารส่งตรงไปยังครูที่ปรึกษาได้อย่างสะดวกและเป็นส่วนตัว จากนั้นคลิกปุ่ม",
        ('btn', 'btn_save'),
        "เพื่อส่งข้อความ"
    ])

    # Save documents with fallback if opened in Microsoft Word
    saved_paths = []
    for target in [OUTPUT_FILE, OUTPUT_FILE_ALT]:
        try:
            doc.save(target)
            saved_paths.append(target)
        except PermissionError:
            base, ext = os.path.splitext(target)
            fallback = f"{base}_Transparent{ext}"
            doc.save(fallback)
            saved_paths.append(fallback)
            
    print("User manual successfully generated at:")
    for p in saved_paths:
        print(f"  - {p}")

if __name__ == "__main__":
    build_full_user_manual()
