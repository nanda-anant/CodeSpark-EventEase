<?php
session_start();
$root=rtrim(str_replace('\\','/',realpath($_SERVER['DOCUMENT_ROOT'])),'/');
$app=str_replace('\\','/',realpath(__DIR__.'/..'));
define('BASE',(strpos($app,$root)===0)?substr($app,strlen($root)):'/eventease');
$opts=[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC];
try{
 $pdo=new PDO('mysql:host=127.0.0.1;charset=utf8mb4','root','',$opts);
 $pdo->exec('CREATE DATABASE IF NOT EXISTS eventease CHARACTER SET utf8mb4');
 $pdo->exec('USE eventease');
 if(!$pdo->query("SHOW TABLES LIKE 'events'")->fetch()){ // first run: auto-import schema + demo data
  $sql=file_get_contents(__DIR__.'/../database/eventease.sql');
  foreach(preg_split('/;\s*[\r\n]+/',$sql) as $q){if(trim($q)!=='')$pdo->exec($q);}
 }
}catch(Exception $x){die('Database error: '.htmlspecialchars($x->getMessage()).'. Make sure MySQL is running in XAMPP and the root user has no password.');}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf(){if(empty($_SESSION['t']))$_SESSION['t']=bin2hex(random_bytes(16));return $_SESSION['t'];}
function csrf_ok(){return hash_equals($_SESSION['t']??'',$_POST['csrf']??'');}
function need_admin(){if(empty($_SESSION['admin'])){header('Location: '.BASE.'/login.php');exit;}}
function log_act($pdo,$t,$m){$pdo->prepare('INSERT INTO activity_log(type,message) VALUES(?,?)')->execute([$t,$m]);}
function reg_count($pdo,$id){$s=$pdo->prepare('SELECT COUNT(*) FROM registrations WHERE event_id=?');$s->execute([$id]);return (int)$s->fetchColumn();}
function reg_state($ev,$n){$d=date('Y-m-d');
 if(!in_array($ev['status'],['Published','Registration Open']))return 'Closed';
 if($d<$ev['registration_start'])return 'Not Started';if($d>$ev['registration_end'])return 'Deadline Passed';
 if($n>=$ev['capacity'])return 'Full';return 'Open';}
function head($t,$admin=false){$b=BASE;echo "<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width,initial-scale=1'><title>".e($t)." | EventEase</title>
<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css' rel='stylesheet'>
<link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css' rel='stylesheet'>
<link href='$b/assets/css/style.css' rel='stylesheet'></head><body>
<nav class='navbar navbar-expand-lg navbar-dark nav-glass'><div class='container'><a class='navbar-brand fw-bold' href='$b/index.php'><i class='fa-solid fa-bolt text-info'></i> EventEase</a>
<button class='navbar-toggler' data-bs-toggle='collapse' data-bs-target='#nv'><span class='navbar-toggler-icon'></span></button><div id='nv' class='collapse navbar-collapse'><ul class='navbar-nav ms-auto gap-lg-2'>";
 if($admin){foreach(['dashboard'=>'Dashboard','events'=>'Events','create-event'=>'New Event','participants'=>'Participants','checkin'=>'Check-in','reports'=>'Reports'] as $k=>$v)echo "<li class='nav-item'><a class='nav-link' href='$b/admin/$k.php'>$v</a></li>";echo "<li><a class='nav-link text-warning' href='$b/logout.php'>Logout</a></li>";}
 else echo "<li><a class='nav-link' href='$b/events.php'>Events</a></li><li><a class='nav-link' href='$b/admin/dashboard.php'>Admin</a></li>";
 echo "</ul></div></div></nav><main class='container py-4'>";}
function foot(){echo "</main><footer class='text-center text-secondary py-4'>EventEase · Team CodeSpark · Code Carnival 3.0</footer><script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js'></script></body></html>";}
