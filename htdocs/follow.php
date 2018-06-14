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

$user_id = get_get_data('follow_detail');
$my_id = $_SESSION['login_id'];
$date = date('Y年m月d日 H:i:s');
$error_message = '';

try {
  $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

  if (isset($_POST['update_follow']) === TRUE) {
    $follow_id = get_post_data('follow_id');
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

    header('Location:' . ROOT_URL . '/follow.php?follow_detail=' . $user_id);
    exit;
  }

  $list_query = $dbh->prepare(
    "SELECT account_table.user_id, account_table.account, profile_table.name, profile_table.picture
    FROM follow_table
    JOIN account_table ON follow_table.follow_id = account_table.user_id
    JOIN profile_table ON follow_table.follow_id = profile_table.user_id
    WHERE follow_table.user_id = :user_id AND follow_table.status = 1
    ORDER BY follow_table.update_date DESC;"
  );

  $list_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $list_query->execute();
  $follow_data = $list_query->fetchAll();
  $list_query = NULL;

  $status_query = $dbh->prepare(
    "SELECT follow_id, status from follow_table
    WHERE user_id = :my_id AND follow_id IN
    (SELECT account_table.user_id
      FROM follow_table
      JOIN account_table ON follow_table.follow_id = account_table.user_id
      WHERE follow_table.user_id = :user_id AND follow_table.status = 1
      ORDER BY follow_table.update_date DESC);"
    );

    $status_query->bindParam(':my_id', $my_id, PDO::PARAM_INT);
    $status_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $status_query->execute();
    $status_data = $status_query->fetchAll();
    $status_query = NULL;

    $dbh = NULL;
  } catch (PDOException $e) {
    $error_message = 'フォローしている人をを表示できませんでした。管理者に問い合わせてください。';
  }

  $follow_data = set_follow_status($follow_data, $status_data);
  $follow_data = change_array_entity($follow_data);
  $error_message = change_entity($error_message);

  include_once VIEW_DIRECTORY . '/follow.html';
