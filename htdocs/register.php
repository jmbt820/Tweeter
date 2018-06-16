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

$error_message = array();

$_SESSION['register_error_message'] = array();
$_SESSION['login_error_message'] = array();

$date = date('Y年m月d日 H:i:s');
$account  = get_post_data('account');
$password = get_post_data('password');
$mail = get_post_data('mail');
$name = get_post_data('name');

if (is_empty_str($account) === TRUE) {
  $error_message[] = 'ユーザー名を入力してください。';
} else if (check_account($account) === FALSE) {
  $error_message[] = 'ユーザー名は英数字と\'_\'(アンダーバー)が使えます。';
} else if (mb_strlen($account) > 15) {
  $error_message[] = 'ユーザー名は15字以内にしてください。';
}

if (is_empty_str($password) === TRUE) {
  $error_message[] = 'パスワードを入力してください。';
} else if (check_passsword($password) === FALSE) {
  $error_message[] = 'パスワードは半角英数字にしてください。';
} else if (mb_strlen($password) <= 5) {
  $error_message[] = 'パスワードは6文字以上にしてください。';
}

if (is_empty_str($mail) === TRUE) {
  $error_message[] = 'メールアドレスを入力してください。';
} else if (check_mail($mail) === FALSE) {
  $error_message[] = '有効なメールアドレスを入力してください。';
}

if (is_blank($name) === TRUE) {
  $error_message[] = '名前を入力してください。';
} else if (mb_strlen($name) > 20) {
  $error_message[] = '名前は20字以内にしてください。';
}

if (count($error_message) <= 0) {
  try {
    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);
    $dbh->beginTransaction();

    $list_query = $dbh->prepare(
      "SELECT account, mail
      FROM account_table
      WHERE account = :account OR mail = :mail FOR UPDATE;"
    );

    $list_query->bindParam(':account', $account, PDO::PARAM_STR);
    $list_query->bindParam(':mail', $mail, PDO::PARAM_STR);
    $list_query->execute();
    $user_data = $list_query->fetchAll();
    $list_query = NULL;

    if (count($user_data) > 0) {
      foreach ($user_data as $user) {
        if ($user['account'] === $account) {
          $error_message[] = 'そのアカウント名はすでに使用されています。別のアカウント名を入力してください。';
        }
      }
      foreach ($user_data as $user) {
        if ($user['mail'] === $account) {
          $error_message[] = 'そのメールアドレスはすでに使用されています。別のメールアドレスを入力してください。';
        }
      }
    }

    if (count($error_message) <= 0) {
      $password = password_hash($password, PASSWORD_DEFAULT);

      $insert_query = $dbh->prepare(
        "INSERT INTO account_table(account, mail, password, register_date, update_date)
        VALUES(:account, :mail, :password, :date, :date);"
      );

      $insert_query->bindParam(':account', $account, PDO::PARAM_STR);
      $insert_query->bindParam(':mail', $mail, PDO::PARAM_STR);
      $insert_query->bindParam(':password', $password, PDO::PARAM_STR);
      $insert_query->bindParam(':date', $date, PDO::PARAM_STR);
      $insert_query->execute();

      $user_id = $dbh->lastInsertId('user_id');

      $insert_query = $dbh->prepare(
        "INSERT INTO profile_table(user_id, name, register_date, update_date)
        VALUES(:user_id, :name, :date, :date);"
      );

      $insert_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
      $insert_query->bindParam(':name', $name, PDO::PARAM_STR);
      $insert_query->bindParam(':date', $date, PDO::PARAM_STR);
      $insert_query->execute();
      $insert_query = NULL;

      $dbh->commit();
      $dbh = NULL;
    }
  } catch (PDOException $e) {
    $dbh->rollBack();
    $error_message[] = 'アカウント登録に失敗しました。管理者に問い合わせてください。';
  }
}

if (count($error_message) <= 0) {
  $_SESSION['login_id'] = $user_id;
  setcookie('login_id'   , '', time() - 42000);
  header('Location:' . ROOT_URL . '/home.php');
  exit;

} else {
  $_SESSION['account'] = $account;
  $_SESSION['mail'] = $mail;
  $_SESSION['name'] = $name;
  $_SESSION['register_error_message'] = $error_message;

  header('Location:' . ROOT_URL . '/top.html');
  exit;
}
