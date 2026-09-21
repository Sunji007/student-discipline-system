# -*- coding: utf-8 -*-
import os
import docx
from docx.shared import Inches, Pt, RGBColor, Cm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import parse_xml
from docx.oxml.ns import nsdecls

def test_cover():
    doc = docx.Document()
    section = doc.sections[0]
    section.top_margin = Cm(2.0)
    section.bottom_margin = Cm(2.0)
    section.left_margin = Cm(2.5)
    section.right_margin = Cm(2.5)
    section.page_width = Cm(21.0)
    section.page_height = Cm(29.7)
    section.different_first_page_header_footer = True
    
    # 1. Top Logos Table (University Logo & Department Logo side-by-side)
    logo_uni = os.path.abspath("docs/manual_images/logo_university.jpg")
    logo_dept = os.path.abspath("docs/manual_images/logo_department.jpg")
    
    logo_tbl = doc.add_table(rows=1, cols=2)
    logo_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
    c0 = logo_tbl.cell(0, 0)
    c1 = logo_tbl.cell(0, 1)
    c0.width = Inches(2.8)
    c1.width = Inches(2.8)
    
    # Uni logo
    p0 = c0.paragraphs[0]
    p0.alignment = WD_ALIGN_PARAGRAPH.RIGHT
    p0.paragraph_format.space_after = Pt(0)
    p0.paragraph_format.space_before = Pt(0)
    if os.path.exists(logo_uni):
        r0 = p0.add_run()
        r0.add_picture(logo_uni, height=Inches(1.3))
        
    # Dept logo
    p1 = c1.paragraphs[0]
    p1.alignment = WD_ALIGN_PARAGRAPH.LEFT
    p1.paragraph_format.space_after = Pt(0)
    p1.paragraph_format.space_before = Pt(0)
    if os.path.exists(logo_dept):
        r1 = p1.add_run()
        # Add small spacing run before picture to separate them slightly
        p1.add_run("   ")
        r1.add_picture(logo_dept, height=Inches(1.25))

    # Titles
    p_t1 = doc.add_paragraph()
    p_t1.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_t1.paragraph_format.space_before = Pt(8)
    p_t1.paragraph_format.space_after = Pt(4)
    r = p_t1.add_run("คู่มือการใช้งาน (User Manual)")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(24)
    r.bold = True
    r.font.color.rgb = RGBColor(15, 76, 129)

    p_t2 = doc.add_paragraph()
    p_t2.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_t2.paragraph_format.space_before = Pt(0)
    p_t2.paragraph_format.space_after = Pt(2)
    r = p_t2.add_run("ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(17)
    r.bold = True
    r.font.color.rgb = RGBColor(30, 30, 30)

    p_t3 = doc.add_paragraph()
    p_t3.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_t3.paragraph_format.space_before = Pt(0)
    p_t3.paragraph_format.space_after = Pt(2)
    r = p_t3.add_run("กรณีศึกษา : โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(13)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 102, 153)

    p_t4 = doc.add_paragraph()
    p_t4.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_t4.paragraph_format.space_before = Pt(0)
    p_t4.paragraph_format.space_after = Pt(10)
    r = p_t4.add_run("Information System Discipline and Monitoring Student Behavior : A Case Study of Sirirat Samakkhi School Pattani Province")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(10.5)
    r.italic = True
    r.font.color.rgb = RGBColor(100, 100, 100)

    # Center Showcase Image
    cover_img_path = os.path.abspath("docs/manual_images/fig_02.png")
    if os.path.exists(cover_img_path):
        img_tbl = doc.add_table(rows=1, cols=1)
        img_tbl.alignment = WD_TABLE_ALIGNMENT.CENTER
        ic = img_tbl.cell(0, 0)
        ic.width = Inches(5.2)
        ip = ic.paragraphs[0]
        ip.alignment = WD_ALIGN_PARAGRAPH.CENTER
        ip.paragraph_format.space_before = Pt(2)
        ip.paragraph_format.space_after = Pt(2)
        irun = ip.add_run()
        irun.add_picture(cover_img_path, width=Inches(5.0))

    # Authors
    p_auth_hdr = doc.add_paragraph()
    p_auth_hdr.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_auth_hdr.paragraph_format.space_before = Pt(10)
    p_auth_hdr.paragraph_format.space_after = Pt(2)
    r = p_auth_hdr.add_run("จัดทำโดย")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(15)
    r.bold = True
    r.font.color.rgb = RGBColor(15, 76, 129)

    p_auth = doc.add_paragraph()
    p_auth.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_auth.paragraph_format.space_before = Pt(0)
    p_auth.paragraph_format.space_after = Pt(8)
    p_auth.paragraph_format.line_spacing = 1.15
    r = p_auth.add_run("นายตอริก ลือแม               รหัสนักศึกษา 406665012\nนายบุสริน มูซอ                 รหัสนักศึกษา 406665027")
    r.font.name = "TH Sarabun New"
    r.font.size = Pt(13)
    r.font.color.rgb = RGBColor(40, 40, 40)

    # Department & University Footer on Cover
    p_dept = doc.add_paragraph()
    p_dept.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_dept.paragraph_format.space_before = Pt(0)
    p_dept.paragraph_format.space_after = Pt(0)
    p_dept.paragraph_format.line_spacing = 1.15
    r1 = p_dept.add_run("หลักสูตรวิทยาศาสตรบัณฑิต สาขาวิชาเทคโนโลยีสารสนเทศ\n")
    r1.font.name = "TH Sarabun New"
    r1.font.size = Pt(13)
    r1.bold = True
    r1.font.color.rgb = RGBColor(40, 40, 40)
    
    r2 = p_dept.add_run("คณะวิทยาศาสตร์เทคโนโลยีและการเกษตร มหาวิทยาลัยราชภัฏยะลา\nปีการศึกษา 2569")
    r2.font.name = "TH Sarabun New"
    r2.font.size = Pt(12)
    r2.font.color.rgb = RGBColor(70, 70, 70)

    doc.add_page_break()
    p_toc = doc.add_paragraph("หน้าสารบัญ (Page 2)")
    
    out_path = os.path.abspath("docs/test_cover.docx")
    doc.save(out_path)
    print("Saved test cover to:", out_path)

if __name__ == "__main__":
    test_cover()
