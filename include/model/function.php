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
