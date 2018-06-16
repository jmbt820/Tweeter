<?php
require_once '/var/www/html/Tweeter/include/const/const.php';
require_once MODEL_DIRECTORY . '/function.php';

if (isset($_SESSION) === FALSE) {
  session_name('%*[cuHdJ+6o2th4E');
  session_start();
}

if (session_name() !== '%*[cuHdJ+6o2th4E') {
  header('Location:' . ROOT_URL . '/logout.php');
  exit;
}

if (isset($_SESSION['login_id']) !== TRUE) {
  header('Location:' . ROOT_URL . '/top.php');
  exit;
}

$my_id = $_SESSION['login_id'];
$error_message = '';

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

  if (isset($_POST['delete_tweet']) === TRUE) {
    $tweet_id = get_post_data('tweet_id');

    $delete_query = $dbh->prepare(
      "UPDATE tweet_table
      SET delete_date = :date, status = 0
      WHERE tweet_id = :tweet_id;"
    );

    $delete_query->bindParam(':date', $date, PDO::PARAM_STR);
    $delete_query->bindParam(':tweet_id', $tweet_id, PDO::PARAM_INT);

    $delete_query->execute();
    $delete_query = NULL;

    header('Location:' . ROOT_URL . '/profile_mine.php');
    exit;
  }

  $my_query = $dbh->prepare(
    "SELECT account_table.account, profile_table.name, profile_table.myself, profile_table.place, profile_table.picture
    FROM profile_table
    JOIN account_table ON profile_table.user_id = account_table.user_id
    WHERE account_table.user_id = :my_id;"
  );

  $my_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
  $my_query->execute();
  $my_data = $my_query->fetch();
  $my_query = NULL;

  $tweet_query = $dbh->prepare(
    "SELECT tweet_table.tweet_id, account_table.user_id, account_table.account, profile_table.name, profile_table.picture, tweet_table.tweet, tweet_table.tweet_date, tweet_table.status
    FROM tweet_table
    JOIN account_table ON tweet_table.user_id = account_table.user_id
    JOIN profile_table ON tweet_table.user_id = profile_table.user_id
    WHERE tweet_table.status != 0 AND account_table.user_id = :my_id
    ORDER BY tweet_table.tweet_date DESC LIMIT 100;"
  );

  $tweet_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
  $tweet_query->execute();
  $tweet_data = $tweet_query->fetchAll();
  $tweet_query = NULL;

  $dbh = NULL;
} catch (PDOException $e) {
  $error_message = '管理者に問い合わせてください';
}

$my_data = change_array_entity($my_data);
$tweet_data = change_array_entity($tweet_data);
$error_message = change_entity($error_message);

include_once VIEW_DIRECTORY . '/profile_mine.html';
