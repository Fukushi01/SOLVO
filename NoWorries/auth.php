<?php
//   自動ログイン認証ページ
//1  login_dateがあった場合
//2  ログイン有効期限オーバーだった場合
//3  ログイン有効期限内だった場合
//4  login_dateがそもそもない場合

if(!empty($_SESSION['login_date'])){
  debug('ログイン済みです。');

  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限オーバーです。');

    //セッションを破棄します。
    session_destroy();

    header('Location:login.php');

  }else{
    debug('ログイン有効期限内です');

    $_SESSION['login_date'] = time();
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
    header('Location:mypage.php');
  }
}

} else{
  debug('未ログインです');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php')
  header('Location:login.php');
}

?>