<?php require 'config/database.php';head('Events');
$q=trim($_GET['q']??'');$c=$_GET['cat']??'';$d=$_GET['date']??'';
$sql="SELECT * FROM events WHERE status<>'Draft' AND event_name LIKE ?";$p=["%$q%"];
if($c){$sql.=" AND category=?";$p[]=$c;}if($d){$sql.=" AND event_date=?";$p[]=$d;}
$st=$pdo->prepare($sql.' ORDER BY event_date');$st->execute($p);$evs=$st->fetchAll();
$cats=$pdo->query('SELECT DISTINCT category FROM events')->fetchAll(PDO::FETCH_COLUMN);?>
<h2 class="mb-3">Events</h2>
<form class="row g-2 mb-4"><div class="col-md-4"><input name="q" value="<?=e($q)?>" class="form-control" placeholder="Search events"></div>
<div class="col-md-3"><select name="cat" class="form-select"><option value="">All categories</option><?php foreach($cats as $x)echo "<option ".($x==$c?'selected':'').">".e($x)."</option>";?></select></div>
<div class="col-md-3"><input type="date" name="date" value="<?=e($d)?>" class="form-control"></div><div class="col-md-2"><button class="btn btn-info w-100">Filter</button></div></form>
<div class="row g-3"><?php if(!$evs)echo "<p class='text-secondary'>No events found.</p>";
foreach($evs as $ev){$n=reg_count($pdo,$ev['id']);$s=reg_state($ev,$n);$bc=$s=='Open'?'success':($s=='Full'?'danger':'secondary');?>
<div class="col-md-6 col-lg-4"><div class="card hov h-100 p-3"><span class="badge bg-info text-dark mb-2 align-self-start"><?=e($ev['category'])?></span>
<h5><?=e($ev['event_name'])?></h5><p class="small text-secondary mb-1"><i class="fa fa-calendar"></i> <?=e($ev['event_date'])?> · <?=substr($ev['start_time'],0,5)?>–<?=substr($ev['end_time'],0,5)?><br><i class="fa fa-location-dot"></i> <?=e($ev['venue'])?><br><i class="fa fa-user"></i> <?=e($ev['organizer'])?></p>
<p class="mb-2">Seats: <b><?=max(0,$ev['capacity']-$n)?></b> / <?=$ev['capacity']?> <span class="badge bg-<?=$bc?>"><?=$s?></span></p>
<?php if($s=='Open')echo "<a class='btn btn-info mt-auto' href='register.php?event_id={$ev['id']}'>Register</a>";else echo "<button class='btn btn-secondary mt-auto' disabled>Unavailable</button>";?></div></div><?php }?></div><?php foot();
