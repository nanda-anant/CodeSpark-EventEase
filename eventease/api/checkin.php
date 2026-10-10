<?php require '../config/database.php';header('Content-Type: application/json');
if(empty($_SESSION['admin'])||!csrf_ok()){http_response_code(403);echo json_encode(['result'=>'error','message'=>'Unauthorized']);exit;}
$code=strtoupper(trim($_POST['code']??''));
$s=$pdo->prepare('SELECT r.*,p.full_name,e.event_name FROM registrations r JOIN participants p ON p.id=r.participant_id JOIN events e ON e.id=r.event_id WHERE r.entry_code=? OR r.registration_id=?');
$s->execute([$code,$code]);$r=$s->fetch();
if(!$r){log_act($pdo,'invalid','Invalid code attempted: '.substr($code,0,20));echo json_encode(['result'=>'invalid','message'=>'INVALID ENTRY CODE']);exit;}
try{$pdo->prepare('INSERT INTO checkins(event_id,participant_id,registration_id) VALUES(?,?,?)')->execute([$r['event_id'],$r['participant_id'],$r['id']]);
 log_act($pdo,'checkin',$r['full_name'].' checked in to '.$r['event_name']);
 echo json_encode(['result'=>'success','message'=>'CHECK-IN SUCCESSFUL','name'=>$r['full_name'],'event'=>$r['event_name'],'reg'=>$r['registration_id'],'time'=>date('d M Y, h:i:s A')]);
}catch(PDOException $x){
 if($x->errorInfo[1]==1062){log_act($pdo,'duplicate','Duplicate check-in attempt: '.$r['full_name']);
  $t=$pdo->prepare('SELECT checkin_time FROM checkins WHERE registration_id=?');$t->execute([$r['id']]);
  echo json_encode(['result'=>'duplicate','message'=>'DUPLICATE CHECK-IN','detail'=>'This participant has already checked in.','name'=>$r['full_name'],'time'=>$t->fetchColumn()]);}
 else echo json_encode(['result'=>'error','message'=>'Server error']);}
