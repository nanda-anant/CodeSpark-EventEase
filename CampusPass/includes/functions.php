<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function flash(string $type, string $message): void { $_SESSION['flash']=['type'=>$type,'message'=>$message]; }
function get_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function csrf_token(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="'.e(csrf_token()).'">'; }
function verify_csrf(): void { if($_SERVER['REQUEST_METHOD']==='POST' && !hash_equals($_SESSION['csrf']??'', $_POST['csrf_token']??'')) { http_response_code(419); exit('Your session token expired. Go back and try again.'); } }
function require_user(): void { if(empty($_SESSION['user_id'])) { flash('warning','Please log in to continue.'); redirect('login.php'); } }
function require_admin(array $roles=[]): void { if(empty($_SESSION['admin_id'])) { redirect('login.php'); } if($roles && !in_array($_SESSION['admin_role']??'', $roles, true)) { http_response_code(403); exit('You do not have permission to access this page.'); } }
function log_action(PDO $pdo, string $action, ?string $details=null): void { if(!empty($_SESSION['admin_id'])) { $s=$pdo->prepare('INSERT INTO audit_logs(admin_id,action,details,ip_address) VALUES(?,?,?,?)'); $s->execute([$_SESSION['admin_id'],$action,$details,$_SERVER['REMOTE_ADDR']??null]); } }
function event_open(array $ev): bool { $now=date('Y-m-d H:i:s'); return $ev['status']==='published' && (empty($ev['registration_start']) || $ev['registration_start'] <= $now) && (empty($ev['registration_end']) || $ev['registration_end'] >= $now) && (!empty($ev['event_date']) && $ev['event_date'] >= date('Y-m-d')) && (int)$ev['registered_count'] < (int)$ev['capacity']; }
function event_image(?string $path): string { return $path ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1000&q=80'; }

/* ===================== VALIDATION HELPERS (server-side) ===================== */
function render_errors($errors): string {
  $errors = array_values(array_filter((array)$errors, 'strlen'));
  if (!$errors) return '';
  $h = '<div class="alert alert-danger form-errors" role="alert"><strong><i class="bi bi-exclamation-triangle-fill me-1"></i>Please fix the following:</strong><ul class="mb-0 mt-2">';
  foreach ($errors as $m) { $h .= '<li>' . e($m) . '</li>'; }
  return $h . '</ul></div>';
}
function v_name(string $v, int $min = 2, int $max = 60): ?string {
  $len = mb_strlen($v);
  if ($len === 0) return 'Full name is required.';
  if ($len < $min) return "Name must be at least $min characters.";
  if ($len > $max) return "Name must not be longer than $max characters.";
  if (!preg_match("/^[\\p{L}][\\p{L} .'-]*$/u", $v)) return 'Name can contain only letters, spaces, dots, apostrophes and hyphens.';
  return null;
}
function v_email(string $v): ?string {
  if ($v === '') return 'Email address is required.';
  if (mb_strlen($v) > 120 || !filter_var($v, FILTER_VALIDATE_EMAIL)) return 'Enter a valid email address.';
  return null;
}
function normalize_mobile(string $v): string {
  $d = preg_replace('/\D/', '', $v);
  return strlen($d) > 10 ? substr($d, -10) : $d;
}
function v_mobile(string $v): ?string {
  if ($v === '') return 'Mobile number is required.';
  $d = preg_replace('/[\s-]/', '', $v);
  if (!preg_match('/^(\+91|91|0)?[6-9][0-9]{9}$/', $d)) return 'Enter a valid 10-digit mobile number starting with 6, 7, 8 or 9.';
  return null;
}
function v_password(string $v, int $min = 8): ?string {
  if ($v === '') return 'Password is required.';
  if (strlen($v) < $min) return "Password must be at least $min characters.";
  if (strlen($v) > 72) return 'Password must not be longer than 72 characters.';
  if (!preg_match('/[a-z]/', $v) || !preg_match('/[A-Z]/', $v) || !preg_match('/[0-9]/', $v) || !preg_match('/[^A-Za-z0-9]/', $v)) {
    return 'Password must include an uppercase letter, a lowercase letter, a number and a special character.';
  }
  return null;
}
function v_date(string $v): bool {
  $d = DateTime::createFromFormat('Y-m-d', $v);
  return $d && $d->format('Y-m-d') === $v;
}
function v_time(string $v): bool {
  $d = DateTime::createFromFormat('H:i', substr($v, 0, 5));
  return $d && $d->format('H:i') === substr($v, 0, 5);
}
function parse_dt(string $v): ?DateTime {
  foreach (['Y-m-d\TH:i', 'Y-m-d\TH:i:s', 'Y-m-d H:i:s', 'Y-m-d H:i'] as $f) {
    $d = DateTime::createFromFormat($f, $v);
    if ($d && $d->format($f) === $v) return $d;
  }
  return null;
}
/**
 * Validates event form input (create + edit).
 * $orig = existing event row when editing (null when creating).
 * Returns [errors[], cleaned[]].
 */
function validate_event(array $in, ?array $orig = null, int $minCapacity = 1): array {
  $err = [];
  $d = [];
  foreach (['title','category','description','venue','organiser','rules','image_url','event_date','start_time','end_time','capacity','registration_start','registration_end'] as $k) {
    $d[$k] = trim((string)($in[$k] ?? ''));
  }
  $d['status'] = (string)($in['status'] ?? 'draft');

  $n = mb_strlen($d['title']);
  if ($n < 3 || $n > 200) $err[] = 'Event title must be between 3 and 200 characters.';
  $n = mb_strlen($d['category']);
  if ($n < 2 || $n > 80) $err[] = 'Category must be between 2 and 80 characters.';
  elseif (!preg_match('/^[\p{L}\p{N} &\/,.-]+$/u', $d['category'])) $err[] = 'Category contains characters that are not allowed.';
  $n = mb_strlen($d['description']);
  if ($n < 10) $err[] = 'Description must be at least 10 characters.';
  elseif ($n > 5000) $err[] = 'Description must not be longer than 5000 characters.';
  $n = mb_strlen($d['venue']);
  if ($n < 3 || $n > 200) $err[] = 'Venue must be between 3 and 200 characters.';
  if (mb_strlen($d['organiser']) > 160) $err[] = 'Organiser name must not be longer than 160 characters.';
  if (mb_strlen($d['rules']) > 5000) $err[] = 'Rules must not be longer than 5000 characters.';

  // Event date
  $dateOk = v_date($d['event_date']);
  if (!$dateOk) $err[] = 'Enter a valid event date.';
  elseif ((!$orig || $orig['event_date'] !== $d['event_date']) && $d['event_date'] < date('Y-m-d')) $err[] = 'Event date cannot be in the past.';

  // Times
  $startOk = v_time($d['start_time']);
  if (!$startOk) $err[] = 'Enter a valid start time.';
  if ($d['end_time'] !== '') {
    if (!v_time($d['end_time'])) $err[] = 'Enter a valid end time.';
    elseif ($startOk && substr($d['end_time'], 0, 5) <= substr($d['start_time'], 0, 5)) $err[] = 'End time must be after the start time.';
  }

  // Capacity
  if (!ctype_digit($d['capacity']) || (int)$d['capacity'] < 1) $err[] = 'Capacity must be a whole number of at least 1.';
  elseif ((int)$d['capacity'] > 100000) $err[] = 'Capacity cannot be more than 100000.';
  elseif ((int)$d['capacity'] < $minCapacity) $err[] = "Capacity cannot be below the $minCapacity existing valid registrations.";

  // Status
  if (!in_array($d['status'], ['draft','published','closed','cancelled'], true)) $err[] = 'Invalid event status.';

  // Registration window
  $rs = $d['registration_start'] !== '' ? parse_dt($d['registration_start']) : null;
  $re = $d['registration_end'] !== '' ? parse_dt($d['registration_end']) : null;
  if ($d['registration_start'] !== '' && !$rs) $err[] = 'Enter a valid registration start date and time.';
  if ($d['registration_end'] !== '' && !$re) $err[] = 'Enter a valid registration end date and time.';
  if ($rs && $re && $re <= $rs) $err[] = 'Registration end must be after registration start.';
  if ($re && $dateOk && $re->format('Y-m-d') > $d['event_date']) $err[] = 'Registration cannot close after the event date.';

  // Image URL
  if ($d['image_url'] !== '') {
    if (mb_strlen($d['image_url']) > 1000 || !filter_var($d['image_url'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $d['image_url'])) {
      $err[] = 'Image URL must be a valid http:// or https:// link.';
    }
  }

  $d['registration_start_db'] = $rs ? $rs->format('Y-m-d H:i:s') : null;
  $d['registration_end_db'] = $re ? $re->format('Y-m-d H:i:s') : null;
  $d['start_time'] = $startOk ? substr($d['start_time'], 0, 5) : $d['start_time'];
  return [$err, $d];
}
