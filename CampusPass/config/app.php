<?php
date_default_timezone_set('Asia/Kolkata');
if (session_status() !== PHP_SESSION_ACTIVE) {
  $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
  session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax']);
  session_start();
}
define('APP_NAME', 'CampusPass');
define('BASE_URL', '');
