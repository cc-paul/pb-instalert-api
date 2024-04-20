<?php
  $main_host = "localhost";
  $main_user = "u546582562_paul";
  $main_pass = "Password$100";
  $main_db   = "u546582562_paul";

  /* Keys and Date */
  date_default_timezone_set('Asia/Manila');
  $trans_appkey = date('mdYHis', time());
  $global_date  = date('Y-m-d H:i:s', time());
 
  $dsn    = "mysql:host=$main_host;dbname=$main_db;charset=utf8mb4";
  $user   = $main_user;
  $passwd = $main_pass;
?>