// สไลด์นำเสนอโครงงานวิจัย — ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน
// 15 สไลด์ | 13.33x7.5" | ธีมเขียวป่า/ทอง | ฟอนต์ Leelawadee UI | เนื้อหากระชับ รายละเอียดอยู่ใน speaker notes
const pptxgen = require("pptxgenjs");

const ROOT = "C:/xampp/htdocs/student-discipline-system";
const IMG = ROOT + "/docs/manual_images";
const AST = ROOT + "/docs/presentation_assets";
const LOGO_U = IMG + "/logo_university.png";     // 360x481
const LOGO_S = ROOT + "/public/images/logo.png"; // 577x433

// ---------- palette ----------
const BG   = "FFFFFF";
const INK  = "1E293B";
const MUT  = "64748B";
const GRN  = "0F5132";
const GRND = "0A3A22";
const GRNT = "E9F1EC";
const GLD  = "D97706";
const GLDT = "FBF0DE";
const LINE = "E2E8F0";
const WHT  = "FFFFFF";
const F = "Leelawadee UI";

const W = 13.33, H = 7.5, M = 0.5;
const AR = 1920 / 1230; // screenshot aspect

const sh  = () => ({ type: "outer", color: "1E293B", blur: 7, offset: 2, angle: 90, opacity: 0.16 });
const shS = () => ({ type: "outer", color: "1E293B", blur: 4, offset: 1, angle: 90, opacity: 0.12 });

let p = new pptxgen();
p.layout = "LAYOUT_WIDE";
p.author = "ตอริก ลือแมะ · บุสริน มูซอ";
p.title = "ระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน";

// ---------- helpers ----------
function header(s, kicker, title) {
  s.addText(kicker, { x: M, y: 0.34, w: 9.5, h: 0.3, fontFace: F, fontSize: 12, bold: true, color: GLD, charSpacing: 2, margin: 0 });
  s.addText(title, { x: M, y: 0.62, w: 11.0, h: 0.66, fontFace: F, fontSize: 27, bold: true, color: GRN, margin: 0 });
}
function foot(s, n) {
  s.addText("ระบบสารสนเทศการบริหารงานวินัยฯ · โรงเรียนศิริราษฎร์สามัคคี", { x: M, y: H - 0.42, w: 6.5, h: 0.3, fontFace: F, fontSize: 9.5, color: "94A3B8", margin: 0 });
  s.addText(String(n).padStart(2, "0") + " / 15", { x: W - 1.5, y: H - 0.42, w: 1.0, h: 0.3, fontFace: F, fontSize: 10.5, color: MUT, align: "right", margin: 0 });
}
// การ์ดหน้าจอระบบแบบ browser frame
function shot(s, x, y, w, img, url) {
  const pad = 0.12, chrome = 0.28;
  const iw = w - 2 * pad, ih = iw / AR;
  const cardH = pad + chrome + ih + pad;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y, w, h: cardH, rectRadius: 0.07, fill: { color: WHT }, line: { color: LINE, width: 0.75 }, shadow: sh() });
  ["CBD5E1", "D8E2DC", "B7D4C4"].forEach((c, i) =>
    s.addShape(p.shapes.OVAL, { x: x + pad + 0.03 + i * 0.155, y: y + pad + chrome / 2 - 0.045, w: 0.09, h: 0.09, fill: { color: c } }));
  s.addText(url || "406665027.site.yru.ac.th", { x: x + pad + 0.58, y: y + pad, w: w - pad * 2 - 0.7, h: chrome, fontFace: "Consolas", fontSize: 8.5, color: "94A3B8", valign: "middle", margin: 0 });
  s.addShape(p.shapes.RECTANGLE, { x: x + pad, y: y + pad + chrome, w: iw, h: 0.012, fill: { color: LINE } });
  s.addImage({ path: img, x: x + pad, y: y + pad + chrome + 0.012, w: iw, h: ih - 0.012 });
  return cardH;
}
// คำบรรยายใต้การ์ดหน้าจอ (tag + หัวข้อ + บูลเล็ตสั้นเดียว)
function caption(s, x, y, w, tag, title, line) {
  s.addText(tag, { x, y, w, h: 0.26, fontFace: F, fontSize: 11.5, bold: true, color: GLD, margin: 0 });
  s.addText(title, { x, y: y + 0.26, w, h: 0.34, fontFace: F, fontSize: 15.5, bold: true, color: INK, margin: 0 });
  if (line) s.addText(line, { x: x + 0.02, y: y + 0.62, w, h: 0.32, fontFace: F, fontSize: 12, color: MUT, margin: 0 });
}

// ============================================================ 1 · ปก
(() => {
  const s = p.addSlide();
  s.background = { path: AST + "/cover_bg.jpg" };
  s.addShape(p.shapes.RECTANGLE, { x: 0, y: 0, w: W, h: H, fill: { color: GRND, transparency: 11 } });

  const d = 1.12, cy = 1.02;
  s.addShape(p.shapes.OVAL, { x: 5.42, y: cy - d / 2, w: d, h: d, fill: { color: WHT }, shadow: shS() });
  s.addImage({ path: LOGO_U, x: 5.42 + d / 2 - 0.325, y: cy - 0.425, w: 0.65, h: 0.85 });
  s.addShape(p.shapes.OVAL, { x: 6.82, y: cy - d / 2, w: d, h: d, fill: { color: WHT }, shadow: shS() });
  s.addImage({ path: LOGO_S, x: 6.82 + d / 2 - 0.45, y: cy - 0.34, w: 0.90, h: 0.68 });

  s.addText("การนำเสนอโครงงานวิจัย  ·  ปีการศึกษา 2569", { x: 1.5, y: 1.98, w: W - 3, h: 0.32, fontFace: F, fontSize: 13, bold: true, color: "F5C063", align: "center", charSpacing: 2, margin: 0 });
  s.addText([
    { text: "ระบบสารสนเทศการบริหารงานวินัย", options: { breakLine: true } },
    { text: "และติดตามพฤติกรรมนักเรียน" },
  ], { x: 1.0, y: 2.34, w: W - 2, h: 1.68, fontFace: F, fontSize: 37, bold: true, color: WHT, align: "center", valign: "middle", margin: 0 });
  s.addText("กรณีศึกษา : โรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี", { x: 1.5, y: 4.12, w: W - 3, h: 0.42, fontFace: F, fontSize: 18.5, bold: true, color: "F5C063", align: "center", margin: 0 });

  const cw = 9.2, cx = (W - cw) / 2, cy2 = 5.18, chh = 1.42;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: cx, y: cy2, w: cw, h: chh, rectRadius: 0.09, fill: { color: WHT, transparency: 88 }, line: { color: "8FBFA6", width: 0.75 } });
  s.addText("คณะผู้วิจัย", { x: cx + 0.4, y: cy2 + 0.16, w: 4.2, h: 0.28, fontFace: F, fontSize: 12, bold: true, color: "F5C063", margin: 0 });
  s.addText([
    { text: "นายตอริก ลือแมะ   (406665012)", options: { breakLine: true } },
    { text: "นายบุสริน มูซอ   (406665027)" },
  ], { x: cx + 0.4, y: cy2 + 0.47, w: 4.2, h: 0.8, fontFace: F, fontSize: 13.5, bold: true, color: WHT, paraSpaceAfter: 4, margin: 0 });
  s.addShape(p.shapes.RECTANGLE, { x: cx + 4.75, y: cy2 + 0.25, w: 0.012, h: chh - 0.5, fill: { color: "8FBFA6" } });
  s.addText("อาจารย์ที่ปรึกษา", { x: cx + 5.05, y: cy2 + 0.16, w: 3.8, h: 0.28, fontFace: F, fontSize: 12, bold: true, color: "F5C063", margin: 0 });
  s.addText([
    { text: "อาจารย์แพรวศรี เดิมราช", options: { breakLine: true } },
    { text: "อาจารย์รุสนี กาแมแล" },
  ], { x: cx + 5.05, y: cy2 + 0.47, w: 3.8, h: 0.8, fontFace: F, fontSize: 13.5, bold: true, color: WHT, paraSpaceAfter: 4, margin: 0 });

  s.addText("สาขาวิชาเทคโนโลยีสารสนเทศ  ·  คณะวิทยาศาสตร์ เทคโนโลยีและการเกษตร  ·  มหาวิทยาลัยราชภัฏยะลา",
    { x: 1.5, y: 6.96, w: W - 3, h: 0.3, fontFace: F, fontSize: 11, color: "CFE0D6", align: "center", margin: 0 });
  s.addNotes("สวัสดีคณะกรรมการและผู้ฟังทุกท่าน วันนี้นำเสนอโครงงานวิจัยเรื่องระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษาโรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี โดยผู้วิจัยนายตอริก ลือแมะ และนายบุสริน มูซอ ภายใต้คำปรึกษาของอาจารย์แพรวศรี เดิมราช และอาจารย์รุสนี กาแมแล (ชื่อภาษาอังกฤษ: Information System Discipline and Monitoring Student Behavior)");
})();

// ============================================================ 2 · คณะผู้วิจัยและอาจารย์ที่ปรึกษา
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "ทีมวิจัย  ·  ผู้จัดทำโครงงาน", "คณะผู้วิจัยและอาจารย์ที่ปรึกษา");

  const cardW = 5.0, cardH = 1.95, gap = 0.3;
  const pairX = (W - 2 * cardW - gap) / 2;
  function person(x, y, ph, tag, name, sub) {
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y, w: cardW, h: cardH, rectRadius: 0.09, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
    s.addImage({ path: AST + "/logos/" + ph, x: x + 0.3, y: y + (cardH - 1.5) / 2, w: 1.5, h: 1.5 });
    s.addText(tag, { x: x + 2.0, y: y + 0.34, w: cardW - 2.2, h: 0.26, fontFace: F, fontSize: 11.5, bold: true, color: GLD, charSpacing: 1, margin: 0 });
    s.addText(name, { x: x + 2.0, y: y + 0.62, w: cardW - 2.2, h: 0.42, fontFace: F, fontSize: 17, bold: true, color: INK, margin: 0 });
    s.addText(sub, { x: x + 2.0, y: y + 1.1, w: cardW - 2.2, h: 0.34, fontFace: F, fontSize: 12, color: MUT, margin: 0 });
  }
  s.addText("คณะผู้วิจัย (Researchers)", { x: M, y: 1.5, w: W - 2 * M, h: 0.34, fontFace: F, fontSize: 16, bold: true, color: GRN, align: "center", margin: 0 });
  person(pairX, 1.92, "photo_researcher.png", "ผู้วิจัย", "นายตอริก ลือแมะ", "รหัสนักศึกษา 406665012");
  person(pairX + cardW + gap, 1.92, "photo_researcher.png", "ผู้วิจัย", "นายบุสริน มูซอ", "รหัสนักศึกษา 406665027");

  s.addText("อาจารย์ที่ปรึกษา (Advisors)", { x: M, y: 4.16, w: W - 2 * M, h: 0.34, fontFace: F, fontSize: 16, bold: true, color: GRN, align: "center", margin: 0 });
  person(pairX, 4.58, "photo_advisor.png", "อาจารย์ที่ปรึกษา", "อาจารย์แพรวศรี เดิมราช", "มหาวิทยาลัยราชภัฏยะลา");
  person(pairX + cardW + gap, 4.58, "photo_advisor.png", "อาจารย์ที่ปรึกษา", "อาจารย์รุสนี กาแมแล", "มหาวิทยาลัยราชภัฏยะลา");

  s.addText("คลิกขวาที่รูปวงกลม → เปลี่ยนรูปภาพ (Change Picture)", { x: M, y: 6.72, w: W - 2 * M, h: 0.3, fontFace: F, fontSize: 10.5, color: "94A3B8", align: "center", margin: 0 });
  foot(s, 2);
  s.addNotes("แนะนำทีมผู้วิจัยและอาจารย์ที่ปรึกษาก่อนเข้าสู่เนื้อหา ทั้งสองท่านสังกัดสาขาวิชาเทคโนโลยีสารสนเทศ คณะวิทยาศาสตร์ เทคโนโลยีและการเกษตร มหาวิทยาลัยราชภัฏยะลา — ใส่รูปถ่ายจริงโดยคลิกขวาที่วงกลมแล้วเลือกเปลี่ยนรูปภาพ รูปจะคงตำแหน่งและขนาดเดิม");
})();

// ============================================================ 3 · ที่มาและความสำคัญ
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 1  ·  ปัญหาและที่มา", "ที่มาและความสำคัญของปัญหา");

  const colW = 5.62, yTop = 1.62, colH = 4.72;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: M, y: yTop, w: colW, h: colH, rectRadius: 0.08, fill: { color: "F1F5F9" } });
  s.addText("ระบบงานเดิม · กระดาษ", { x: M + 0.32, y: yTop + 0.26, w: colW - 0.64, h: 0.36, fontFace: F, fontSize: 16.5, bold: true, color: "475569", margin: 0 });
  ["ข้อมูลกระจัดกระจาย สูญหายง่าย", "ค้นหาประวัติย้อนหลังช้า", "คะแนนไม่เป็น Real-time", "ผู้ปกครองรับรู้ช้า"].forEach((t, i) => {
    const y = yTop + 0.95 + i * 0.88;
    s.addShape(p.shapes.OVAL, { x: M + 0.36, y: y + 0.14, w: 0.14, h: 0.14, fill: { color: "94A3B8" } });
    s.addText(t, { x: M + 0.68, y, w: colW - 1.0, h: 0.42, fontFace: F, fontSize: 16.5, bold: true, color: INK, valign: "middle", margin: 0 });
  });

  const rx = W - M - colW;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx, y: yTop, w: colW, h: colH, rectRadius: 0.08, fill: { color: GRNT } });
  s.addText("ระบบใหม่ · เว็บแอปพลิเคชัน", { x: rx + 0.32, y: yTop + 0.26, w: colW - 0.64, h: 0.36, fontFace: F, fontSize: 16.5, bold: true, color: GRN, margin: 0 });
  ["ฐานข้อมูลกลางทั้งโรงเรียน", "คำนวณคะแนนอัตโนมัติ", "แดชบอร์ดนักเรียนกลุ่มเสี่ยง", "พอร์ทัลผู้ปกครอง"].forEach((t, i) => {
    const y = yTop + 0.95 + i * 0.88;
    s.addShape(p.shapes.OVAL, { x: rx + 0.36, y: y + 0.14, w: 0.14, h: 0.14, fill: { color: GRN } });
    s.addText(t, { x: rx + 0.68, y, w: colW - 1.0, h: 0.42, fontFace: F, fontSize: 16.5, bold: true, color: INK, valign: "middle", margin: 0 });
  });

  s.addShape(p.shapes.RIGHT_ARROW, { x: 6.31, y: 3.44, w: 0.86, h: 0.66, fill: { color: GLD }, shadow: shS() });
  s.addText("พัฒนาระบบ", { x: 6.06, y: 4.16, w: 1.36, h: 0.28, fontFace: F, fontSize: 11, bold: true, color: GLD, align: "center", margin: 0 });
  foot(s, 3);
  s.addNotes("งานวินัยเดิมพึ่งพาการจดลงกระดาษ ข้อมูลแยกหลายที่ ค้นประวัติย้อนหลังยาก สรุปผลช้า ผู้ปกครองรับรู้ช้า และการติดตามกิจกรรมล้างโทษตรวจสอบไม่ทั่วถึง จึงพัฒนาเว็บแอปพลิเคชันที่ใช้ฐานข้อมูลกลาง บันทึกพฤติกรรมบวก-ลบ คำนวณคะแนนอัตโนมัติ คัดกรองนักเรียนกลุ่มเสี่ยง และให้ผู้ปกครองติดตามได้ทันที (ที่มา: รายงานวิจัย หัวข้อ 1.1)");
})();

// ============================================================ 4 · วัตถุประสงค์
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 1  ·  วัตถุประสงค์", "วัตถุประสงค์การวิจัย");

  const cards = [
    ["01", "พัฒนาระบบ", "วิเคราะห์ ออกแบบ และพัฒนาระบบงานวินัย"],
    ["02", "ประเมินคุณภาพ", "โดยผู้เชี่ยวชาญ 3 คน · 4 ด้าน"],
    ["03", "ประเมินความพึงพอใจ", "ผู้ใช้งานจริง 30 คน"],
  ];
  const cw = 3.98, gap = 0.2, y0 = 2.3, ch = 2.7;
  cards.forEach((c, i) => {
    const x = M + i * (cw + gap);
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y: y0, w: cw, h: ch, rectRadius: 0.09, fill: { color: i === 0 ? GRNT : WHT }, line: { color: i === 0 ? "CDE2D6" : LINE, width: 1 }, shadow: sh() });
    s.addShape(p.shapes.OVAL, { x: x + cw / 2 - 0.34, y: y0 - 0.34, w: 0.68, h: 0.68, fill: { color: GRN }, line: { color: GLD, width: 1.5 }, shadow: shS() });
    s.addText(c[0], { x: x + cw / 2 - 0.34, y: y0 - 0.34, w: 0.68, h: 0.68, fontFace: F, fontSize: 19, bold: true, color: WHT, align: "center", valign: "middle", margin: 0 });
    s.addText(c[1], { x: x + 0.26, y: y0 + 0.6, w: cw - 0.52, h: 0.5, fontFace: F, fontSize: 19, bold: true, color: GRN, align: "center", valign: "middle", margin: 0 });
    s.addText(c[2], { x: x + 0.3, y: y0 + 1.22, w: cw - 0.6, h: 0.9, fontFace: F, fontSize: 13.5, color: MUT, align: "center", valign: "top", margin: 0 });
  });
  foot(s, 4);
  s.addNotes("วัตถุประสงค์ 3 ข้อ ข้อแรก วิเคราะห์ ออกแบบ และพัฒนาระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษาโรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี ข้อที่สอง ประเมินคุณภาพระบบโดยผู้เชี่ยวชาญด้านเทคโนโลยีสารสนเทศ 3 คน และข้อที่สาม ประเมินความพึงพอใจของผู้ใช้งานจริง 30 คน");
})();

// ============================================================ 5 · ขอบเขตการวิจัย
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 1  ·  ขอบเขต", "ขอบเขตการวิจัย");

  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: M, y: 1.62, w: 5.7, h: 5.0, rectRadius: 0.08, fill: { color: "F8FAFC" } });
  s.addText("ผู้ใช้งาน 5 กลุ่ม", { x: M + 0.32, y: 1.88, w: 5.1, h: 0.34, fontFace: F, fontSize: 16.5, bold: true, color: INK, margin: 0 });
  const roles = [["ผู้ดูแลระบบ", "Admin"], ["ฝ่ายปกครอง", "Discipline"], ["ครูที่ปรึกษา", "Teacher"], ["นักเรียน", "Student"], ["ผู้ปกครอง", "Parent"]];
  roles.forEach((r, i) => {
    const y = 2.4 + i * 0.62;
    s.addShape(p.shapes.OVAL, { x: M + 0.34, y, w: 0.4, h: 0.4, fill: { color: GRN } });
    s.addText(String(i + 1), { x: M + 0.34, y, w: 0.4, h: 0.4, fontFace: F, fontSize: 13, bold: true, color: WHT, align: "center", valign: "middle", margin: 0 });
    s.addText([
      { text: r[0] + "   ", options: { fontSize: 15, bold: true, color: INK } },
      { text: r[1], options: { fontSize: 11.5, color: MUT } },
    ], { x: M + 0.9, y: y - 0.03, w: 4.6, h: 0.5, fontFace: F, valign: "middle", margin: 0 });
  });
  s.addText([
    { text: "30", options: { fontSize: 34, bold: true, color: GLD } },
    { text: "  คน  ·  กลุ่มตัวอย่างแบบเจาะจง (Purposive)", options: { fontSize: 13.5, bold: true, color: INK } },
  ], { x: M + 0.32, y: 5.72, w: 5.1, h: 0.66, fontFace: F, valign: "middle", margin: 0 });

  const rx = 6.56, rw = W - M - rx;
  s.addText("ฟังก์ชันหลักของระบบ", { x: rx, y: 1.72, w: rw, h: 0.36, fontFace: F, fontSize: 16.5, bold: true, color: INK, margin: 0 });
  ["บันทึกพฤติกรรมบวก / ลบ", "คำนวณคะแนน Real-time", "คำร้องอุทธรณ์คะแนน", "เช็คชื่อเข้าแถวหน้าเสาธง", "เช็คชื่อละหมาดด้วย QR Code", "พอร์ทัลผู้ปกครอง + กล่องข้อความ"].forEach((t, i) => {
    const y = 2.36 + i * 0.74;
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx, y: y + 0.05, w: 0.3, h: 0.3, rectRadius: 0.05, fill: { color: GRNT } });
    s.addShape(p.shapes.RECTANGLE, { x: rx + 0.115, y: y + 0.165, w: 0.07, h: 0.07, fill: { color: GRN } });
    s.addText(t, { x: rx + 0.48, y, w: rw - 0.5, h: 0.42, fontFace: F, fontSize: 16, bold: true, color: INK, valign: "middle", margin: 0 });
    if (i < 5) s.addShape(p.shapes.RECTANGLE, { x: rx + 0.48, y: y + 0.62, w: rw - 0.62, h: 0.008, fill: { color: "EDF2F7" } });
  });
  foot(s, 5);
  s.addNotes("ระบบรองรับผู้ใช้ 5 กลุ่ม ได้แก่ ผู้ดูแลระบบ ฝ่ายปกครอง ครูที่ปรึกษา นักเรียน และผู้ปกครอง กลุ่มตัวอย่าง 30 คน (นักเรียน ผู้ปกครอง ฝ่ายปกครอง ครูประจำชั้น) เลือกแบบเจาะจง ฟังก์ชันหลัก: บันทึกพฤติกรรมพร้อมหลักฐานและส่งอนุมัติ คำนวณคะแนนจาก 100 คะแนนแบบทันที ยื่นอุทธรณ์โต้แย้ง เช็คชื่อเข้าแถวรายวัน เช็คชื่อละหมาดซุฮรี/อัศรีด้วย QR และพอร์ทัลผู้ปกครองที่สลับดูบุตรหลานได้");
})();

// ============================================================ 6 · ทฤษฎีและเครื่องมือ
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 2  ·  ทฤษฎีและเครื่องมือ", "แนวคิด ทฤษฎี และเครื่องมือที่ใช้");

  const LOGOS = AST + "/logos";
  const cw = 6.06, chh = 2.42, gx = 0.21, gy = 0.24, y0 = 1.72;
  const qX = i => M + (i % 2) * (cw + gx), qY = i => y0 + Math.floor(i / 2) * (chh + gy);
  const card = i => s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: qX(i), y: qY(i), w: cw, h: chh, rectRadius: 0.09, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  const qHead = (i, t) => s.addText(t, { x: qX(i) + 0.3, y: qY(i) + 0.24, w: cw - 0.6, h: 0.36, fontFace: F, fontSize: 16, bold: true, color: INK, margin: 0 });
  function chip(x, y, size, img) {
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y, w: size, h: size, rectRadius: 0.08, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: shS() });
    const g = size * 0.66;
    s.addImage({ path: img, x: x + (size - g) / 2, y: y + (size - g) / 2, w: g, h: g });
  }

  card(0); qHead(0, "เครื่องมือพัฒนา");
  chip(qX(0) + 0.42, qY(0) + 0.86, 1.06, LOGOS + "/vscode.png");
  s.addText("Visual Studio Code", { x: qX(0) + 1.78, y: qY(0) + 1.0, w: 3.9, h: 0.4, fontFace: F, fontSize: 17, bold: true, color: INK, margin: 0 });
  s.addText("โปรแกรมแก้ไขโค้ดหลัก", { x: qX(0) + 1.78, y: qY(0) + 1.44, w: 3.9, h: 0.32, fontFace: F, fontSize: 12.5, color: MUT, margin: 0 });

  card(1); qHead(1, "ภาษาและเทคโนโลยี");
  const langs = [["html5.png", "HTML"], ["css.png", "CSS"], ["javascript.png", "JavaScript"], ["react.png", "React.JS"], ["php.png", "PHP"]];
  const cs = 0.82, cg = 0.22, sx = qX(1) + (cw - (5 * cs + 4 * cg)) / 2;
  langs.forEach((l, i) => {
    const x = sx + i * (cs + cg);
    chip(x, qY(1) + 0.8, cs, LOGOS + "/" + l[0]);
    s.addText(l[1], { x: x - 0.14, y: qY(1) + 1.7, w: cs + 0.28, h: 0.26, fontFace: F, fontSize: 10.5, bold: true, color: INK, align: "center", margin: 0 });
  });
  s.addText("Frontend  ·  Backend", { x: qX(1) + 0.3, y: qY(1) + 2.0, w: cw - 0.6, h: 0.3, fontFace: F, fontSize: 11.5, color: MUT, align: "center", margin: 0 });

  card(2); qHead(2, "ระบบจัดการฐานข้อมูล");
  chip(qX(2) + 0.42, qY(2) + 0.86, 1.06, LOGOS + "/mysql.png");
  s.addText("MySQL", { x: qX(2) + 1.78, y: qY(2) + 1.0, w: 3.9, h: 0.4, fontFace: F, fontSize: 17, bold: true, color: INK, margin: 0 });
  s.addText("16 ตารางฐานข้อมูลหลัก", { x: qX(2) + 1.78, y: qY(2) + 1.44, w: 3.9, h: 0.32, fontFace: F, fontSize: 12.5, color: MUT, margin: 0 });

  card(3); qHead(3, "ระเบียบวิธีพัฒนาระบบ (SDLC)");
  const sw = 0.82, sg = 0.14, sx4 = qX(3) + 0.4;
  for (let i = 0; i < 5; i++) {
    const x = sx4 + i * (sw + sg), y = qY(3) + 0.8 + i * 0.17;
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y, w: sw, h: 0.28, rectRadius: 0.05, fill: { color: i === 4 ? GLDT : GRNT } });
    s.addText(String(i + 1), { x, y, w: sw, h: 0.28, fontFace: F, fontSize: 10.5, bold: true, color: i === 4 ? "92400E" : GRN, align: "center", valign: "middle", margin: 0 });
  }
  s.addText("วิเคราะห์ด้วย DFD · ER-Diagram", { x: qX(3) + 0.4, y: qY(3) + 2.0, w: cw - 0.8, h: 0.3, fontFace: F, fontSize: 11.5, color: MUT, margin: 0 });
  foot(s, 6);
  s.addNotes("เครื่องมือหลักคือ Visual Studio Code ภาษาที่ใช้ได้แก่ HTML CSS JavaScript React.JS (ฝั่ง Frontend) และ PHP (ฝั่ง Backend) โดยใช้ MySQL จัดการฐานข้อมูล 16 ตารางหลัก กระบวนการพัฒนาใช้วงจร SDLC เป็นกรอบการทำงาน วิเคราะห์ระบบด้วย DFD และ ER-Diagram");
})();

// ============================================================ 7 · วิธีดำเนินการวิจัย
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 3  ·  วิธีดำเนินการวิจัย", "ขั้นตอนการดำเนินการวิจัยตามวงจร SDLC");

  const steps = [
    ["ศึกษาและกำหนดปัญหา", "แผนผังก้างปลา", false],
    ["ศึกษาความเป็นไปได้", "เทคนิค · การใช้งาน", false],
    ["วิเคราะห์ระบบ", "DFD · ER-Diagram", true],
    ["ออกแบบระบบ", "UI · ฐานข้อมูล", false],
    ["พัฒนาระบบ", "PHP · React · MySQL", false],
    ["ทดสอบและติดตั้ง", "ทดสอบฟังก์ชัน", false],
    ["ประเมินระบบ", "ผู้เชี่ยวชาญ · ผู้ใช้งาน", true],
  ];
  const lx = M, lw = 7.35;
  steps.forEach((st, i) => {
    const y = 1.66 + i * 0.715;
    s.addShape(p.shapes.OVAL, { x: lx, y: y + 0.05, w: 0.46, h: 0.46, fill: { color: st[2] ? GLD : GRN } });
    s.addText(String(i + 1), { x: lx, y: y + 0.05, w: 0.46, h: 0.46, fontFace: F, fontSize: 15, bold: true, color: WHT, align: "center", valign: "middle", margin: 0 });
    s.addText(st[0], { x: lx + 0.66, y, w: 3.3, h: 0.52, fontFace: F, fontSize: 15.5, bold: true, color: INK, valign: "middle", margin: 0 });
    s.addText(st[1], { x: lx + 4.0, y, w: lw - 4.0, h: 0.52, fontFace: F, fontSize: 12, color: MUT, valign: "middle", margin: 0 });
    if (i < steps.length - 1) s.addShape(p.shapes.RECTANGLE, { x: lx + 0.225, y: y + 0.54, w: 0.012, h: 0.185, fill: { color: "CBD5E1" } });
  });

  const rx = 8.2, rw = W - M - rx;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx, y: 1.66, w: rw, h: 3.3, rectRadius: 0.08, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  s.addImage({ path: AST + "/fishbone.png", x: rx + 0.18, y: 1.92, w: rw - 0.36, h: (rw - 0.36) / 2.563 });
  s.addText("แผนผังก้างปลา (ภาพที่ 3.2)", { x: rx + 0.2, y: 3.62, w: rw - 0.4, h: 0.32, fontFace: F, fontSize: 13.5, bold: true, color: INK, margin: 0 });
  s.addText("วิเคราะห์สาเหตุปัญหาของระบบเดิม", { x: rx + 0.2, y: 3.96, w: rw - 0.4, h: 0.3, fontFace: F, fontSize: 11.5, color: MUT, margin: 0 });
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx, y: 5.2, w: rw, h: 1.46, rectRadius: 0.08, fill: { color: GRNT } });
  s.addText([
    { text: "7 ขั้นตอน", options: { fontSize: 26, bold: true, color: GRN, breakLine: true } },
    { text: "ครบวงจร ศึกษาปัญหา ถึง ประเมินผล", options: { fontSize: 11.5, color: MUT } },
  ], { x: rx + 0.28, y: 5.38, w: rw - 0.56, h: 1.1, fontFace: F, margin: 0 });
  foot(s, 7);
  s.addNotes("ดำเนินการ 7 ขั้นตอนตาม SDLC เริ่มจากศึกษาปัญหาด้วยแผนผังก้างปลา ศึกษาความเป็นไปได้ด้านเทคนิคและประโยชน์ใช้งาน วิเคราะห์ระบบด้วย DFD และ ER-Diagram ออกแบบหน้าจอและฐานข้อมูล พัฒนาด้วย PHP React.JS MySQL ทดสอบฟังก์ชันและติดตั้งบนเว็บเซิร์ฟเวอร์ และประเมินโดยผู้เชี่ยวชาญและผู้ใช้งาน");
})();

// ============================================================ 8 · ผลการวิเคราะห์และออกแบบ
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 3  ·  การวิเคราะห์และออกแบบ", "ผลการวิเคราะห์และออกแบบระบบ");

  const dw = 6.95, dx = M, dy = 1.56, dimg = dw - 0.3, dhimg = dimg / 2.094;
  const dCardH = 0.15 + dhimg + 0.15;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: dx, y: dy, w: dw, h: dCardH, rectRadius: 0.07, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  s.addImage({ path: AST + "/dfd_level0.png", x: dx + 0.15, y: dy + 0.15, w: dimg, h: dhimg });
  caption(s, dx + 0.05, dy + dCardH + 0.14, dw - 0.1, "ภาพที่ 3.5", "DFD Level 0", "การไหลของข้อมูลระหว่างผู้ใช้กับระบบ");

  const ew = 5.62, ex = W - M - ew, eimg = ew - 0.3, ehimg = eimg / 1.614;
  const eCardH = 0.15 + ehimg + 0.15;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: ex, y: dy, w: ew, h: eCardH, rectRadius: 0.07, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  s.addImage({ path: AST + "/er_diagram.png", x: ex + 0.15, y: dy + 0.15, w: eimg, h: ehimg });
  caption(s, ex + 0.05, dy + eCardH + 0.14, ew - 0.1, "ภาพที่ 3.10", "ER-Diagram", "โครงสร้างฐานข้อมูล 16 ตาราง");

  s.addShape(p.shapes.RECTANGLE, { x: 0, y: 6.42, w: W, h: 1.08, fill: { color: GRNT } });
  const stats = [["5", "กลุ่มผู้ใช้งาน"], ["16", "ตารางฐานข้อมูลหลัก"], ["100", "คะแนนเริ่มต้น / ภาคเรียน"]];
  stats.forEach((st, i) => {
    const x = 0.9 + i * 4.2;
    s.addText([
      { text: st[0], options: { fontSize: 30, bold: true, color: GRN } },
      { text: "   " + st[1], options: { fontSize: 12.5, color: INK } },
    ], { x, y: 6.42, w: 4.0, h: 1.08, fontFace: F, valign: "middle", margin: 0 });
  });
  foot(s, 8);
  s.addNotes("ภาพซ้าย DFD ระดับ 0 แสดงบริบทการแลกเปลี่ยนข้อมูลระหว่างผู้ใช้ทั้ง 5 กลุ่มกับระบบ ภาพขวา ER-Diagram แสดงความสัมพันธ์ของข้อมูลในฐานข้อมูล 16 ตารางหลัก ให้ชี้ความสัมพันธ์สำคัญจากภาพ ไม่ต้องอ่านชื่อทุกตาราง");
})();

// ============================================================ 9 · ผลการพัฒนา 1 — เข้าสู่ระบบ + ผู้ดูแล
(() => {
  const s = p.addSlide();
  s.background = { color: "F8FAFC" };
  header(s, "บทที่ 4  ·  ผลการพัฒนาระบบ (1/4)", "การเข้าสู่ระบบและงานพื้นฐานของผู้ดูแลระบบ");

  const cw = 5.92, y0 = 1.62;
  shot(s, M, y0, cw, IMG + "/fig_01.png");
  shot(s, W - M - cw, y0, cw, IMG + "/fig_02.png");

  caption(s, M, 5.85, cw - 0.2, "หน้าเข้าสู่ระบบ", "แยกสิทธิ์ตามบทบาท (RBAC)", "รองรับผู้ใช้ที่มีหลายบทบาท");
  caption(s, W - M - cw, 5.85, cw - 0.2, "แดชบอร์ดผู้ดูแลระบบ", "ภาพรวมข้อมูลทั้งโรงเรียน", "ผู้ใช้ นักเรียน ครู ผู้ปกครอง");
  foot(s, 9);
  s.addNotes("ผู้ใช้ทุกกลุ่มเข้าสู่ระบบผ่านหน้าล็อกอินเดียวกัน ระบบตรวจสอบสิทธิ์และพาไปยังแดชบอร์ดของแต่ละบทบาท ผู้ที่มีหลายบทบาทเลือกได้ก่อนเข้าใช้งาน ส่วนแดชบอร์ดผู้ดูแลระบบสรุปจำนวนผู้ใช้และข้อมูลหลักของโรงเรียน รวมถึงจัดการข้อมูลนักเรียน ครู และการพิมพ์บัตร QR");
})();

// ============================================================ 10 · ผลการพัฒนา 2 — ฝ่ายปกครอง
(() => {
  const s = p.addSlide();
  s.background = { color: "F8FAFC" };
  header(s, "บทที่ 4  ·  ผลการพัฒนาระบบ (2/4)", "กลุ่มฝ่ายปกครอง — หัวใจของการบริหารงานวินัย");

  const cw = 4.0, gap = 0.165, y0 = 1.58;
  const xs = [M, M + cw + gap, M + 2 * (cw + gap)];
  shot(s, xs[0], y0, cw, IMG + "/fig_13.png");
  shot(s, xs[1], y0, cw, IMG + "/fig_17.png");
  shot(s, xs[2], y0, cw, IMG + "/fig_20.png");

  const cy = 4.72;
  caption(s, xs[0], cy, cw - 0.15, "แดชบอร์ดฝ่ายปกครอง", "สถิติวินัยทั้งโรงเรียน", "ติดตามแบบ Real-time");
  caption(s, xs[1], cy, cw - 0.15, "บันทึกพฤติกรรม", "ตัด/เพิ่มคะแนนพร้อมหลักฐาน", "ครูส่งเรื่อง ฝ่ายปกครองอนุมัติ");
  caption(s, xs[2], cy, cw - 0.15, "รายงานสรุป", "กรองตามชั้น / ห้องเรียน", "ส่งออก PDF / Excel");
  foot(s, 10);
  s.addNotes("หน้าบันทึกพฤติกรรมเป็นแกนของระบบ ฝ่ายปกครองบันทึกเหตุการณ์บวก-ลบ แนบรูปหลักฐาน ครูส่งเรื่องเข้าสายอนุมัติก่อนตัดคะแนนจริง แดชบอร์ดสรุปคดี คะแนนเฉลี่ย และนักเรียนกลุ่มเสี่ยง ส่วนรายงานสรุปกรองสถิติตามชั้น/ห้องและส่งออกเป็น PDF หรือ Excel ได้");
})();

// ============================================================ 11 · ผลการพัฒนา 3 — ครู
(() => {
  const s = p.addSlide();
  s.background = { color: "F8FAFC" };
  header(s, "บทที่ 4  ·  ผลการพัฒนาระบบ (3/4)", "กลุ่มครูผู้สอนและครูที่ปรึกษา");

  const cw = 5.92, y0 = 1.62;
  shot(s, M, y0, cw, IMG + "/fig_22.png");
  shot(s, W - M - cw, y0, cw, IMG + "/fig_23.png");

  caption(s, M, 5.85, cw - 0.2, "เช็คชื่อเข้าแถวประจำวัน", "มา / สาย / ขาด ทั้งห้องเรียน", "ข้อมูลสะสมอัตโนมัติ");
  caption(s, W - M - cw, 5.85, cw - 0.2, "บันทึกพฤติกรรมโดยครู", "เลือกเกณฑ์ + แนบหลักฐาน", "ส่งให้ฝ่ายปกครองอนุมัติ");
  foot(s, 11);
  s.addNotes("ครูที่ปรึกษาใช้หน้าเช็คชื่อบันทึกสถานะการมาเรียนรายวันของทั้งห้อง ข้อมูลสะสมเชื่อมกับคะแนนความประพฤติอัตโนมัติ เมื่อพบพฤติกรรมที่ต้องบันทึก ครูเลือกเกณฑ์ตามที่ฝ่ายปกครองกำหนด แนบหลักฐานภาพถ่าย แล้วส่งเรื่องเข้าสายอนุมัติ ทำให้ข้อมูลถูกต้องและตรวจสอบย้อนหลังได้");
})();

// ============================================================ 12 · ผลการพัฒนา 4 — นักเรียน + ผู้ปกครอง
(() => {
  const s = p.addSlide();
  s.background = { color: "F8FAFC" };
  header(s, "บทที่ 4  ·  ผลการพัฒนาระบบ (4/4)", "กลุ่มนักเรียนและผู้ปกครอง");

  const cw = 4.0, gap = 0.165, y0 = 1.58;
  const xs = [M, M + cw + gap, M + 2 * (cw + gap)];
  shot(s, xs[0], y0, cw, IMG + "/fig_26.png");
  shot(s, xs[1], y0, cw, IMG + "/fig_28.png");
  shot(s, xs[2], y0, cw, IMG + "/fig_31.png");

  const cy = 4.72;
  caption(s, xs[0], cy, cw - 0.15, "แดชบอร์ดนักเรียน", "คะแนนคงเหลือจาก 100", "ยื่นอุทธรณ์พร้อมหลักฐาน");
  caption(s, xs[1], cy, cw - 0.15, "บัตร QR นักเรียน", "เช็คชื่อละหมาดซุฮรี / อัศรี", "สแกนผ่านกล้อง / บาร์โค้ด");
  caption(s, xs[2], cy, cw - 0.15, "พอร์ทัลผู้ปกครอง", "คะแนน + การเข้าเรียน", "สลับดูบุตรหลานหลายคน");
  foot(s, 12);
  s.addNotes("นักเรียนดูคะแนนคงเหลือและประวัติของตนเอง ยื่นอุทธรณ์พร้อมหลักฐานได้ และใช้บัตร QR เช็คชื่อละหมาด ส่วนผู้ปกครองเข้าพอร์ทัลดูคะแนน ทัณฑ์บน และการเข้าเรียน สลับดูบุตรหลานหลายคนในบัญชีเดียว พร้อมกล่องข้อความติดต่อครูที่ปรึกษาและฝ่ายปกครองโดยตรง");
})();

// ============================================================ 13 · การประเมินระบบ
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 4  ·  การประเมินระบบ", "ผลการประเมินคุณภาพและความพึงพอใจ");

  const cw = 6.0, y0 = 1.66, ch = 4.1;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: M, y: y0, w: cw, h: ch, rectRadius: 0.09, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  s.addText([
    { text: "ผู้เชี่ยวชาญ  ", options: { fontSize: 24, bold: true, color: GRN } },
    { text: "3 คน · 4 ด้าน", options: { fontSize: 15, color: MUT } },
  ], { x: M + 0.3, y: y0 + 0.22, w: cw - 0.6, h: 0.46, fontFace: F, margin: 0 });
  ["ด้านความปลอดภัย", "ด้านความถูกต้อง", "ด้านการออกแบบ", "ด้านการนำไปใช้ประโยชน์"].forEach((d, i) => {
    const y = y0 + 0.95 + i * 0.56;
    s.addShape(p.shapes.OVAL, { x: M + 0.32, y: y + 0.1, w: 0.11, h: 0.11, fill: { color: GRN } });
    s.addText(d, { x: M + 0.56, y, w: 2.9, h: 0.34, fontFace: F, fontSize: 13.5, bold: true, color: INK, valign: "middle", margin: 0 });
    s.addText("x̅ = ______    S.D. = ______", { x: M + 3.4, y, w: 2.4, h: 0.34, fontFace: F, fontSize: 12, color: "B45309", valign: "middle", margin: 0 });
  });
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: M + 0.3, y: y0 + 3.24, w: cw - 0.6, h: 0.62, rectRadius: 0.07, fill: { color: GLDT } });
  s.addText("ภาพรวม  x̅ = ______    S.D. = ______    ระดับ = ________", { x: M + 0.5, y: y0 + 3.24, w: cw - 1.0, h: 0.62, fontFace: F, fontSize: 12.5, bold: true, color: "92400E", valign: "middle", margin: 0 });

  const rx = W - M - cw;
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx, y: y0, w: cw, h: ch, rectRadius: 0.09, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
  s.addText([
    { text: "ผู้ใช้งานจริง  ", options: { fontSize: 24, bold: true, color: GRN } },
    { text: "30 คน · 2 ด้าน", options: { fontSize: 15, color: MUT } },
  ], { x: rx + 0.3, y: y0 + 0.22, w: cw - 0.6, h: 0.46, fontFace: F, margin: 0 });
  ["ด้านกระบวนการทำงานของระบบ", "ด้านการติดต่อระบบงาน"].forEach((d, i) => {
    const y = y0 + 1.05 + i * 0.62;
    s.addShape(p.shapes.OVAL, { x: rx + 0.32, y: y + 0.1, w: 0.11, h: 0.11, fill: { color: GRN } });
    s.addText(d, { x: rx + 0.56, y, w: 3.1, h: 0.34, fontFace: F, fontSize: 13.5, bold: true, color: INK, valign: "middle", margin: 0 });
    s.addText("x̅ = ______    S.D. = ______", { x: rx + 3.5, y, w: 2.3, h: 0.34, fontFace: F, fontSize: 12, color: "B45309", valign: "middle", margin: 0 });
  });
  s.addShape(p.shapes.ROUNDED_RECTANGLE, { x: rx + 0.3, y: y0 + 2.5, w: cw - 0.6, h: 0.62, rectRadius: 0.07, fill: { color: GRNT } });
  s.addText("ภาพรวม  x̅ = ______    S.D. = ______    ระดับ = ________", { x: rx + 0.5, y: y0 + 2.5, w: cw - 1.0, h: 0.62, fontFace: F, fontSize: 12.5, bold: true, color: GRN, valign: "middle", margin: 0 });

  s.addText("กรอกค่าจริงจากตาราง 4.1–4.2 ในรายงาน ก่อนวันนำเสนอ", { x: M, y: 6.1, w: W - 2 * M, h: 0.32, fontFace: F, fontSize: 11, color: "B45309", margin: 0 });
  foot(s, 13);
  s.addNotes("รายงานผลประเมินคุณภาพโดยผู้เชี่ยวชาญ 3 คน 4 ด้าน (ความปลอดภัย ความถูกต้อง การออกแบบ การนำไปใช้ประโยชน์) และความพึงพอใจของผู้ใช้ 30 คน 2 ด้าน (กระบวนการทำงาน การติดต่อระบบงาน) โดยวิเคราะห์ด้วยค่าเฉลี่ยและส่วนเบี่ยงเบนมาตรฐาน แปลผลตามเกณฑ์ 5 ระดับ — ตารางในรายงานยังว่าง ต้องกรอกค่าจริงก่อนนำเสนอ ห้ามอ่านตัวเลขตัวอย่างเป็นผลของโครงงาน");
})();

// ============================================================ 14 · สรุป อภิปราย ข้อเสนอแนะ
(() => {
  const s = p.addSlide();
  s.background = { color: BG };
  header(s, "บทที่ 5  ·  บทสรุป", "สรุปผล อภิปรายผล และข้อเสนอแนะ");

  s.addShape(p.shapes.RECTANGLE, { x: 0, y: 1.6, w: W, h: 1.2, fill: { color: GRNT } });
  s.addShape(p.shapes.OVAL, { x: M, y: 1.87, w: 0.66, h: 0.66, fill: { color: GRN } });
  s.addText("✓", { x: M, y: 1.87, w: 0.66, h: 0.66, fontFace: F, fontSize: 22, bold: true, color: WHT, align: "center", valign: "middle", margin: 0 });
  s.addText("สรุปผล", { x: M + 0.92, y: 1.74, w: 5, h: 0.34, fontFace: F, fontSize: 16, bold: true, color: GRN, margin: 0 });
  s.addText("รวมงานวินัยไว้ที่เดียว ทดแทนสมุดกระดาษได้จริง", { x: M + 0.92, y: 2.1, w: 11.3, h: 0.44, fontFace: F, fontSize: 15, color: INK, margin: 0 });

  s.addShape(p.shapes.RECTANGLE, { x: 0, y: 3.0, w: W, h: 1.2, fill: { color: GLDT } });
  s.addShape(p.shapes.OVAL, { x: M, y: 3.27, w: 0.66, h: 0.66, fill: { color: GLD } });
  s.addText("i", { x: M, y: 3.27, w: 0.66, h: 0.66, fontFace: "Georgia", fontSize: 22, bold: true, italic: true, color: WHT, align: "center", valign: "middle", margin: 0 });
  s.addText("อภิปรายผล", { x: M + 0.92, y: 3.14, w: 5, h: 0.34, fontFace: F, fontSize: 16, bold: true, color: "92400E", margin: 0 });
  s.addText("PHP + MySQL คำนวณคะแนน Real-time ตอบโจทย์โรงเรียน", { x: M + 0.92, y: 3.5, w: 11.3, h: 0.44, fontFace: F, fontSize: 15, color: INK, margin: 0 });

  s.addText("ข้อเสนอแนะเพื่อพัฒนาต่อยอด", { x: M, y: 4.56, w: 6, h: 0.36, fontFace: F, fontSize: 16, bold: true, color: INK, margin: 0 });
  const recs = [
    ["1", "Mobile App", "แอปมือถือ iOS / Android"],
    ["2", "แจ้งเตือนอัตโนมัติ", "LINE / SMS เมื่อตัดคะแนน"],
    ["3", "ปัญญาประดิษฐ์", "พยากรณ์นักเรียนกลุ่มเสี่ยง"],
  ];
  const cw = 3.98, gap = 0.2, y0 = 5.04, ch = 1.5;
  recs.forEach((r, i) => {
    const x = M + i * (cw + gap);
    s.addShape(p.shapes.ROUNDED_RECTANGLE, { x, y: y0, w: cw, h: ch, rectRadius: 0.09, fill: { color: WHT }, line: { color: LINE, width: 1 }, shadow: sh() });
    s.addShape(p.shapes.OVAL, { x: x + 0.26, y: y0 + 0.28, w: 0.44, h: 0.44, fill: { color: GRNT } });
    s.addText(r[0], { x: x + 0.26, y: y0 + 0.28, w: 0.44, h: 0.44, fontFace: F, fontSize: 15, bold: true, color: GRN, align: "center", valign: "middle", margin: 0 });
    s.addText(r[1], { x: x + 0.84, y: y0 + 0.3, w: cw - 1.0, h: 0.4, fontFace: F, fontSize: 15.5, bold: true, color: INK, valign: "middle", margin: 0 });
    s.addText(r[2], { x: x + 0.28, y: y0 + 0.88, w: cw - 0.56, h: 0.4, fontFace: F, fontSize: 12, color: MUT, margin: 0 });
  });
  foot(s, 14);
  s.addNotes("สรุป: ระบบรวมข้อมูลการทำงานวินัยไว้จุดเดียว ลดการพึ่งพาเอกสารกระดาษ ค้นหาและสรุปผลได้เร็วขึ้น โปร่งใสและตรวจสอบย้อนหลังได้ อภิปราย: PHP กับ MySQL สนับสนุนการจัดเก็บและการเข้าถึงฐานข้อมูลกลาง การคำนวณคะแนน Real-time และพอร์ทัลผู้ปกครองเพิ่มความโปร่งใสและการร่วมดูแล ข้อเสนอแนะ: พัฒนาแอปมือถือ เชื่อมแจ้งเตือน LINE/SMS ทันทีที่ตัดคะแนน และใช้ AI พยากรณ์แนวโน้มพฤติกรรมนักเรียนกลุ่มเสี่ยงเพื่อการแนะแนวเชิงป้องกัน");
})();

// ============================================================ 15 · ปิดท้าย
(() => {
  const s = p.addSlide();
  s.background = { color: GRND };
  s.addShape(p.shapes.OVAL, { x: -1.6, y: -2.2, w: 5.4, h: 5.4, fill: { color: GRN, transparency: 62 } });
  s.addShape(p.shapes.OVAL, { x: 10.2, y: 4.6, w: 5.6, h: 5.6, fill: { color: GRN, transparency: 68 } });

  s.addText("ขอบพระคุณสำหรับการรับฟัง", { x: 1.5, y: 2.42, w: W - 3, h: 0.94, fontFace: F, fontSize: 44, bold: true, color: WHT, align: "center", margin: 0 });
  s.addText("พร้อมรับคำถามและข้อเสนอแนะจากคณะกรรมการ (Q&A)", { x: 1.5, y: 3.44, w: W - 3, h: 0.42, fontFace: F, fontSize: 17, bold: true, color: "F5C063", align: "center", margin: 0 });

  const d = 1.0, cy = 5.3;
  s.addShape(p.shapes.OVAL, { x: W / 2 - d - 0.35, y: cy - d / 2, w: d, h: d, fill: { color: WHT }, shadow: shS() });
  s.addImage({ path: LOGO_U, x: W / 2 - d - 0.35 + d / 2 - 0.29, y: cy - 0.38, w: 0.58, h: 0.76 });
  s.addShape(p.shapes.OVAL, { x: W / 2 + 0.35, y: cy - d / 2, w: d, h: d, fill: { color: WHT }, shadow: shS() });
  s.addImage({ path: LOGO_S, x: W / 2 + 0.35 + d / 2 - 0.4, y: cy - 0.3, w: 0.8, h: 0.6 });

  s.addText("นายตอริก ลือแมะ (406665012)  ·  นายบุสริน มูซอ (406665027)  |  อาจารย์ที่ปรึกษา: อาจารย์แพรวศรี เดิมราช · อาจารย์รุสนี กาแมแล",
    { x: 1.0, y: 6.24, w: W - 2, h: 0.32, fontFace: F, fontSize: 11.5, color: "CFE0D6", align: "center", margin: 0 });
  s.addText("สาขาวิชาเทคโนโลยีสารสนเทศ  ·  คณะวิทยาศาสตร์ เทคโนโลยีและการเกษตร  ·  มหาวิทยาลัยราชภัฏยะลา",
    { x: 1.0, y: 6.58, w: W - 2, h: 0.3, fontFace: F, fontSize: 10.5, color: "9DBBA9", align: "center", margin: 0 });
  s.addNotes("ขอบพระคุณคณะกรรมการและผู้ฟังทุกท่าน เปิดโอกาสให้ซักถามและให้ข้อเสนอแนะครับ");
})();

p.writeFile({ fileName: ROOT + "/docs/Research_Presentation_Student_Discipline_System.pptx" })
  .then(() => console.log("DONE"))
  .catch(e => { console.error(e); process.exit(1); });
