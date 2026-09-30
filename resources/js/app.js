import './bootstrap';
import {createIcons,House,Gamepad2,Star,Users,Settings,Sparkles,LogIn,ArrowRight,Volume2} from 'lucide';
import './audio';
import './learning';
import './games';
import './admin';
createIcons({icons:{House,Gamepad2,Star,Users,Settings,Sparkles,LogIn,ArrowRight,Volume2}});
document.querySelectorAll('[data-confirm]').forEach(form=>form.addEventListener('submit',event=>{if(!confirm(form.dataset.confirm))event.preventDefault();}));
