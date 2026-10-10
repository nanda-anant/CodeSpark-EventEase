<?php require '../config/database.php';need_admin();head('Dashboard',true);
$q=fn($s)=>(int)$pdo->query($s)->fetchColumn();
$ev=$q('SELECT COUNT(*) FROM events');$act=$q("SELECT COUNT(*) FROM events WHERE status IN('Published','Registration Open')");
$reg=$q('SELECT COUNT(*) FROM registrations');$ci=$q('SELECT COUNT(*) FROM checkins');$cap=$q("SELECT COALESCE(SUM(capacity),0) FROM events WHERE status<>'Draft'");
$pct=$reg?round($ci*100/$reg):0;
$cards=['Total Events'=>$ev,'Active Events'=>$act,'Registrations'=>$reg,'Check-ins'=>$ci,'Available Seats'=>max(0,$cap-$reg),'Attendance'=>$pct.'%'];
$rows=$pdo->query('SELECT e.event_name,e.capacity,(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) reg,(SELECT COUNT(*) FROM checkins c WHERE c.event_id=e.id) ci FROM events e')->fetchAll();
$logs=$pdo->query('SELECT * FROM activity_log ORDER BY id DESC LIMIT 8')->fetchAll();?>
<h2 class="mb-3">Dashboard</h2><div class="row g-3 mb-4"><?php foreach($cards as $k=>$v)echo "<div class='col-6 col-md-4 col-lg-2'><div class='card p-3 text-center'><div class='stat'>$v</div><small>$k</small></div></div>";?></div>
<div class="row g-3"><div class="col-lg-8"><div class="card p-3"><canvas id="ch" height="110"></canvas></div></div>
<div class="col-lg-4"><div class="card p-3"><h5>Recent Activity</h5><?php if(!$logs)echo '<p class="text-secondary">No activity yet.</p>';foreach($logs as $l){$i=['checkin'=>'text-success','duplicate'=>'text-warning','invalid'=>'text-danger'][$l['type']]??'text-info';echo "<div class='small mb-2'><i class='fa fa-circle $i'></i> ".e($l['message'])."<br><span class='text-secondary'>".e($l['created_at'])."</span></div>";}?></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>const d=<?=json_encode($rows)?>;new Chart(document.getElementById('ch'),{type:'bar',data:{labels:d.map(x=>x.event_name),datasets:[{label:'Capacity',data:d.map(x=>x.capacity),backgroundColor:'#334155'},{label:'Registrations',data:d.map(x=>x.reg),backgroundColor:'#38bdf8'},{label:'Check-ins',data:d.map(x=>x.ci),backgroundColor:'#34d399'}]},options:{plugins:{legend:{labels:{color:'#e6ecff'}}},scales:{x:{ticks:{color:'#e6ecff'}},y:{ticks:{color:'#e6ecff'},beginAtZero:true}}}});</script><?php foot();
