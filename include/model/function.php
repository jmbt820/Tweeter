<?php

/**
* 文字列をHTMLエンティティに変換する
* @param str  $str 変換前文字列
* @return str HTMLエンティティ変換後文字列
*/
function change_entity($str) {
  return htmlspecialchars($str, ENT_QUOTES, HTML_CHARACTER_SET);
}

/**
* 文字列配列をHTMLエンティティに変換する
* @param array  $str_array 変換前配列
* @return array HTMLエンティティ変換後配列
*/
function change_array_entity($str_array) {

  foreach ($str_array as $key => $value) {
    if(is_array($value) === TRUE){
      $str_array[$key] = change_array_entity($value);
    } else {
      $str_array[$key] = change_entity($value);
    }
  }
  return $str_array;
}

/**
* 格納されたPOSTデータを取得
* @param str $key 配列キー
* @return str POST値(格納されていなければ空文字を返す)
*/
function get_post_data($key) {

  $str = '';

  if (isset($_POST[$key]) === TRUE) {
    $str = $_POST[$key];
  }
  return $str;
}

/**
* 格納されたCookieを取得
* @param str $key 配列キー
* @return str Cookie(格納されていなければ空文字を返す)
*/
function get_cookie_data($key) {

  $str = '';

  if (isset($_COOKIE[$key]) === TRUE) {
    $str = $_COOKIE[$key];
  }
  return $str;
}

/**
* 文字列が空の文字列であるかどうか判定
* @param str $str　文字列
* @return bool 空文字列ならTRUEを返す
*/
function is_empty_str($str) {
  return ($str === '');
}

/**
* ユーザー名が正しい形式であるかどうか判定
* @param str $name ユーザー名
* @return bool ユーザー名が指定した形式ならTRUEを返す
*/
function check_user_name($name) {
  return (preg_match('/\A[A-Za-z0-9_]+\z/', $name) === 1);
}

/**
* パスワードが正しい形式であるかどうか判定(暫定的に半角英数字6文字以上にしています)
* @param str $password　パスワード
* @return bool パスワードが指定した形式ならTRUEを返す
*/
function check_passsword($password) {
  return (preg_match('/\A[A-Za-z0-9]{6,}\z/', $password) === 1);
}

/**
* メールアドレスが正しい形式であるかどうか判定
* @param str $mail　メールアドレス
* @return bool メールアドレスが指定した形式ならTRUEを返す
*/
function check_mail($mail) {
  return (preg_match('/\A[A-Za-z0-9\._-]+@([A-Za-z0-9_-])+([A-Za-z0-9\._-]+)*\z/', $mail) === 1);
}

/**
* ログイン時に登録されているアカウント名(またはメールアドレス)、パスワードに一致するアカウントidを取得
*
* @param obj  $dbh DBハンドル
* @param str  $login_id ユーザー名、またはメールアドレス
* @param str  $login_password パスワード
* @return array 取得した配列データ(失敗したらFALSEを返す)
*/
function get_login_id($dbh, $login_id, $login_password) {

  $sql = "SELECT user_id FROM account_table WHERE password ='$login_password' AND (user_name ='$login_id' OR mail ='$login_id')";
  $array = $dbh->query($sql);
  return $array -> fetch(PDO::FETCH_ASSOC);
}

/**
* 新規アカウント登録時、そのアカウントがすでに登録されているかアカウント名から確認(排他ロックあり)
*
* @param obj  $dbh DBハンドル
* @param str  $user_name ユーザー名
* @return array 取得した配列データ(失敗したらFALSEを返す)
*/
function get_id_by_user_name($dbh, $user_name) {

  $sql = "SELECT user_id FROM account_table WHERE user_name='$user_name' FOR UPDATE";
  $array = $dbh->query($sql);
  return $array -> fetch(PDO::FETCH_ASSOC);
}

/**
* 新規アカウント登録時、そのアカウントがすでに登録されているかメールアドレスから確認
*
* @param obj  $dbh DBハンドル
* @param str  $mail メールアドレス
* @return array 取得した配列データ(失敗したらFALSEを返す)
*/
function get_id_by_mail($dbh, $mail) {

  $sql = "SELECT user_id FROM account_table WHERE mail='$mail'";
  $array = $dbh->query($sql);
  return $array -> fetch(PDO::FETCH_ASSOC);
}

/**
* 新規アカウント登録時、アカウント情報専用テーブルに保存
*
* @param obj  $dbh DBハンドル
* @param str  $user_name ユーザー名
* @param str  $mail メールアドレス
* @param str  $password パスワード
* @param str  $date 現在の日付
* @return int テーブルに影響を与えた行数(失敗したらFALSEを返す)
*/
function insert_new_account($dbh, $user_name, $mail, $password, $date) {

  $insert_account_query = "INSERT INTO account_table(user_name, mail, password, register_date, update_date) VALUES('$user_name', '$mail', '$password','$date' , '$date');";
  return $dbh->exec($insert_account_query);
}

/**
* 新規アカウント登録時、プロフィール専用テーブルに保存
*
* @param obj  $dbh DB ハンドル
* @param str  $user_id ユーザーID
* @param str  $name 呼び名
* @param str  $date 現在の日付
* @return int テーブルに影響を与えた行数(失敗したらFALSEを返す)
*/
function insert_new_profile($dbh, $user_id, $name, $date) {

  $insert_profile_query = "INSERT INTO profile_table(user_id, name, register_date, update_date) VALUES($user_id, '$name', '$date', '$date');";
  return $dbh->exec($insert_profile_query);
}
