import fs from 'node:fs/promises';
import {pathToFileURL} from 'node:url';
const ROOT='C:/xampp/htdocs/student-discipline-system', TMP=ROOT+'/tmp/research-slides';
const SKILL='C:/Users/บุสริน/.codex/plugins/cache/openai-primary-runtime/presentations/26.909.11814/skills/presentations';
const PKG='C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';
const {Presentation,PresentationFile}=await import(pathToFileURL(PKG).href);
const {finalizePresentation}=await import(pathToFileURL(SKILL+'/container_tools/artifact_tool_utils.mjs').href);
const layout=JSON.parse(await fs.readFile(TMP+'/native-layout.json','utf8'));
const P=Presentation.create({slideSize:{width:1280,height:720}});
const G='#143F38',C='#F8F6EE',A='#BD9C59',M='#62766F',L='#D9DED6';
let S;
function shape(geometry,x,y,w,h,fill='none',stroke='none',weight=0,rotation=0){return S.shapes.add({geometry,position:{left:x,top:y,width:w,height:h,rotation},fill,line:{fill:stroke,width:weight}});}
function text(s,x,y,w,h,size=24,color=G,bold=false,center=false,font='Tahoma',vertical='middle',align=null){
 const sh=shape('textbox',x,y,w,h);sh.text=s;
 sh.text.style={typeface:font,fontSize:size,color,bold,alignment:align||(center?'center':'left'),verticalAlignment:vertical,autoFit:'none',wrap:'none',insets:{top:0,bottom:0,left:0,right:0}};return sh;
}
async function image(path,x,y,w,h,cover=false,alt='ภาพประกอบงานวิจัย'){
 S.images.add({blob:new Uint8Array(await fs.readFile(path)),contentType:/\.jpe?g$/i.test(path)?'image/jpeg':'image/png',alt,fit:cover?'cover':'contain',position:{left:x,top:y,width:w,height:h}});
}
function base(n,title,source,section){
 text(section,840,26,384,32,17,M,false,false,'Tahoma','middle','right');
 text(title,56,78,1168,65,42,G,true);shape('line',56,661,1168,0,'none',L,.8);
 text(source,56,675,1060,26,13,M);text(String(n).padStart(2,'0')+' / 20',1144,675,80,26,16,A,false,true,'Arial');
}
const sdlcSource=await fs.readFile(TMP+'/build_sdlc_native.mjs','utf8');
const sdlcBody=sdlcSource.slice(sdlcSource.indexOf("shape('rect',0,0"),sdlcSource.indexOf('const candidate=')).split('\n').filter(line=>!line.includes("text('STUDENT DISCIPLINE")).join('\n').replace("'08 / 18'","'08 / 20'");
const sdlcBuild=new Function('S','shape','text','G','C','A','M',sdlcBody);
const times=[25,20,35,20,25,30,25,55,35,35,20,25,20,20,20,20,35,35,40,5];
const notes={
 1:'นำเสนอระบบสารสนเทศการบริหารงานวินัยและติดตามพฤติกรรมนักเรียน กรณีศึกษาโรงเรียนศิริราษฎร์สามัคคี จังหวัดปัตตานี',
 2:'แนะนำผู้วิจัย นายตอริก ลือแมะ นายบุสริน มูซอ และที่ปรึกษา อ.แพรวศรี เดิมราช อ.รุสนี กาแมแล',
 3:'ระบบเดิมบันทึกกระดาษ ข้อมูลกระจัดกระจาย ค้นหาและสรุปช้า ผู้ใช้งานคือโรงเรียน ครู นักเรียน และผู้ปกครอง ระบบใหม่รวมข้อมูลวินัยเพื่อช่วยติดตามและตรวจสอบ',
 4:'ใช้แผนผังก้างปลาสรุปสาเหตุของปัญหาเดิม ไม่อ่านรายละเอียดทุกกิ่ง',
 5:'สามวัตถุประสงค์: วิเคราะห์ออกแบบและพัฒนาระบบ ประเมินคุณภาพโดยผู้เชี่ยวชาญ และประเมินความพึงพอใจของผู้ใช้งาน',
 6:'ผู้ใช้ 5 บทบาท ประชากรที่ศึกษา: นักเรียน ผู้ปกครอง ครูประจำชั้น และฝ่ายปกครอง กลุ่มตัวอย่าง 30 คน เลือกแบบเจาะจง: นักเรียน12 ผู้ปกครอง12 ครู3 ฝ่ายปกครอง3 ตามบท3 หน้า58',
 7:'เลือกแนวคิดที่ใช้จริง ได้แก่ SDLC การจัดการฐานข้อมูล การแบ่งสิทธิ์ และการประเมินระบบ ภาพโลโก้แสดงเครื่องมือพัฒนาเว็บตามเล่มและโครงงาน',
 8:'อธิบาย SDLC 7 ขั้นตามกิจกรรมของโครงงาน ตั้งแต่ปัญหากระดาษ วิเคราะห์ผู้ใช้5บทบาท ออกแบบDFD/ฐานข้อมูล พัฒนาPHP/MySQL ทดสอบ ติดตั้ง และนำข้อเสนอแนะมาปรับปรุง ไม่อ้างว่าประเมินเสร็จแล้ว',
 9:'งานที่เกี่ยวข้องสามเรื่องสนับสนุนการใช้ SDLC การติดตามเข้าเรียนร่วมกับผู้ปกครอง และการสรุปพฤติกรรมรายบุคคล ไม่ยกค่าคะแนนของงานอื่นเป็นผลของโครงงานนี้',
 10:'DFD Level0 แสดงการแลกเปลี่ยนข้อมูลระหว่างระบบกับผู้ใช้แต่ละบทบาท เลือกอธิบายข้อมูลเข้าและรายงานผลที่สำคัญ',
 11:'ฝ่ายปกครองใช้แดชบอร์ดดูคะแนนและสถิติในภาพเดียว ช่วยเห็นภาพรวมสำหรับติดตามนักเรียน',
 12:'ครูและฝ่ายปกครองบันทึกพฤติกรรมบวก/ลบ อ้างอิงเกณฑ์คะแนนและหลักฐาน เพื่อลดข้อมูลแยกหลายแฟ้ม',
 13:'นักเรียนยื่นอุทธรณ์ ฝ่ายปกครองตรวจสอบหลักฐานและสถานะคำร้อง ช่วยให้ตรวจสอบกระบวนการได้',
 14:'ครูบันทึกการเข้าเรียนและเปิดประวัติย้อนหลัง เพื่อติดตามการมาเรียนรายบุคคล',
 15:'ครูติดตามการร่วมกิจกรรมละหมาดทั้งภาพรวมและรายบุคคล แสดงตัวอย่างหน้าระบบจริง',
 16:'ผู้ปกครองดูข้อมูลบุตรหลานผ่านระบบ ช่วยให้รับรู้ข้อมูลพฤติกรรมร่วมกับโรงเรียน',
 17:'โครงสร้างกรณีทดสอบตามแนวทาง6ด้านรวม15ข้อ เกณฑ์ภาพรวมผ่านอย่างน้อย85% ยังไม่มีผลทดสอบจริง ตามคำยืนยันของผู้วิจัย จึงไม่ระบุจำนวนผ่านหรือร้อยละ',
 18:'ประเมินคุณภาพโดยผู้เชี่ยวชาญและความพึงพอใจโดยผู้ใช้งาน30คน รายงานค่าเฉลี่ย S.D. และระดับจากข้อมูลจริง ตารางในPDF168–171ยังว่างและผู้วิจัยยืนยันว่ายังไม่มีผล จึงแสดงรอผลจริง ไม่สรุประดับ',
 19:'สรุปสิ่งที่พัฒนาได้จากหน้าจอจริง อภิปรายความสอดคล้องเชิงแนวคิดกับงานที่เกี่ยวข้อง โดยยังไม่กล่าวอ้างประสิทธิผลเชิงสถิติ ข้อเสนอเพื่อพัฒนาต่อ: เก็บผลทดสอบและประเมินจริง ปรับระบบจากข้อเสนอแนะ',
 20:'ขอบคุณและรับคำถาม ไม่รวมเวลาตอบคำถามในเวลานำเสนอ'
};
await fs.mkdir(TMP+'/guideline-previews',{recursive:true});
for(let n=1;n<=20;n++){
 S=P.slides.add();S.background.fill=C;
 const old=n<=8?n:n===10?9:n>=11&&n<=16?n-1:n===20?18:null;
 if(n===8)sdlcBuild(S,shape,text,G,C,A,M);
 else if(old){
  for(const item of layout[old-1].items){
   if(item.type==='text'&&(item.s==='STUDENT DISCIPLINE / RESEARCH 2569'||(n===1&&item.s==='โครงงานวิจัย • ปีการศึกษา 2569')))continue;
   if(item.type==='rect')shape('rect',item.x,item.y,item.w,item.h,item.color);
   else if(item.type==='line')shape('line',item.x,item.y,item.w,item.h,'none',item.color,item.weight);
   else if(item.type==='text'){
    let value=item.s,w=item.w;
    if(/\d{2} \/ 18/.test(value))value=String(n).padStart(2,'0')+' / 20';
    if(n===6&&value==='ขอบเขตผู้ใช้งาน'){value='ขอบเขตและกลุ่มตัวอย่าง';w=1168;}
    if(n===6&&value.startsWith('ที่มา:')){value='ที่มา: Pop del.pdf หน้า 18-19, 58';w=1060;}
    if(n===7&&value==='เครื่องมือที่ใช้พัฒนา'){value='แนวคิดและเครื่องมือ';w=1168;}
    if(n===10&&value==='แผนภาพกระแสข้อมูล'){value='การวิเคราะห์และออกแบบระบบ';w=1168;}
    if(n===11&&value==='ดูคะแนน พฤติกรรม และสถิติในหน้าเดียว'){value='ฝ่ายปกครอง • ดูคะแนน พฤติกรรม และสถิติในหน้าเดียว';w=1168;}
    if(n===13&&value==='ยื่นคำร้อง → ตรวจสอบหลักฐาน → ติดตามสถานะ'){value='นักเรียน / ฝ่ายปกครอง • ยื่นคำร้อง → ตรวจสอบหลักฐาน → ติดตามสถานะ';w=1168;}
    if(n===14&&value.startsWith('บันทึกการเข้าเรียน')){value='ครูประจำชั้น • บันทึกการเข้าเรียนและตรวจสอบประวัติย้อนหลัง';w=1168;}
    if(n===15&&value.startsWith('สรุปการเข้าร่วม')){value='ครูประจำชั้น • สรุปการร่วมกิจกรรมและตรวจสอบรายบุคคล';w=1168;}
    text(value,item.x,item.y,w,item.h,item.size,item.color,item.bold,item.align==='center',item.font,'top',item.align);
   }else if(item.type==='image')await image(item.path,item.x,item.y,item.w,item.h,item.cover);
  }
  if(n===1)text('ที่ปรึกษา: อ.แพรวศรี เดิมราช / อ.รุสนี กาแมแล',60,622,750,29,18,M);
  if(n===6){text('กลุ่มตัวอย่าง 30 คน • เลือกแบบเจาะจง',56,591,1168,31,23,G,true);text('นักเรียน 12 • ผู้ปกครอง 12 • ครู 3 • ฝ่ายปกครอง 3',56,625,1168,27,21,M);}
  if(n===7)text('SDLC • ฐานข้อมูล • การแบ่งสิทธิ์ • การประเมินระบบ',56,608,1168,32,23,M);
  if(n===12)text('ครู / ฝ่ายปกครอง',60,527,440,38,24,M,true);
 }else if(n===9){
  base(n,'งานวิจัยที่เกี่ยวข้อง','ที่มา: Pop del.pdf หน้า 51–54','บทที่ 2 • แนวคิดที่นำมาใช้');
  const rows=[
   ['01','มธุริน ปิ่นทอง และคณะ (2566)','ระบบสารสนเทศงานบริการนักเรียน','นำมาใช้: SDLC และฐานข้อมูลสำหรับงานนักเรียน'],
   ['02','วัฒนพล ชุมเพชร และคณะ (2561)','ติดตามการเข้าเรียนผ่านระบบออนไลน์','นำมาใช้: เช็กชื่อและให้ผู้ปกครองร่วมติดตาม'],
   ['03','ภัชราภรณ์ พิมพา (2566)','ติดตามและวิเคราะห์นักเรียนกลุ่มเสี่ยง','นำมาใช้: สรุปพฤติกรรมและประวัติรายบุคคล']];
  rows.forEach((r,i)=>{const y=166+i*159;text(r[0],58,y,94,75,48,A,false,false,'Arial');text(r[1],188,y,1036,42,28,G,true);text(r[2],188,y+47,1036,37,25,G);text(r[3],188,y+90,1036,36,23,M);if(i<2)shape('line',188,y+140,1036,0,'none',L,1);});
 }else if(n===17){
  base(n,'การทดสอบระบบ: Black Box','ที่มา: แนวทางการนำเสนอ หน้า 2 • ผลทดสอบจริง: ยังไม่มี','บทที่ 4 • 6 ด้าน / 15 ข้อ');
  shape('rect',56,164,790,46,G);text('กรณีทดสอบตามเกณฑ์',76,168,576,38,23,C,true);text('จำนวนข้อ',664,168,166,38,22,C,true,true);
  const rows=[['เข้าสู่ระบบ',3],['จัดการข้อมูล',2],['ประมวลผล / เชื่อมโยง',3],['ค้นหา / รายงาน',3],['สิทธิ์ / ความปลอดภัย',2],['ออกจากระบบ',2]];
  rows.forEach((r,i)=>{const y=212+i*55;shape('line',56,y+53,790,0,'none',L,1);text(r[0],76,y,570,49,24);text(String(r[1]),664,y,166,49,25,G,true,true,'Arial');});
  text('รวม 15 ข้อ',56,561,790,42,29,G,true);
  shape('rect',882,164,342,438,G);text('สถานะผลทดสอบ',906,184,294,38,22,C,true);text('รอผลจริง',906,242,294,65,42,C,true,true);text('เกณฑ์ภาพรวม',906,341,294,37,24,C,false,true);text('ผ่าน ≥ 85%',906,386,294,50,33,'#D8C18D',true,true);text('ร้อยละผ่าน =',906,469,294,33,22,C,false,true);text('(ผ่าน ÷ ทั้งหมด) × 100',892,510,322,38,22,C,false,true);
  text('จำนวนข้อข้างต้นเป็นเกณฑ์จัดชุดทดสอบ • ยังไม่ใช่จำนวนข้อที่ผ่าน',56,618,1168,32,22,M);
 }else if(n===18){
  base(n,'การประเมินคุณภาพและความพึงพอใจ','ที่มา: Pop del.pdf หน้า 58, 168–171 • สถานะยืนยันโดยผู้วิจัย','บทที่ 4 • รอผลจริง');
  text('ผู้ใช้งาน 30 คน: นักเรียน 12 • ผู้ปกครอง 12 • ครู 3 • ฝ่ายปกครอง 3',56,150,1168,37,24,M);
  shape('rect',56,204,1168,48,G);
  const xs=[76,562,778,940,1081],ws=[464,202,142,125,123];
  ['รายการประเมิน','ผู้ประเมิน','ค่าเฉลี่ย','S.D.','ระดับ'].forEach((v,i)=>text(v,xs[i],208,ws[i],38,22,C,true,i>=2));
  const rows=[['ความปลอดภัย','ผู้เชี่ยวชาญ'],['ความถูกต้อง','ผู้เชี่ยวชาญ'],['การออกแบบ','ผู้เชี่ยวชาญ'],['ประโยชน์การใช้งาน','ผู้เชี่ยวชาญ'],['ความพึงพอใจภาพรวม','ผู้ใช้งาน']];
  rows.forEach((r,i)=>{const y=255+i*57;shape('line',56,y+54,1168,0,'none',L,1);[r[0],r[1],'—','—','—'].forEach((v,j)=>text(v,xs[j],y,ws[j],51,j<2?24:25,G,j===0,j>=2));});
  text('รอผลจริง',56,563,1168,43,31,A,true);text('เติมค่าเฉลี่ย • ส่วนเบี่ยงเบนมาตรฐาน • ระดับ เมื่อเก็บข้อมูลครบ',56,610,1168,37,24,M);
 }else if(n===19){
  base(n,'สรุป อภิปรายผล และแนวทางพัฒนาต่อ','ที่มา: หน้าระบบจริงในบท 4 และงานที่เกี่ยวข้องในบท 2 • ข้อเสนอโดยผู้วิจัย','บทที่ 5');
  const rows=[['01','สรุปผลการพัฒนา','ระบบรวมข้อมูลวินัย เช็กชื่อ กิจกรรม และอุทธรณ์','ผู้ใช้ 5 บทบาทเข้าถึงข้อมูลตามสิทธิ์'],['02','อภิปรายผล','สอดคล้องกับแนวคิด SDLC และการติดตามแบบมีส่วนร่วม','รอผลประเมินจริงเพื่อสรุปคุณภาพและความพึงพอใจ'],['03','แนวทางพัฒนาต่อ','เก็บผลทดสอบและประเมินให้ครบ','ปรับปรุงระบบจากข้อเสนอแนะของผู้ใช้งาน']];
  rows.forEach((r,i)=>{const y=171+i*159;text(r[0],58,y,94,78,49,A,false,false,'Arial');text(r[1],188,y,1036,41,30,G,true);text(r[2],188,y+48,1036,38,26,G);text(r[3],188,y+93,1036,35,24,M);if(i<2)shape('line',188,y+140,1036,0,'none',L,1);});
 }
 for(const logo of [{path:'logo_university.png',x:56,w:43.4},{path:'logo_department.png',x:116,w:58}])await image(ROOT+'/docs/manual_images/'+logo.path,logo.x,12,logo.w,58,false,logo.path);
 S.speakerNotes.textFrame.setText(`เวลาแนะนำ ${times[n-1]} วินาที\n${notes[n]}`);
 const preview=await P.export({slide:S,format:'png',scale:1});await fs.writeFile(TMP+'/guideline-previews/slide-'+String(n).padStart(2,'0')+'.png',new Uint8Array(await preview.arrayBuffer()));console.log('Rendered '+n);
}
const candidate=TMP+'/guideline-deck-candidate.pptx';await(await PresentationFile.exportPptx(P)).save(candidate);
const finalPath=ROOT+'/output/pdf/student-discipline-guideline-20-slides.pptx';
await finalizePresentation({workspaceDir:ROOT,candidatePath:candidate,finalPath,pythonExecutable:'C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe',integrityValidatorPath:SKILL+'/container_tools/inspect_presentation_package_integrity.py',layoutValidatorPath:SKILL+'/container_tools/inspect_presentation_layout_geometry.py',layoutArgs:['--expected-slide-size-emu','12192000,6858000','--validate-heading-fit'],explicitTotalSlideCount:20,fontPolicy:{basis:'design',families:['Tahoma','Arial']},verifyArtifactToolImport:true,receiptPath:TMP+'/guideline-deck-validation.json'});
console.log(finalPath);console.log('Total seconds: '+times.reduce((a,b)=>a+b,0));
