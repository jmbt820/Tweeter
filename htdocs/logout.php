<?php

require_once '/home/jmbt820/include/conf/const.php';

if (isset($_SESSION) === FALSE) {
  session_name('%*[cuHdJ+6o2th4E');
  session_start();
}

$session_name = session_name();
$_SESSION = array();

if (isset($_COOKIE[$session_name]) === TRUE) {
  setcookie($session_name, '', time() - 42000);
}

session_destroy();

header('Location:' . ROOT_URL . '/top.html');
exit;
