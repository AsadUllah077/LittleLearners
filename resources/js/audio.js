let currentAudio=null;
let sequence=0;
function status(text){ const target=document.getElementById('audioStatus'); if(target)target.textContent=text; }
export function stopAudio(){ sequence++; if(currentAudio){currentAudio.pause();currentAudio=null;} window.speechSynthesis?.cancel();status('');document.querySelectorAll('.speaking').forEach(el=>el.classList.remove('speaking')); }
export function speak(text,url=null,button=null){
    stopAudio(); const id=sequence; button?.classList.add('speaking'); status('Listening…');
    const done=()=>{if(id!==sequence)return;status('');button?.classList.remove('speaking');};
    const fallback=()=>{if(id!==sequence)return;if(!('speechSynthesis' in window)){status('Audio is unavailable in this browser. You can still learn with the pictures.');button?.classList.remove('speaking');return;} const utterance=new SpeechSynthesisUtterance(text);utterance.lang=document.querySelector('meta[name="speech-language"]')?.content||'en-US';utterance.rate=.78;utterance.onend=done;utterance.onerror=()=>{done();status('Tap Listen to try the audio again.');};window.speechSynthesis.speak(utterance);};
    if(url){currentAudio=new Audio(url);currentAudio.onended=done;currentAudio.onerror=fallback;currentAudio.play().catch(fallback);}else fallback();
}
window.addEventListener('pagehide',stopAudio);
document.addEventListener('visibilitychange',()=>{if(document.hidden)stopAudio();});
document.querySelectorAll('[data-stop-audio]').forEach(button=>button.addEventListener('click',stopAudio));
