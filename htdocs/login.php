<?php
require_once '/var/www/html/Tweeter/include/const/const.php';
require_once MODEL_DIRECTORY . '/function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location:' . ROOT_URL . '/top.php');
  exit;
}

if (isset($_SESSION) === FALSE) {
  session_name('%*[cuHdJ+6o2th4E');
  session_start();
}

if (session_name() !== '%*[cuHdJ+6o2th4E') {
  header('Location:' . ROOT_URL . '/logout.php');
  exit;
}

$cookie_check = get_post_data('cookie_check');
$login_code  = get_post_data('login_code');
$login_password = get_post_data('login_password');

$error_message = array();
$_SESSION['login_error_message'] = array();
$_SESSION['register_error_message'] = array();

if (check_account($login_code) === TRUE || check_mail($login_code) !== FALSE) {
  try {
    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

    $list_query = $dbh->prepare(
      "SELECT user_id, password
      FROM account_table
      WHERE account ='$login_code' OR mail ='$login_code';"
    );

    $list_query->bindParam(':login_code', $login_code, PDO::PARAM_STR);
    $list_query->execute();
    $user_data = $list_query->fetch();
    $list_query = NULL;

    $dbh = NULL;
  } catch (PDOException $e) {
    $error_message[] = 'ログインできませんでした。管理者に問い合わせてください。';
  }
}

if (password_verify($login_password, $user_data['password']) === TRUE) {

  if ($cookie_check === 'checked') {
    setcookie('login_code'   , $login_code, time() + 60 * 60 * 24 * 365);
  } else {
    setcookie('login_code'   , '', time() - 42000);
  }
  $_SESSION['login_id'] = $user_data['user_id'];
  header('Location:' . ROOT_URL . '/home.php');
  exit;

} else {
  $error_message[] = '入力されたユーザー名やパスワードが正しくありません。確認してからやりなおしてください。';
  $_SESSION['login_error_message'] = $error_message;
  $_SESSION['login_code'] = $login_code;
  header('Location:' . ROOT_URL . '/top.html');
  exit;
}
