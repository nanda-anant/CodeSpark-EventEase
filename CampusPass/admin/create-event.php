<?php require_once __DIR__.'/../includes/admin-auth.php';
require_admin(['super_admin','event_admin']);
$pageTitle='Create event';
$err=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  [$err,$d]=validate_event($_POST,null,1);
  if(!$err){
    $s=$pdo->prepare('INSERT INTO events(title,category,description,venue,event_date,start_time,end_time,capacity,organiser,rules,image_url,status,registration_start,registration_end,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    $s->execute([$d['title'],$d['category'],$d['description'],$d['venue'],$d['event_date'],$d['start_time'],$d['end_time']!==''?$d['end_time']:null,(int)$d['capacity'],$d['organiser'],$d['rules'],$d['image_url']!==''?$d['image_url']:null,$d['status'],$d['registration_start_db'],$d['registration_end_db'],$_SESSION['admin_id']]);
    log_action($pdo,'event.create','Created event: '.$d['title']);
    flash('success','Event created.');
    redirect('events.php');
  }
}
$val=fn($k,$def='')=>e($_POST[$k]??$def);
$selStatus=$_POST['status']??'draft';
require __DIR__.'/../includes/admin-layout.php'; ?>
<div class="panel"><p class="muted">Fields marked <span class="text-danger">*</span> are required. Image URL is optional; use a trusted HTTPS image URL.</p>
<?=render_errors($err)?>
<form method="post" data-validate novalidate><?=csrf_field()?>
<div class="row g-3">
<div class="col-md-8"><label class="form-label required" for="title">Event title</label><input class="form-control" id="title" name="title" required minlength="3" maxlength="200" value="<?=$val('title')?>"></div>
<div class="col-md-4"><label class="form-label required" for="category">Category</label><input class="form-control" id="category" name="category" list="category-list" required minlength="2" maxlength="80" data-rule="category" value="<?=$val('category','Technology')?>"><datalist id="category-list"><option value="Technology"><option value="Cultural"><option value="Sports"><option value="Workshop"><option value="Seminar"><option value="Hackathon"><option value="Music"></datalist></div>
<div class="col-12"><label class="form-label required" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4" required minlength="10" maxlength="5000"><?=$val('description')?></textarea></div>
<div class="col-md-6"><label class="form-label required" for="venue">Venue</label><input class="form-control" id="venue" name="venue" required minlength="3" maxlength="200" value="<?=$val('venue')?>"></div>
<div class="col-md-6"><label class="form-label" for="organiser">Organiser</label><input class="form-control" id="organiser" name="organiser" maxlength="160" value="<?=$val('organiser')?>"></div>
<div class="col-md-4"><label class="form-label required" for="event_date">Event date</label><input class="form-control" id="event_date" type="date" name="event_date" required data-not-past value="<?=$val('event_date')?>"></div>
<div class="col-md-4"><label class="form-label required" for="start_time">Start time</label><input class="form-control" id="start_time" type="time" name="start_time" required value="<?=$val('start_time','09:00')?>"></div>
<div class="col-md-4"><label class="form-label" for="end_time">End time</label><input class="form-control" id="end_time" type="time" name="end_time" data-after="#start_time" data-msg-after="End time must be after the start time." value="<?=$val('end_time')?>"></div>
<div class="col-md-4"><label class="form-label required" for="capacity">Capacity</label><input class="form-control" id="capacity" type="number" min="1" max="100000" step="1" name="capacity" required data-msg-min="Capacity must be at least 1." data-msg-max="Capacity cannot be more than 100000." value="<?=$val('capacity','100')?>"></div>
<div class="col-md-4"><label class="form-label" for="registration_start">Registration starts</label><input class="form-control" id="registration_start" type="datetime-local" name="registration_start" value="<?=$val('registration_start')?>"></div>
<div class="col-md-4"><label class="form-label" for="registration_end">Registration ends</label><input class="form-control" id="registration_end" type="datetime-local" name="registration_end" data-after="#registration_start" data-not-after-date="#event_date" data-msg-after="Registration end must be after registration start." value="<?=$val('registration_end')?>"></div>
<div class="col-md-8"><label class="form-label" for="image_url">Image URL</label><input class="form-control" id="image_url" type="url" name="image_url" maxlength="1000" placeholder="https://..." value="<?=$val('image_url')?>"></div>
<div class="col-md-4"><label class="form-label" for="status">Initial status</label><select class="form-select" id="status" name="status"><?php foreach(['draft'=>'Draft','published'=>'Published','closed'=>'Closed'] as $k=>$lbl): ?><option value="<?=$k?>" <?=$selStatus===$k?'selected':''?>><?=$lbl?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label" for="rules">Rules &amp; instructions</label><textarea class="form-control" id="rules" name="rules" rows="3" maxlength="5000"><?=$val('rules')?></textarea></div>
</div>
<div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Create event</button><a class="btn btn-outline-secondary" href="events.php">Cancel</a></div>
</form></div>
<?php require __DIR__.'/../includes/admin-footer.php'; ?>
