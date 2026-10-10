<?php require '../config/database.php';need_admin();$err=[];$v=$_POST;
if($_SERVER['REQUEST_METHOD']=='POST'){
 if(!csrf_ok())$err[]='Invalid token.';
 foreach(['event_name','category','event_date','start_time','end_time','venue','organizer','registration_start','registration_end','status'] as $f){$v[$f]=trim($v[$f]??'');if($v[$f]==='')$err[]="$f is required.";}
 $cap=filter_var($v['capacity']??'',FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>100000]]);if(!$cap)$err[]='Capacity must be a positive number.';
 if(!$err){if($v['end_time']<=$v['start_time'])$err[]='End time must be after start time.';
  if($v['registration_end']<$v['registration_start'])$err[]='Registration end is before start.';
  if($v['registration_end']>$v['event_date'])$err[]='Registration must close on or before the event date.';
  if(!in_array($v['status'],['Draft','Published','Registration Open','Registration Closed','Completed']))$err[]='Bad status.';}
 if(!$err){$pdo->prepare('INSERT INTO events(event_name,description,category,event_date,start_time,end_time,venue,organizer,capacity,registration_start,registration_end,status) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)')
  ->execute([$v['event_name'],$v['description']??'',$v['category'],$v['event_date'],$v['start_time'],$v['end_time'],$v['venue'],$v['organizer'],$cap,$v['registration_start'],$v['registration_end'],$v['status']]);
  log_act($pdo,'event','Event created: '.$v['event_name']);header('Location: events.php?ok=1');exit;}}
head('Create Event',true);?><h2>Create Event</h2><?php foreach($err as $m)echo "<div class='alert alert-danger py-2'>".e($m)."</div>";?>
<form method="post" class="row g-3 card p-4"><input type="hidden" name="csrf" value="<?=csrf()?>">
<?php $in=function($n,$l,$t='text')use($v){echo "<div class='col-md-6'><label class='form-label'>$l</label><input type='$t' name='$n' value='".e($v[$n]??'')."' class='form-control' required></div>";};
$in('event_name','Event Name');
echo "<div class='col-md-6'><label class='form-label'>Category</label><select name='category' class='form-select'>";foreach(['Workshop','Seminar','Hackathon','Competition','Cultural','Other'] as $c)echo "<option ".(($v['category']??'')==$c?'selected':'').">$c</option>";echo "</select></div>";
$in('event_date','Event Date','date');$in('venue','Venue');$in('start_time','Start Time','time');$in('end_time','End Time','time');$in('organizer','Organizer');$in('capacity','Capacity','number');$in('registration_start','Registration Start','date');$in('registration_end','Registration End','date');?>
<div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['Draft','Published','Registration Open','Registration Closed','Completed'] as $s)echo "<option ".(($v['status']??'Registration Open')==$s?'selected':'').">$s</option>";?></select></div>
<div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control"><?=e($v['description']??'')?></textarea></div>
<div class="col-12"><button class="btn btn-info">Create Event</button></div></form><?php foot();
