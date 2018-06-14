<?php
require_once '/home/jmbt820/include/conf/const.php';
require_once MODEL_DIRECTORY . '/function.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location:' . ROOT_URL . '/mine.php');
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

header('Expires: -1');
header('Cache-Control:');
header('Pragma:');

$name = get_post_data('name');
$myself = get_post_data('myself');
$place = get_post_data('place');
$error_message = array();

if (isset($_POST['edit']) === TRUE) {

  if (is_blank($name) === TRUE) {
    $error_message[] = '名前を入力してください。';
  } else if (mb_strlen($name) > 20) {
    $error_message[] = '名前は20字以内にしてください。';
  }

  if (mb_strlen($myself) > 160) {
    $error_message[] = '自己紹介は160字以内にしてください。';
  }

  if (mb_strlen($place) > 30) {
    $error_message[] = '場所は30字以内にしてください。';
  }

  if (count($error_message) <= 0) {
    $user_id = $_SESSION['login_id'];
    $date = date('Y年m月d日 H:i:s');

    try {
      $dbh = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset='. DB_CHARACTER_SET , DB_USER, DB_PASSWD);

      if ((exif_imagetype($_FILES["picture"]["tmp_name"]) !== FALSE) && (check_img($_FILES["picture"]["type"]) === TRUE)) {
        $picture = $_SESSION['login_id'] . 'profile';
        move_uploaded_file($_FILES["picture"]["tmp_name"], "picture/" . $picture);
        $update_query = $dbh->prepare(
          "UPDATE profile_table
          SET name=:name, place=:place, myself=:myself, picture=:picture, update_date=:date
          WHERE user_id = :user_id;"
        );

        $update_query->bindParam(':picture', $picture, PDO::PARAM_STR);

      } else {
        $update_query = $dbh->prepare(
          "UPDATE profile_table
          SET name=:name, place=:place, myself=:myself, update_date=:date
          WHERE user_id = :user_id;"
        );
      }

      $update_query->bindParam(':name', $name, PDO::PARAM_STR);
      $update_query->bindParam(':place', $place, PDO::PARAM_STR);
      $update_query->bindParam(':myself', $myself, PDO::PARAM_STR);
      $update_query->bindParam(':date', $date, PDO::PARAM_STR);
      $update_query->bindParam(':user_id', $user_id, PDO::PARAM_INT);

      $update_query->execute();
      $update_query = NULL;

      $dbh = NULL;

      header('Location:' . ROOT_URL . '/profile_mine.php');
      exit;

    } catch (PDOException $e) {
      $error_message[] = 'プロフィールの更新に失敗しました。管理者に問い合わせてください。';
    }
  }
}

$name = change_entity($name);
$myself = change_entity($myself);
$place = change_entity($place);
$error_message = change_array_entity($error_message);

include_once VIEW_DIRECTORY . '/profile_edit.html';
