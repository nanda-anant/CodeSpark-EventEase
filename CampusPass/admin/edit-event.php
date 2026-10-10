<?php require_once __DIR__.'/../includes/admin-auth.php';
require_admin(['super_admin','event_admin']);
$id=(int)($_GET['id']??$_POST['id']??0);
$s=$pdo->prepare('SELECT * FROM events WHERE id=?');$s->execute([$id]);$ev=$s->fetch();
if(!$ev){flash('danger','Event not found.');redirect('events.php');}
$countS=$pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id=? AND status='valid'");$countS->execute([$id]);$count=(int)$countS->fetchColumn();
$pageTitle='Edit event';
$err=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  [$err,$d]=validate_event($_POST,$ev,max(1,$count));
  if(!$err){
    $s=$pdo->prepare('UPDATE events SET title=?,category=?,description=?,venue=?,event_date=?,start_time=?,end_time=?,capacity=?,organiser=?,rules=?,image_url=?,status=?,registration_start=?,registration_end=? WHERE id=?');
    $s->execute([$d['title'],$d['category'],$d['description'],$d['venue'],$d['event_date'],$d['start_time'],$d['end_time']!==''?$d['end_time']:null,(int)$d['capacity'],$d['organiser'],$d['rules'],$d['image_url']!==''?$d['image_url']:null,$d['status'],$d['registration_start_db'],$d['registration_end_db'],$id]);
    log_action($pdo,'event.update','Updated event ID '.$id);
    flash('success','Event updated.');
    redirect('events.php');
  }
}
$val=fn($k,$def='')=>e($_POST[$k]??$def);
$rsDefault=$ev['registration_start']?date('Y-m-d\TH:i',strtotime($ev['registration_start'])):'';
$reDefault=$ev['registration_end']?date('Y-m-d\TH:i',strtotime($ev['registration_end'])):'';
$selStatus=$_POST['status']??$ev['status'];
$minCap=max(1,$count);
require __DIR__.'/../includes/admin-layout.php'; ?>
<div class="panel"><p class="muted">Fields marked <span class="text-danger">*</span> are required.</p>
<?=render_errors($err)?>
<form method="post" data-validate novalidate><?=csrf_field()?><input type="hidden" name="id" value="<?=$id?>">
<div class="row g-3">
<div class="col-md-8"><label class="form-label required" for="title">Title</label><input class="form-control" id="title" name="title" required minlength="3" maxlength="200" value="<?=$val('title',$ev['title'])?>"></div>
<div class="col-md-4"><label class="form-label required" for="category">Category</label><input class="form-control" id="category" name="category" list="category-list" required minlength="2" maxlength="80" data-rule="category" value="<?=$val('category',$ev['category'])?>"><datalist id="category-list"><option value="Technology"><option value="Cultural"><option value="Sports"><option value="Workshop"><option value="Seminar"><option value="Hackathon"><option value="Music"></datalist></div>
<div class="col-12"><label class="form-label required" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4" required minlength="10" maxlength="5000"><?=$val('description',$ev['description'])?></textarea></div>
<div class="col-md-6"><label class="form-label required" for="venue">Venue</label><input class="form-control" id="venue" name="venue" required minlength="3" maxlength="200" value="<?=$val('venue',$ev['venue'])?>"></div>
<div class="col-md-6"><label class="form-label" for="organiser">Organiser</label><input class="form-control" id="organiser" name="organiser" maxlength="160" value="<?=$val('organiser',$ev['organiser'])?>"></div>
<div class="col-md-4"><label class="form-label required" for="event_date">Date</label><input class="form-control" id="event_date" type="date" name="event_date" required data-not-past data-original="<?=e($ev['event_date'])?>" value="<?=$val('event_date',$ev['event_date'])?>"></div>
<div class="col-md-4"><label class="form-label required" for="start_time">Start time</label><input class="form-control" id="start_time" type="time" name="start_time" required value="<?=$val('start_time',substr((string)$ev['start_time'],0,5))?>"></div>
<div class="col-md-4"><label class="form-label" for="end_time">End time</label><input class="form-control" id="end_time" type="time" name="end_time" data-after="#start_time" data-msg-after="End time must be after the start time." value="<?=$val('end_time',substr((string)$ev['end_time'],0,5))?>"></div>
<div class="col-md-4"><label class="form-label required" for="capacity">Capacity <span class="text-muted fw-normal">(<?=$count?> valid registrations)</span></label><input class="form-control" id="capacity" type="number" min="<?=$minCap?>" max="100000" step="1" name="capacity" required data-label="Capacity" data-msg-min="Capacity cannot be below the <?=$minCap?> existing valid registrations." data-msg-max="Capacity cannot be more than 100000." value="<?=$val('capacity',$ev['capacity'])?>"></div>
<div class="col-md-4"><label class="form-label" for="registration_start">Registration starts</label><input class="form-control" id="registration_start" type="datetime-local" name="registration_start" value="<?=$val('registration_start',$rsDefault)?>"></div>
<div class="col-md-4"><label class="form-label" for="registration_end">Registration ends</label><input class="form-control" id="registration_end" type="datetime-local" name="registration_end" data-after="#registration_start" data-not-after-date="#event_date" data-msg-after="Registration end must be after registration start." value="<?=$val('registration_end',$reDefault)?>"></div>
<div class="col-md-8"><label class="form-label" for="image_url">Image URL</label><input class="form-control" id="image_url" name="image_url" type="url" maxlength="1000" placeholder="https://..." value="<?=$val('image_url',$ev['image_url'])?>"></div>
<div class="col-md-4"><label class="form-label" for="status">Status</label><select class="form-select" id="status" name="status"><?php foreach(['draft','published','closed','cancelled'] as $v): ?><option value="<?=$v?>" <?=$selStatus===$v?'selected':''?>><?=ucfirst($v)?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label" for="rules">Rules</label><textarea class="form-control" id="rules" name="rules" rows="3" maxlength="5000"><?=$val('rules',$ev['rules'])?></textarea></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Save changes</button><a class="btn btn-outline-secondary" href="events.php">Cancel</a></div>
</form></div>
<?php require __DIR__.'/../includes/admin-footer.php'; ?>
