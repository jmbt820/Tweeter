<?php
require_once '/var/www/html/Tweeter/include/const/const.php';
require_once MODEL_DIRECTORY . '/function.php';

$error_message = array();
$login_code = get_cookie_data('login_code');
$account = '';
$name = '';
$mail = '';

if (isset($_SESSION) === FALSE) {
  session_name('%*[cuHdJ+6o2th4E');
  session_start();
}

if (session_name() !== '%*[cuHdJ+6o2th4E') {
  header('Location:' . ROOT_URL . '/logout.php');
  exit;
}

if (isset($_SESSION['login_id']) === TRUE) {
  header('Location:' . ROOT_URL . '/home.php');
  exit;
}

if ((isset($_SESSION['login_error_message']) === TRUE) && count($_SESSION['login_error_message']) > 0) {
  $error_message = $_SESSION['login_error_message'];
  $login_code = $_SESSION['login_code'];
} else if ((isset($_SESSION['login_error_message']) === TRUE) && count($_SESSION['register_error_message']) > 0) {
  $error_message = $_SESSION['register_error_message'];
  $account = $_SESSION['account'];
  $name = $_SESSION['name'];
  $mail = $_SESSION['mail'];
}

$login_code = change_entity($login_code);
$account = change_entity($account);
$name = change_entity($name);
$mail = change_entity($mail);

$error_message = change_array_entity($error_message);

include_once VIEW_DIRECTORY . '/top.html';
