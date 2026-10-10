<?php require '../config/database.php';
need_admin();
head('Participants',true);
$q=trim($_GET['q']??'');
$ev=(int)($_GET['event']??0);
$st=$_GET['st']??'';
$dp=trim($_GET['dept']??'');
$sql="SELECT p.*,r.registration_id,r.entry_code,e.event_name,c.checkin_time FROM registrations r JOIN participants p 
ON p.id=r.participant_id JOIN events e ON e.id=r.event_id LEFT JOIN checkins c ON c.registration_id=r.id 
WHERE (p.full_name LIKE :q1 OR p.email LIKE :q2 OR r.registration_id LIKE :q3 OR r.entry_code LIKE :q4) AND p.department LIKE :d";
$p=[':q1'=>"%$q%",':q2'=>"%$q%",':q3'=>"%$q%",':q4'=>"%$q%",':d'=>"%$dp%"];
if($ev){
  $sql.=' AND r.event_id=:e';
  $p[':e']=$ev;
}
if($st=='in')$sql.=' AND c.id IS NOT NULL';
if($st=='out')$sql.=' AND c.id IS NULL';
$s=$pdo->prepare($sql.' ORDER BY r.id DESC');
$s->execute($p);
$evs=$pdo->query('SELECT id,event_name FROM events')->fetchAll();?>
<h2>Participants</h2>
<form class="row g-2 mb-3">
  <div class="col-md-3">
    <input name="q" value="<?=e($q)?>" class="form-control" placeholder="Name, email, reg ID, code">
  </div>
<div class="col-md-3">
  <select name="event" class="form-select">
    <option value="0">All events</option>
<?php foreach($evs as $x)echo "<option value='{$x['id']}' ".($x['id']==$ev?'selected':'').">".e($x['event_name'])."</option>";?>
</select>
</div>
<div class="col-md-2">
  <select name="st" class="form-select">
    <option value="">All</option>
    <option value="in" <?=$st=='in'?'selected':''?>>Checked In</option>
    <option value="out" <?=$st=='out'?'selected':''?>>Not Checked In</option>
  </select>
</div>
<div class="col-md-2">
  <input name="dept" value="<?=e($dp)?>" class="form-control" placeholder="Department">
</div>
<div class="col-md-2">
  <button class="btn btn-info w-100">Filter</button>
</div>
</form>
<div class="table-responsive">
  <table class="table">
    <tr>
      <th>Name</th>
      <th>Email</th>
      <th>Mobile</th>
      <th>College</th>
      <th>Dept</th>
      <th>Event</th>
      <th>Reg ID</th>
      <th>Code</th>
      <th>Status</th>
    </tr>
<?php $n=0;
foreach($s as $r)
{
  $n++;
  echo "<tr><td>".e($r['full_name'])."</td><td>".e($r['email'])."</td>
  <td>".e($r['mobile'])."</td><td>".e($r['college'])."</td><td>".e($r['department'])."</td>
  <td>".e($r['event_name'])."</td><td>".e($r['registration_id'])."</td><td>".e($r['entry_code'])."</td>
  <td>".($r['checkin_time']?"<span class='badge bg-success'>In ".e($r['checkin_time'])."</span>":"<span class='badge bg-secondary'>Not yet</span>")."</td></tr>";}
if(!$n)echo "<tr><td colspan=9 class='text-secondary text-center'>No participants found.</td></tr>";?></table></div><?php foot();
