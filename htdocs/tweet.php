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

$user_id = $_SESSION['login_id'];
$date = date('Y年m月d日 H:i:s');
$tweet = get_post_data('tweet');
$monologue = get_post_data('monologue');

if (mb_strlen($tweet) > 140) {
  $message = 'ツイートは140字以内にしてください。';
}

if (is_blank($tweet) === TRUE) {
  $message = 'ツイートを入力してください。';
}

if (isset($message) === FALSE) {
  try {
    $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

    if ($monologue === '') {
      $tweet_query = $dbh->prepare(
        "INSERT INTO tweet_table(user_id, tweet, tweet_date)
        VALUES(:user_id, :tweet, :date);"
      );
    } else {
      $tweet_query = $dbh->prepare(
        "INSERT INTO tweet_table(user_id, tweet, tweet_date, status)
        VALUES(:user_id, :tweet, :date, 2);"
      );
    }
    $tweet_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $tweet_query->bindParam(':tweet', $tweet, PDO::PARAM_STR);
    $tweet_query->bindParam(':date', $date, PDO::PARAM_STR);

    $tweet_query->execute();
    $tweet_query = NULL;

    $dbh = NULL;
    $message = 'ツイートを送信しました。';

  } catch (PDOException $e) {
    $message = 'データベースに接続できませんでした。管理者に問い合わせてください。';
  }
}

$_SESSION['message'] = $message;

header('Location:' . ROOT_URL . '/home.php');
exit;
