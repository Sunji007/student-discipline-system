import fs from 'node:fs/promises';
import {pathToFileURL} from 'node:url';
const ROOT='C:/xampp/htdocs/student-discipline-system';
const PKG='C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';
const SKILL='C:/Users/บุสริน/.codex/plugins/cache/openai-primary-runtime/presentations/26.909.11814/skills/presentations';
const {Presentation,PresentationFile}=await import(pathToFileURL(PKG).href);
const {finalizePresentation}=await import(pathToFileURL(SKILL+'/container_tools/artifact_tool_utils.mjs').href);
const P=Presentation.create({slideSize:{width:1280,height:720}});
const S=P.slides.add(); S.background.fill='#F8F6EE';
const G='#143F38',C='#F8F6EE',A='#BD9C59',M='#62766F';
function shape(geometry,x,y,w,h,fill='none',stroke='none',weight=0,rotation=0){return S.shapes.add({geometry,position:{left:x,top:y,width:w,height:h,rotation},fill,line:{fill:stroke,width:weight}});}
function text(s,x,y,w,h,size=24,color=G,bold=false,center=false,font='Tahoma'){
 const sh=shape('textbox',x,y,w,h);sh.text=s;
 sh.text.style={typeface:font,fontSize:size,color,bold,alignment:center?'center':'left',verticalAlignment:'middle',autoFit:'none',wrap:'none',insets:{top:0,bottom:0,left:0,right:0}};
 return sh;
}
shape('rect',0,0,1280,720,C);
text('STUDENT DISCIPLINE / RESEARCH 2569',56,27,880,26,16,A,false,false,'Arial');
text('ขั้นตอนดำเนินงานตาม SDLC',56,76,1168,58,42,G,true);
text('7 ขั้นตอนเพื่อพัฒนาระบบวินัยและติดตามพฤติกรรมนักเรียน',56,136,1168,35,23,M);
shape('line',56,179,1168,0,'none','#D9DED6',1);
const cx=298,cy=405,r=192;
shape('ellipse',cx-r,cy-r,r*2,r*2,'none','#D3C8A8',2);
const short=['ปัญหา','ความเป็นไปได้','วิเคราะห์','ออกแบบ','พัฒนา\nและทดสอบ','ติดตั้ง','บำรุงรักษา'];
for(let i=0;i<7;i++){
 const a=-Math.PI/2+i*2*Math.PI/7;
 const x=cx+r*Math.cos(a),y=cy+r*Math.sin(a);
 shape('ellipse',x-27,y-27,54,54,G);
 text(String(i+1).padStart(2,'0'),x-27,y-22,54,44,31,C,false,true,'Arial');
 const lx=cx+(r-70)*Math.cos(a),ly=cy+(r-70)*Math.sin(a);
 text(short[i],lx-95,ly-22,190,i===4?55:44,18,G,true,true);
 const m=a+Math.PI/7;
 const ax=cx+r*Math.cos(m),ay=cy+r*Math.sin(m);
 shape('triangle',ax-6,ay-7,12,14,A,'none',0,m*180/Math.PI+180);
}
text('SDLC',cx-145,353,290,65,54,G,false,true,'Arial');
text('ระบบวินัยนักเรียน',cx-130,423,260,40,22,M,false,true);
text('พัฒนาและปรับปรุงอย่างต่อเนื่อง',76,618,444,34,20,M,false,true);
shape('line',557,205,0,424,'none','#D9DED6',1);
const steps=[
['ค้นหาปัญหา','วิเคราะห์การบันทึกกระดาษและติดตามพฤติกรรม'],
['ศึกษาความเป็นไปได้','พิจารณาเครื่องมือ บุคลากร งบประมาณ และเวลา'],
['วิเคราะห์ระบบ','สัมภาษณ์ผู้ใช้ • กำหนดงานและสิทธิ์ 5 บทบาท'],
['ออกแบบระบบ','DFD • ER Diagram • ฐานข้อมูล • หน้าจอ'],
['พัฒนาและทดสอบ','PHP/MySQL • ทดสอบคะแนน สิทธิ์ และอุทธรณ์'],
['ติดตั้งระบบ','ทดลองใช้ด้วย XAMPP และแม่ข่ายมหาวิทยาลัย'],
['บำรุงรักษาระบบ','ใช้ผลประเมินและข้อเสนอแนะปรับปรุงระบบ']];
for(let i=0;i<7;i++){
 const y=201+i*62;
 text(String(i+1).padStart(2,'0'),593,y+1,48,37,28,A,false,false,'Arial');
 text(steps[i][0],657,y,567,35,24,G,true);
 text(steps[i][1],657,y+32,567,30,21,M);
}
shape('line',56,661,1168,0,'none','#D9DED6',.8);
text('ที่มา: Pop del.pdf หน้า 24-25, 59-65, 102-104',56,673,1060,30,13,M);
text('08 / 18',1145,673,80,30,16,A,false,true,'Arial');
S.speakerNotes.textFrame.setText('SDLC 7 ขั้นตาม Pop del.pdf หน้า 24-25 เชื่อมกับกิจกรรมวิจัยจากหน้า 59-65 และ 102-104 ไม่ระบุว่าขั้นใดดำเนินการเสร็จแล้ว หากไม่มีข้อมูลสถานะยืนยัน');
const candidate=ROOT+'/tmp/research-slides/sdlc-native-candidate.pptx';
await (await PresentationFile.exportPptx(P)).save(candidate);
const finalPath=ROOT+'/output/pdf/sdlc-page-8-editable-v2.pptx';
await finalizePresentation({workspaceDir:ROOT,candidatePath:candidate,finalPath,pythonExecutable:'C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe',integrityValidatorPath:SKILL+'/container_tools/inspect_presentation_package_integrity.py',layoutValidatorPath:SKILL+'/container_tools/inspect_presentation_layout_geometry.py',layoutArgs:['--expected-slide-size-emu','12192000,6858000','--validate-heading-fit'],explicitTotalSlideCount:1,fontPolicy:{basis:'design',families:['Tahoma','Arial']},verifyArtifactToolImport:true,receiptPath:ROOT+'/tmp/research-slides/sdlc-native-validation-v2.json'});
const preview=await P.export({slide:S,format:'png',scale:1});
await fs.writeFile(ROOT+'/tmp/research-slides/sdlc-native-preview.png',new Uint8Array(await preview.arrayBuffer()));
console.log(finalPath);


