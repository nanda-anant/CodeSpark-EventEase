<?php require_once __DIR__.'/includes/auth.php';
if(!empty($_SESSION['user_id'])) redirect('dashboard.php');
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
  verify_csrf();
  $name=trim($_POST['name']??'');
  $email=strtolower(trim($_POST['email']??''));
  $mobile=trim($_POST['mobile']??'');
  $password=$_POST['password']??'';
  $confirm=$_POST['confirm_password']??'';
  if($m=v_name($name)) $errors[]=$m;
  if($m=v_email($email)) $errors[]=$m;
  if($m=v_mobile($mobile)) $errors[]=$m;
  if($m=v_password($password,8)) $errors[]=$m;
  elseif($password!==$confirm) $errors[]='Password and confirm password do not match.';
  if(empty($_POST['terms'])) $errors[]='Please accept the terms to create an account.';
  if(!$errors){
    $s=$pdo->prepare('SELECT id FROM users WHERE email=?');$s->execute([$email]);
    if($s->fetch()) $errors[]='An account with this email already exists.';
  }
  if(!$errors){
    try{
      $s=$pdo->prepare('INSERT INTO users(name,email,mobile,password_hash) VALUES(?,?,?,?)');
      $s->execute([$name,$email,normalize_mobile($mobile),password_hash($password,PASSWORD_DEFAULT)]);
      flash('success','Account created. You can now log in.');
      redirect('login.php');
    }catch(PDOException $ex){ $errors[]='Could not create the account. This email may already be in use.'; }
  }
}
$pageTitle='Create your account';require __DIR__.'/includes/header.php'; ?>
<div class="auth-wrap"><div class="auth-card"><span class="eyebrow">Join the community</span><h1 class="h2 mt-3">Create your account</h1><p class="muted">One account for every event and ticket.</p>
<?=render_errors($errors)?>
<form method="post" data-validate novalidate autocomplete="on"><?=csrf_field()?>
<div class="mb-3"><label class="form-label" for="name">Full name</label><input class="form-control" id="name" name="name" required minlength="2" maxlength="60" data-rule="name" autocomplete="name" placeholder="e.g. Riya Patel" value="<?=e($_POST['name']??'')?>"></div>
<div class="mb-3"><label class="form-label" for="email">College email</label><input class="form-control" id="email" type="email" name="email" required maxlength="120" autocomplete="email" placeholder="name@college.edu" value="<?=e($_POST['email']??'')?>"></div>
<div class="mb-3"><label class="form-label" for="mobile">Mobile number</label><input class="form-control" id="mobile" type="tel" name="mobile" required maxlength="16" inputmode="tel" data-rule="mobile" autocomplete="tel" placeholder="10-digit mobile number" value="<?=e($_POST['mobile']??'')?>"></div>
<div class="mb-3"><label class="form-label" for="password">Password</label><input class="form-control" id="password" type="password" name="password" required minlength="8" maxlength="72" data-rule="strongpass" data-strength autocomplete="new-password"><div class="form-text">At least 8 characters with uppercase, lowercase, a number and a special character.</div></div>
<div class="mb-3"><label class="form-label" for="confirm_password">Confirm password</label><input class="form-control" id="confirm_password" type="password" name="confirm_password" required maxlength="72" data-match="#password" autocomplete="new-password"></div>
<div class="form-check mb-4"><input class="form-check-input" type="checkbox" id="terms" name="terms" value="1" required data-msg-required="Please accept the terms to continue."><label class="form-check-label" for="terms">I agree to use CampusPass responsibly and follow event rules.</label></div>
<button class="btn btn-primary w-100">Create account</button></form>
<p class="text-center mt-4 mb-0 muted">Already registered? <a href="login.php">Log in</a></p></div></div><?php require __DIR__.'/includes/footer.php'; ?>
