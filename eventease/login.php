<?php require 'config/database.php';$err='';
if($_SERVER['REQUEST_METHOD']=='POST'){
 if(!csrf_ok())
  $err='Invalid token.';
 else{
  $s=$pdo->prepare('SELECT * FROM admins WHERE email=?');
  $s->execute([trim($_POST['email']??'')]);
  $a=$s->fetch();$pw=$_POST['password']??'';
  $ok=false;
  if($a){
   if(strpos($a['password'],'PLAIN:')===0)
    {
     if(hash_equals(substr($a['password'],6),$pw)){$ok=true;$pdo->prepare('UPDATE admins SET password=? WHERE id=?')->execute([password_hash($pw,PASSWORD_DEFAULT),$a['id']]);
    }
   }
   else $ok=password_verify($pw,$a['password']);
  }
  if($ok)
  {
   session_regenerate_id(true);$_SESSION['admin']=$a['id'];
   header('Location: admin/dashboard.php');exit;
  }
  $err='Invalid email or password.';
 }
}
head('Admin Login');?>
<div class="card p-4 mx-auto" style="max-width:420px">
 <h3>Admin Login</h3>
 <?php if($err)echo "<div class='alert alert-danger py-2'>".e($err)."</div>";?>
<form method="post">
 <input type="hidden" name="csrf" value="<?=csrf()?>">
 <input name="email" type="email" class="form-control mb-3" placeholder="Email" required>
 <input name="password" type="password" class="form-control mb-3" placeholder="Password" required>
 <button class="btn btn-info w-100">Login</button>
</form>
</div>
<?php foot();
