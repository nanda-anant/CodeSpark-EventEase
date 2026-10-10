<?php require '../config/database.php';
need_admin();head('Check-in',true);?>
<h2>Check-in</h2>
<div class="row g-3">
 <div class="col-md-6">
  <div class="card p-3">
   <h5>Manual entry code</h5>
   <input id="code" class="form-control form-control-lg mb-2 text-uppercase" placeholder="EVT7K92P" autofocus>
   <button id="go" class="btn btn-info btn-lg">VERIFY &amp; CHECK IN</button>
   <hr>
   <h5>QR scanner</h5>
   <div id="reader"></div>
  </div>
 </div>
 <div class="col-md-6">
  <div id="out" class="res bad d-none"></div>
 </div>
</div>
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
 const T=<?=json_encode(csrf())?>;
 const out=document.getElementById('out');
 let busy=false;
 async function ci(code){
  if(busy||!code)return;
  busy=true;
  const f=new FormData();
  f.append('code',code);
  f.append('csrf',T);
 try{
  const r=await (await fetch('../api/checkin.php',{method:'POST',body:f})).json();
  const c={
   success:'ok',duplicate:'dup'
  }
 [r.result]||'bad';
 const ic={ok:'✓',dup:'⚠',bad:'✕'}[c];
 out.className='res '+c;
 out.innerHTML=`<h3>${ic} ${r.message}</h3>`+(r.detail?`<p>${r.detail}</p>`:'')+(r.name?`<p>Participant: <b>${r.name}</b></p>`:'')+(r.event?`<p>Event: ${r.event}<br>
 Registration ID: ${r.reg}</p>`:'')+(r.time?`<p>Time: ${r.time}</p>`:'');
 }
 catch(e){
  out.className='res bad';out.textContent='Network error';
 }
  setTimeout(()=>busy=false,1500);
 }
document.getElementById('go').onclick=()=>ci(document.getElementById('code').value);
document.getElementById('code').onkeydown=e=>{if(e.key=='Enter')ci(e.target.value)};
try{new Html5QrcodeScanner('reader',{fps:10,qrbox:200}).render(t=>ci(t),()=>{});}catch(e){}</script><?php foot();
