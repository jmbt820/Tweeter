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

$user_id = get_get_data('user_id');
$date = date('Y年m月d日 H:i:s');
$error_message = '';

if ($_SESSION['login_id'] === $user_id) {
  header('Location:' . ROOT_URL . '/profile_mine.php');
  exit;
}

$my_id = $_SESSION['login_id'];

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

  if (isset($_POST['update_follow']) === TRUE) {
    $follow_status = get_post_data('follow_status');

    $follow_query = $dbh->prepare(
      "INSERT into follow_table(user_id, follow_id, update_date, status)
      VALUES (:my_id, :user_id,:update_date, :follow_status) ON duplicate key
      UPDATE update_date=:update_date, status=:follow_status;"
    );
    $follow_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
    $follow_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $follow_query->bindParam(':update_date', $date, PDO::PARAM_STR);
    $follow_query->bindParam(':follow_status', $follow_status, PDO::PARAM_INT);

    $follow_query->execute();
    $follow_query = NULL;
  }

  $others_query = $dbh->prepare(
    "SELECT account_table.account, profile_table.name, profile_table.myself, profile_table.place, profile_table.picture
    FROM profile_table
    JOIN account_table ON profile_table.user_id = account_table.user_id
    WHERE account_table.user_id=:user_id;"
  );

  $others_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $others_query->execute();
  $others_data = $others_query->fetch();
  $others_query = NULL;

  $status_query = $dbh->prepare(
    "SELECT follow_table.status
    FROM follow_table
    JOIN account_table ON follow_table.user_id = account_table.user_id
    WHERE follow_table.user_id=:my_id AND follow_table.follow_id=:user_id;"
  );

  if (isset($others_data['account']) === TRUE) {
    $status_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
    $status_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $status_query->execute();
    $status_data = $status_query->fetch();
    $status_query = NULL;

    if($status_data['status'] === '1') {
      $others_data['status'] = '1';
    } else {
      $others_data['status'] = '0';
    }

    $tweet_query = $dbh->prepare(
      "SELECT tweet_table.tweet_id, account_table.user_id, account_table.account, profile_table.name, profile_table.picture, tweet_table.tweet, tweet_table.tweet_date
      FROM tweet_table
      JOIN account_table ON tweet_table.user_id = account_table.user_id
      JOIN profile_table ON tweet_table.user_id = profile_table.user_id
      WHERE tweet_table.status = 1 AND account_table.user_id = :user_id
      ORDER BY tweet_table.tweet_date DESC LIMIT 100;"
    );

    $tweet_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $tweet_query->execute();
    $tweet_data = $tweet_query->fetchAll();
    $tweet_query = NULL;

    $others_data = change_array_entity($others_data);
    $tweet_data = change_array_entity($tweet_data);
  }
  $dbh = NULL;
} catch (PDOException $e) {
  $error_message = '管理者に問い合わせてください';
}

$error_message = change_entity($error_message);

include_once VIEW_DIRECTORY . '/profile_others.html';
