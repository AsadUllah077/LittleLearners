import {speak,stopAudio} from './audio';
import {createDrawing,shapePath} from './drawing';
import {post,notify} from './http';
const app=document.getElementById('lessonApp');
if(app){
 const items=JSON.parse(document.getElementById('lessonData').textContent);let index=Math.min(Number(app.dataset.position)||0,items.length-1),lowercase=false;const setDrawing=createDrawing();let saveChain=Promise.resolve();
 const el=id=>document.getElementById(id);
 const render=(save=false)=>{stopAudio();const item=items[index],category=app.dataset.category,value=lowercase?item.lowercase||item.value:item.value;
 el('itemValue').textContent=value;el('itemDescription').textContent=item.description||item.title;el('itemCounter').textContent=`Discovery ${index+1} of ${items.length}`;el('lessonProgress').value=100*(index+1)/items.length;
 el('previousItem').disabled=index===0;el('nextItem').disabled=index===items.length-1;el('caseToggle').hidden=category!=='english-alphabet';el('speakWord').hidden=category!=='english-alphabet';
 const visual=el('itemVisual');visual.replaceChildren();el('quantityHint').textContent='';
 if(item.image){const img=document.createElement('img');img.src=item.image;img.alt=item.title;visual.append(img);}
 else if(category==='colors'){const sample=document.createElement('div');sample.className='color-sample';sample.style.backgroundColor=item.value.toLowerCase();visual.append(sample);}
 else if(category==='shapes'){const c=document.createElement('canvas');c.width=280;c.height=200;c.setAttribute('aria-label',item.title);c.setAttribute('role','img');const ctx=c.getContext('2d');ctx.fillStyle='#8c7af5';if(shapePath(ctx,item.value,280,200))ctx.fill();visual.append(c);}
 else if(category==='numbers'){const n=Number(item.value);visual.classList.add('count-visual');if(n<=20){visual.textContent='● '.repeat(n);}else{visual.textContent='▦ '.repeat(Math.floor(n/10))+'● '.repeat(n%10);el('quantityHint').textContent=`${Math.floor(n/10)} groups of ten${n%10?' and '+n%10+' ones':''}`;}}
 else if(category==='days-of-the-week'||category==='months-of-the-year'){const calendar=document.createElement('div');calendar.className='calendar-tile';calendar.textContent=`📅 ${index+1}`;visual.append(calendar);}
 else visual.textContent=item.example||'✨';
 document.querySelectorAll('[data-index]').forEach(b=>{b.classList.toggle('is-selected',Number(b.dataset.index)===index);b.setAttribute('aria-pressed',String(Number(b.dataset.index)===index));});
 document.querySelectorAll('[data-body-index]').forEach(b=>b.classList.toggle('is-selected',Number(b.dataset.bodyIndex)===index));
 setDrawing(value,item.id);
 if(save){const savedIndex=index;saveChain=saveChain.catch(()=>{}).then(()=>post(app.dataset.bookmark,{index:savedIndex})).catch(error=>notify(error.message));}
 };
 el('previousItem').onclick=()=>{if(index>0){index--;render(true);}};el('nextItem').onclick=()=>{if(index<items.length-1){index++;render(true);}};
 el('caseToggle').onclick=()=>{lowercase=!lowercase;render();};
 document.querySelectorAll('[data-index]').forEach(b=>b.onclick=()=>{index=Number(b.dataset.index);render(true);});
 document.querySelectorAll('[data-body-index]').forEach(b=>b.onclick=()=>{index=Number(b.dataset.bodyIndex);render(true);speak(items[index].title);document.querySelector('.body-person').dataset.part=items[index].title;});
 el('speakItem').onclick=e=>speak(app.dataset.category==='numbers'?items[index].description:items[index].title,items[index].audio,e.currentTarget);
 el('speakWord').onclick=e=>speak(items[index].description?.split(' is for ')[1]||items[index].title,null,e.currentTarget);
 render();
}
