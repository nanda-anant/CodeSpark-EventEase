document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Are you sure?'))e.preventDefault()}));
document.querySelectorAll('[data-toggle-password]').forEach(btn=>btn.addEventListener('click',()=>{const input=document.getElementById(btn.dataset.togglePassword);if(input)input.type=input.type==='password'?'text':'password'}));

// Highlight the current page in the admin sidebar and the public navbar.
(function(){var cur=location.pathname.split('/').pop()||'index.php';document.querySelectorAll('.admin-sidebar>a[href], .sidebar-bottom a[href], .navbar .nav-link[href]').forEach(function(a){if(a.getAttribute('href')===cur)a.classList.add('active');});})();
