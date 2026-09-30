import {post,notify} from './http';
const shapeVertices={Triangle:3,Square:4,Diamond:4,Pentagon:5,Hexagon:6,Star:10};
export function shapePath(ctx,name,w,h){
 const cx=w/2,cy=h/2,r=Math.min(w,h)*.32;ctx.beginPath();
 if(name==='Circle'||name==='Oval')ctx.ellipse(cx,cy,r,name==='Oval'?r*.65:r,0,0,Math.PI*2);
 else if(name==='Rectangle')ctx.rect(cx-r*1.3,cy-r*.7,r*2.6,r*1.4);
 else if(name==='Heart'){ctx.moveTo(cx,cy+r);ctx.bezierCurveTo(cx-r*2,cy-r*.1,cx-r,cy-r*1.7,cx,cy-r*.6);ctx.bezierCurveTo(cx+r,cy-r*1.7,cx+r*2,cy-r*.1,cx,cy+r);}
 else if(shapeVertices[name]){const n=shapeVertices[name];for(let i=0;i<n;i++){const angle=i*2*Math.PI/n-Math.PI/2+(name==='Square'?Math.PI/4:0);const rad=name==='Star'&&i%2?r*.45:r;const x=cx+rad*Math.cos(angle),y=cy+rad*Math.sin(angle);i?ctx.lineTo(x,y):ctx.moveTo(x,y);}ctx.closePath();}
 else return false; return true;
}
export function createDrawing(){
 const canvas=document.getElementById('drawingCanvas'),guide=document.getElementById('traceGuide');if(!canvas||!guide)return ()=>{};
 const ctx=canvas.getContext('2d'),g=guide.getContext('2d');let strokes=[],active=null,target='',itemId=null,show=true,example=false;
 const color=document.getElementById('colorPicker'),brush=document.getElementById('brushSize');
 const redraw=()=>{const w=canvas.clientWidth,h=canvas.clientHeight;ctx.clearRect(0,0,w,h);for(const stroke of strokes){ctx.beginPath();ctx.strokeStyle=stroke.color;ctx.lineWidth=stroke.width;ctx.lineCap='round';ctx.lineJoin='round';stroke.points.forEach((p,i)=>i?ctx.lineTo(p[0]*w,p[1]*h):ctx.moveTo(p[0]*w,p[1]*h));ctx.stroke();}g.clearRect(0,0,w,h);if(!show)return;g.save();g.strokeStyle=example?'#6c63ff':'#b6afcc';g.lineWidth=example?8:3;g.setLineDash(example?[]:[3,9]);g.lineCap='round';if(shapePath(g,target,w,h))g.stroke();else{g.font=`700 ${Math.min(h*.66,w/(Math.max(1,target.length)*.7),220)}px Arial`;g.textAlign='center';g.textBaseline='middle';g.strokeText(target,w/2,h/2);}g.restore();};
 const resize=()=>{for(const surface of [canvas,guide]){const ratio=window.devicePixelRatio||1;surface.width=surface.clientWidth*ratio;surface.height=surface.clientHeight*ratio;surface.getContext('2d').setTransform(ratio,0,0,ratio,0,0);}redraw();};
 new ResizeObserver(resize).observe(canvas);
 const point=e=>{const r=canvas.getBoundingClientRect();return [Math.max(0,Math.min(1,(e.clientX-r.left)/r.width)),Math.max(0,Math.min(1,(e.clientY-r.top)/r.height))];};
 canvas.addEventListener('pointerdown',e=>{if(active)return;e.preventDefault();canvas.setPointerCapture(e.pointerId);active={id:e.pointerId,color:color.value,width:Number(brush.value),points:[point(e)]};strokes.push(active);example=false;redraw();});
 canvas.addEventListener('pointermove',e=>{if(!active||active.id!==e.pointerId)return;e.preventDefault();if(active.points.length<1500)active.points.push(point(e));redraw();});
 for(const event of ['pointerup','pointercancel','lostpointercapture'])canvas.addEventListener(event,()=>active=null);
 document.getElementById('clearCanvas').onclick=()=>{strokes=[];example=false;redraw();};
 document.getElementById('undoStroke').onclick=()=>{strokes.pop();redraw();};
 document.getElementById('showExample').onclick=()=>{example=!example;show=true;redraw();};
 document.getElementById('toggleGuide').onclick=e=>{show=!show;e.currentTarget.textContent=show?'Hide guide':'Show guide';redraw();};
 document.getElementById('finishTracing')?.addEventListener('click',async e=>{const button=e.currentTarget;const points=strokes.flatMap(s=>s.points).slice(0,5000);if(points.length<8){notify('Draw along the guide first. You can do it!');return;}button.disabled=true;try{const result=await post(document.getElementById('tracing').dataset.complete,{item_id:itemId,points});document.getElementById('tracingStatus').textContent='⭐ '+result.message;notify(result.message);}catch(error){notify(error.message);}finally{button.disabled=false;}});
 return (value,id)=>{target=value;itemId=id;strokes=[];example=false;document.getElementById('practiceTarget').textContent=value;redraw();};
}
