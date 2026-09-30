export function notify(message) { const toast=document.getElementById('toast'); if(toast){toast.textContent=message;toast.hidden=false;clearTimeout(window.toastTimer);window.toastTimer=setTimeout(()=>toast.hidden=true,4500);} }
export async function post(url,body) {
    const response=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(body)});
    const data=await response.json().catch(()=>({message:'Please refresh the page and try again.'}));
    if(!response.ok) throw new Error(data.message || 'We could not save that. Please try again.'); return data;
}
