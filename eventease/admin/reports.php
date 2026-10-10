<?php require '../config/database.php';
need_admin();
$rows=$pdo->query('SELECT e.event_name,e.capacity,(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) 
reg,(SELECT COUNT(*) FROM checkins c WHERE c.event_id=e.id) ci FROM events e')->fetchAll();
if(isset($_GET['csv']))
{
 header('Content-Type: text/csv');
 header('Content-Disposition: attachment; filename=eventease-report.csv');
 $o=fopen('php://output','w');
 fputcsv($o,['Event','Capacity','Registrations','Check-ins','Check-in %','Utilization %','No-shows']);
 foreach($rows as $r)fputcsv($o,[$r['event_name'],$r['capacity'],$r['reg'],$r['ci'],
 $r['reg']?round($r['ci']*100/$r['reg']):0,round($r['reg']*100/$r['capacity']),$r['reg']-$r['ci']]);
 exit;
}
head('Reports',true);?>
<div class="d-flex justify-content-between mb-3">
 <h2>Reports</h2><div class="noprint">
  <a class="btn btn-info" href="?csv=1">Export CSV</a> 
  <button class="btn btn-outline-light" onclick="print()">Print / PDF</button>
 </div>
</div>
<table class="table">
 <tr>
  <th>Event</th>
  <th>Capacity</th>
  <th>Registered</th>
  <th>Checked in</th>
  <th>Check-in %</th>
  <th>Utilization %</th>
  <th>No-shows</th></tr>
<?php foreach($rows as $r)echo "<tr><td>".e($r['event_name'])."</td>
<td>{$r['capacity']}</td><td>{$r['reg']}</td><td>{$r['ci']}</td>
<td>".($r['reg']?round($r['ci']*100/$r['reg']):0)."%</td>
<td>".round($r['reg']*100/$r['capacity'])."%</td>
<td>".($r['reg']-$r['ci'])."</td></tr>";?></table>
<p class="text-secondary">No-show participants: Participants → filter “Not Checked In”.</p>
<?php foot();
