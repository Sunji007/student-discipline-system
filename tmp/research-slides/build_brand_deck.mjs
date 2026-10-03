import fs from 'node:fs/promises';
import {pathToFileURL} from 'node:url';
const ROOT='C:/xampp/htdocs/student-discipline-system';
const TMP=ROOT+'/tmp/research-slides';
const SKILL='C:/Users/บุสริน/.codex/plugins/cache/openai-primary-runtime/presentations/26.909.11814/skills/presentations';
const PKG='C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/@oai/artifact-tool/dist/artifact_tool.mjs';
const {Presentation,PresentationFile}=await import(pathToFileURL(PKG).href);
const {finalizePresentation}=await import(pathToFileURL(SKILL+'/container_tools/artifact_tool_utils.mjs').href);
const layout=JSON.parse(await fs.readFile(TMP+'/native-layout.json','utf8'));
const P=Presentation.create({slideSize:{width:1280,height:720}});
const G='#143F38',C='#F8F6EE',A='#BD9C59',M='#62766F';
let S;
function shape(geometry,x,y,w,h,fill='none',stroke='none',weight=0,rotation=0){return S.shapes.add({geometry,position:{left:x,top:y,width:w,height:h,rotation},fill,line:{fill:stroke,width:weight}});}
function text(s,x,y,w,h,size=24,color=G,bold=false,center=false,font='Tahoma',vertical='middle',align=null){
 const sh=shape('textbox',x,y,w,h);sh.text=s;
 sh.text.style={typeface:font,fontSize:size,color,bold,alignment:align||(center?'center':'left'),verticalAlignment:vertical,autoFit:'none',wrap:'none',insets:{top:0,bottom:0,left:0,right:0}};
 return sh;
}
const sdlcSource=await fs.readFile(TMP+'/build_sdlc_native.mjs','utf8');
const sdlcBody=sdlcSource.slice(sdlcSource.indexOf("shape('rect',0,0"),sdlcSource.indexOf('const candidate=')).split('\n').filter(line=>!line.includes("text('STUDENT DISCIPLINE")).join('\n');
const sdlcBuild=new Function('S','shape','text','G','C','A','M',sdlcBody);
await fs.mkdir(TMP+'/brand-previews',{recursive:true});
for(const page of layout){
 S=P.slides.add();S.background.fill=C;
 if(page.number===8){sdlcBuild(S,shape,text,G,C,A,M);}
 else for(const item of page.items){
  if(item.type==='text' && (item.s==='STUDENT DISCIPLINE / RESEARCH 2569' || (page.number===1 && item.s==='โครงงานวิจัย • ปีการศึกษา 2569'))) continue;
  if(item.type==='rect')shape('rect',item.x,item.y,item.w,item.h,item.color);
  else if(item.type==='line')shape('line',item.x,item.y,item.w,item.h,'none',item.color,item.weight);
  else if(item.type==='text'){
   text(item.s,item.x,item.y,item.w,item.h,item.size,item.color,item.bold,item.align==='center',item.font,'top',item.align);
  } else if(item.type==='image'){
   S.images.add({blob:new Uint8Array(await fs.readFile(item.path)),contentType:/\.jpe?g$/i.test(item.path)?'image/jpeg':'image/png',alt:item.path.split('/').pop(),fit:item.cover?'cover':'contain',position:{left:item.x,top:item.y,width:item.w,height:item.h}});
  }
 }
 if(page.number!==8) S.speakerNotes.textFrame.setText(page.source||'Pop del.pdf และภาพประกอบโครงงานระบบวินัยนักเรียน');
 for(const logo of [{path:'logo_university.png',x:56,w:43.4},{path:'logo_department.png',x:116,w:58}]){
  S.images.add({blob:new Uint8Array(await fs.readFile(ROOT+'/docs/manual_images/'+logo.path)),contentType:'image/png',alt:logo.path==='logo_university.png'?'ตรามหาวิทยาลัยราชภัฏยะลา':'โลโก้สาขาวิชาเทคโนโลยีสารสนเทศ',fit:'contain',position:{left:logo.x,top:12,width:logo.w,height:58}});
 }
 const preview=await P.export({slide:S,format:'png',scale:1});
 await fs.writeFile(TMP+'/brand-previews/slide-'+String(page.number).padStart(2,'0')+'.png',new Uint8Array(await preview.arrayBuffer()));
 console.log('Rendered '+page.number);
}
const candidate=TMP+'/brand-deck-candidate.pptx';
await (await PresentationFile.exportPptx(P)).save(candidate);
const finalPath=ROOT+'/output/pdf/student-discipline-with-logos.pptx';
await finalizePresentation({workspaceDir:ROOT,candidatePath:candidate,finalPath,pythonExecutable:'C:/Users/บุสริน/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe',integrityValidatorPath:SKILL+'/container_tools/inspect_presentation_package_integrity.py',layoutValidatorPath:SKILL+'/container_tools/inspect_presentation_layout_geometry.py',layoutArgs:['--expected-slide-size-emu','12192000,6858000','--validate-heading-fit'],explicitTotalSlideCount:18,fontPolicy:{basis:'design',families:['Tahoma','Arial']},verifyArtifactToolImport:true,receiptPath:TMP+'/brand-deck-validation.json'});
console.log(finalPath);
