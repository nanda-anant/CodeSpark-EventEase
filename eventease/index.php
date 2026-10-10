<?php require 'config/database.php';head('Home');?>
<section class="hero">
  <h1>EVENTEASE</h1>
  <p class="lead">College Event Registration &amp; 
    Check-In</p>
  <h4 class="text-info my-3">Register. Scan. Check In. Done.</h4>
  <a href="events.php" class="btn btn-info btn-lg me-2">Explore Events</a>
  <a href="admin/dashboard.php" class="btn btn-outline-light btn-lg">Admin Login</a>
</section>
<div class="row g-3">
  <?php foreach([['pen-to-square','Easy Registration'],['qrcode','Smart QR Entry'],['bolt','Fast Check-In'],['shield-halved','Duplicate Prevention'],['users','Real-Time Attendance'],['chart-pie','Event Analytics']] as $f)
  echo "<div class='col-md-4'>
  <div class='card hov p-4 text-center h-100'>
    <i class='fa-solid fa-{$f[0]} fa-2x text-info mb-2'></i>
    <h5>{$f[1]}</h5></div></div>";?>
</div>
<?php foot();
