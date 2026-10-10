<?php require 'config/database.php';
$s=$pdo->prepare('SELECT r.*,p.full_name,e.event_name,e.event_date,e.start_time,e.venue FROM registrations r JOIN participants p ON p.id=r.participant_id JOIN events e ON e.id=r.event_id WHERE r.registration_id=?');
$s->execute([$_GET['r']??'']);$r=$s->fetch();head('Entry Pass');
if(!$r){echo "<div class='alert alert-danger'>Pass not found.</div>";foot();exit;}?>
<div class="pass shadow"><div class="top"><h4 class="mb-0">EVENTEASE</h4><small>Entry Pass</small></div><div class="p-4 text-center">
<h5><?=e($r['event_name'])?></h5><h4><?=e($r['full_name'])?></h4>
<p class="small mb-2">Reg ID: <b><?=e($r['registration_id'])?></b><br>Entry Code: <b class="fs-5"><?=e($r['entry_code'])?></b><br><?=e($r['event_date'])?> · <?=substr($r['start_time'],0,5)?><br><?=e($r['venue'])?></p>
<div id="qr" class="d-flex justify-content-center my-3"></div></div></div>
<div class="text-center mt-3 noprint"><button class="btn btn-info" onclick="dl()">Download QR</button> <button class="btn btn-outline-light" onclick="print()">Print Pass</button> <a class="btn btn-outline-light" href="events.php">Back to Events</a></div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>new QRCode(document.getElementById('qr'),{text:<?=json_encode($r['entry_code'])?>,width:200,height:200});
function dl(){const i=document.querySelector('#qr img')||document.querySelector('#qr canvas');const a=document.createElement('a');a.href=i.src||i.toDataURL();a.download='<?=e($r['registration_id'])?>.png';a.click();}</script><?php foot();
