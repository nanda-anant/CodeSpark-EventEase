<?php require 'config/database.php';
$id=(int)($_GET['event_id']??$_POST['event_id']??0);
$s=$pdo->prepare('SELECT * FROM events WHERE id=?');
$s->execute([$id]);$ev=$s->fetch();
if(!$ev){
  head('Register');
  echo "<div class='alert alert-danger'>Event not found.</div>";
  foot();
  exit;
}
$err=[];$v=$_POST;
if($_SERVER['REQUEST_METHOD']=='POST'){
 if(!csrf_ok())$err[]='Invalid session token. Reload the page.';
 foreach(['full_name','email','mobile','college','department','semester','enrollment_no'] as $f)$v[$f]=trim($v[$f]??'');
 foreach(['full_name','college','department','semester','enrollment_no'] as $f)if($v[$f]==='')$err[]="$f is required.";
 if(!filter_var($v['email'],FILTER_VALIDATE_EMAIL))$err[]='Invalid email.';
 if(!preg_match('/^[6-9][0-9]{9}$/',$v['mobile']))$err[]='Mobile must be a valid 10-digit number.';
 if(!$err){
  try{$pdo->beginTransaction();
   $l=$pdo->prepare('SELECT * FROM events WHERE id=? FOR UPDATE');
   $l->execute([$id]);$ev=$l->fetch();
   $st=reg_state($ev,reg_count($pdo,$id));
   if($st!='Open')throw new Exception("Registration unavailable: $st.");
   $pdo->prepare('INSERT INTO participants(full_name,email,mobile,college,department,semester,enrollment_no)
   VALUES(?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE full_name=VALUES(full_name),mobile=VALUES(mobile),
   college=VALUES(college),department=VALUES(department),semester=VALUES(semester),
   enrollment_no=VALUES(enrollment_no),id=LAST_INSERT_ID(id)')
    ->execute([$v['full_name'],$v['email'],$v['mobile'],$v['college'],$v['department'],$v['semester'],$v['enrollment_no']]);
   $pid=(int)$pdo->lastInsertId();
   $d=$pdo->prepare('SELECT 1 FROM registrations WHERE event_id=? AND participant_id=?');
   $d->execute([$id,$pid]);
   if($d->fetch())throw new Exception('You are already registered for this event.');
   $al='ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
   for($t=0;$t<10;$t++){
    $code='EVT';
    for($i=0;$i<5;$i++)$code.=$al[random_int(0,31)];
    try{
     $pdo->prepare('INSERT INTO registrations(event_id,participant_id,registration_id,entry_code)
     VALUES(?,?,?,?)')->execute([$id,$pid,uniqid('T'),$code]);break;
  }
    catch(PDOException $x){
     if($x->errorInfo[1]!=1062)throw $x;
    }
   }
   $rid=(int)$pdo->lastInsertId();
   $reg='EVT'.date('Ymd').str_pad($rid,4,'0',STR_PAD_LEFT);
   $pdo->prepare('UPDATE registrations SET registration_id=? WHERE id=?')->execute([$reg,$rid]);
   log_act($pdo,'registration',$v['full_name'].' registered for '.$ev['event_name']);
   $pdo->commit();header('Location: pass.php?r='.$reg);
   exit;
  }
  catch(Exception $x){
   if($pdo->inTransaction())$pdo->rollBack();
   $err[]=$x->getMessage();
  }
 }
}
head('Register');?>
<h2>Register: <?=e($ev['event_name'])?></h2>
<?php foreach($err as $m)echo "<div class='alert alert-danger py-2'>".e($m)."</div>";?>
<form method="post" class="row g-3 card p-4"><input type="hidden" name="csrf" value="<?=csrf()?>"><input type="hidden" name="event_id" value="<?=$id?>">
<?php foreach(['full_name'=>'Full Name','email'=>'Email','mobile'=>'Mobile (10 digits)','college'=>'College Name','department'=>'Department','semester'=>'Semester','enrollment_no'=>'Enrollment Number'] as $k=>$l)
echo "<div class='col-md-6'><label class='form-label'>$l *</label><input required name='$k' value='".e($v[$k]??'')."' class='form-control'></div>";?>
<div class="col-12"><button class="btn btn-info btn-lg">Register &amp; Get Pass</button></div></form><?php foot();
