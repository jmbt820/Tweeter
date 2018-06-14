<?php
require_once '/home/jmbt820/include/conf/const.php';
require_once MODEL_DIRECTORY . '/function.php';

if (isset($_SESSION) === FALSE) {
  session_name('%*[cuHdJ+6o2th4E');
  session_start();
}

if (session_name() !== '%*[cuHdJ+6o2th4E') {
  header('Location:' . ROOT_URL . '/logout.php');
  exit;
}

if (isset($_SESSION['login_id']) === FALSE) {
  header('Location:' . ROOT_URL . '/top.php');
  exit;
}

$message = '';
$mail = get_get_data('search_mail');
$my_id = $_SESSION['login_id'];
$date = date('Y年m月d日 H:i:s');

if (is_blank($mail) === TRUE) {
  $_SESSION['message'] = 'メールアドレスを入力してください';
  header('Location:' . ROOT_URL . '/home.php');
  exit;
}

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

  if (isset($_POST['update_follow']) === TRUE) {
    $follow_id = get_post_data('user_id');
    $follow_status = get_post_data('follow_status');

    $follow_query = $dbh->prepare(
      "INSERT into follow_table(user_id, follow_id, update_date, status)
      VALUES (:my_id, :follow_id,:update_date, :follow_status) ON duplicate key
      UPDATE update_date=:update_date, status=:follow_status;"
    );

    $follow_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
    $follow_query->bindParam(':follow_id', $follow_id, PDO::PARAM_INT);
    $follow_query->bindParam(':update_date', $date, PDO::PARAM_STR);
    $follow_query->bindParam(':follow_status', $follow_status, PDO::PARAM_INT);

    $follow_query->execute();
    $follow_query = NULL;
  }

  $search_query = $dbh->prepare(
    "SELECT account_table.user_id, account_table.account, account_table.mail, profile_table.name, profile_table.picture
    FROM account_table
    JOIN profile_table ON account_table.user_id = profile_table.user_id
    WHERE account_table.mail=:mail;"
  );

  $search_query->bindParam(':mail', $mail, PDO::PARAM_STR);
  $search_query->execute();
  $search = $search_query->fetch();
  $search_query = NULL;

  if (isset($search['mail']) === TRUE) {
    $search_id = $search['user_id'];

    $status_query = $dbh->prepare(
      "SELECT follow_table.status
      FROM follow_table JOIN account_table ON follow_table.user_id = account_table.user_id
      WHERE follow_table.user_id=:my_id AND follow_table.follow_id=:search_id;"
    );

    $status_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
    $status_query->bindParam(':search_id', $search_id, PDO::PARAM_INT);
    $status_query->execute();
    $status_data = $status_query->fetch();
    $status_query = NULL;

    if($status_data['status'] === '1') {
      $search['status'] = '1';
    } else {
      $search['status'] = '0';
    }
    $search = change_array_entity($search);
  } else {
    $message = 'そのメールアドレスは登録されていません。';
  }

  $dbh = NULL;
} catch (PDOException $e) {
  $message = 'エラーが発生しました。管理者に問い合わせてください。';
}

$message = change_entity($message);

include_once VIEW_DIRECTORY . '/search.html';
