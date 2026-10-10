<?php require '../config/database.php';need_admin();
if($_SERVER['REQUEST_METHOD']=='POST'&&csrf_ok()){$id=(int)$_POST['id'];$a=$_POST['act'];
 if($a=='delete')$pdo->prepare('DELETE FROM events WHERE id=?')->execute([$id]);
 elseif(in_array($a,['Published','Registration Open','Registration Closed','Completed']))$pdo->prepare('UPDATE events SET status=? WHERE id=?')->execute([$a,$id]);
 header('Location: events.php');exit;}
head('Manage Events',true);$q=trim($_GET['q']??'');$s=$pdo->prepare('SELECT * FROM events WHERE event_name LIKE ? ORDER BY event_date DESC');$s->execute(["%$q%"]);?>
<div class="d-flex justify-content-between mb-3"><h2>Events</h2><a class="btn btn-info" href="create-event.php">+ New Event</a></div>
<?php if(isset($_GET['ok']))echo "<div class='alert alert-success py-2'>Event created.</div>";?>
<form class="mb-3"><input name="q" value="<?=e($q)?>" class="form-control" placeholder="Search events"></form>
<div class="table-responsive"><table class="table align-middle"><tr><th>Name</th><th>Date</th><th>Seats</th><th>Status</th><th>Actions</th></tr>
<?php foreach($s as $ev){$n=reg_count($pdo,$ev['id']);echo "<tr><td>".e($ev['event_name'])."</td><td>".e($ev['event_date'])."</td><td>$n / {$ev['capacity']}</td><td><span class='badge bg-info text-dark'>".e($ev['status'])."</span></td><td>
<a class='btn btn-sm btn-outline-light' href='participants.php?event={$ev['id']}'>Participants</a>
<form method='post' class='d-inline'><input type='hidden' name='csrf' value='".csrf()."'><input type='hidden' name='id' value='{$ev['id']}'>
<button name='act' value='Registration Open' class='btn btn-sm btn-success'>Open</button> <button name='act' value='Registration Closed' class='btn btn-sm btn-warning'>Close</button> <button name='act' value='Completed' class='btn btn-sm btn-secondary'>Complete</button>
<button name='act' value='delete' class='btn btn-sm btn-danger' onclick=\"return confirm('Delete event and its registrations?')\">Delete</button></form></td></tr>";}?></table></div><?php foot();
