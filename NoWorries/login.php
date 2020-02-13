<?php

require('function.php');
debug('...........................................');
debug('ログイン画面表示');
debug('...........................................');
debugLogStart();

//POST送信された場合
if(!empty($_POST)){

//変数にユーザー情報を格納する
  $email= $_POST['email'];
  $pass= $_POST['pass'];
  $pass_save= (!empty($_POST['pass_save'])) ? true : false;

//バリデーションチェック
  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');


  //email形式チェック
  validEmail($email, 'email');
  //emailの最大文字数チェック
  validMaxLen($email, 'email');

  //パスワードの半角英数字チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字数チェック
  validMinLen($pass, 'pass');

  if(empty($err_msg)){
    //DB接続

    try{
      $dbh = dbConnect();
      $sql = 'SELECT pass, id FROM users WHERE email= :email AND delete_flg=0';
      $data= array(':email' => $email);
      $stmt = queryPost($dbh, $sql, $data);
      $result= $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果の中身' .print_r($result, true));

      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードが一致しました。');

      //最終ログイン日時を現在日時にする
      $_SESSION['login_date'] =time();

      //pass_save入力があった場合となかった場合
      if($pass_save){

        debug('ログイン保持にチェックがあります');
        $_SESSION['login_limit']= 60*60*24*30;

      }else{
        debug('ログイン保持にチェックがありません');
        $_SESSION['login_limit']= 60*60;
      }

      //ユーザーIDを格納する
      $_SESSION['user_id']= $result['id'];

      debug('セッション情報' .print_r($_SESSION, true));
      debug('マイページへ遷移します。');
      header('Location:mypage.php');

      }else{
        debug('パスワードがアンマッチです');
        $err_msg['common'] = MSG09;
      }
    }catch(EXCEPTION $e){
      error_log('エラー情報:' .$e->getMessage());
      $err_msg['common'] = MSG07;
    }
  }
}
?>
<!--画面部分-->
<html lang= 'ja'>
  <head>
    <meta charset= 'utf8'>
    <title>ログインページ</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<?php require('header.php'); ?>

<div id= 'contents' class='site-width'>

<section id= 'main'>
  <div class= 'form-container'>
    <form action= '' method= 'post' class='form'>
      <h2 class='title'>ログイン</h2>
      
      <div class='area-msg'>
        <?php
          if(!empty($err_msg['common'])) echo $err_msg['common'];
        ?>
      </div>
      
      <label class='<?php if(!empty($err_msg['email'])) echo 'err';?>'>
      Email
      <input type='text' name='email' value='<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>'>
      </label>
      <div class= 'area-msg'>
        <?php
          if(!empty($err_msg['email'])) echo $err_msg['email'];
        ?>
      </div>

      <label class= '<?php if($err_msg['pass']) echo 'err' ?>'>
      Password<span style= 'font-size: 12px'>＊英数字６文字以上</span>
      <input type='password' name= 'pass' value='<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>'>
      </label>
      <div class= 'area-msg'>
        <?php
          if(!empty($err_msg['pass'])) echo $err_msg['pass']; 
        ?>
        </div>

        <div class='pass_save'>
        <input type= 'checkbox' name= 'pass_save'>次回から自動でログインする。
        </div>

        <div class= 'btn-container'>
        <input type= 'submit' class='btn btn-mid' value= 'ログイン'>
        </div>

        <div class='link'>
        パスワードを忘れた方は<a href='passRemindSend.php'>コチラ</a>
        </div>

            </form>
          </div>
    </section>
    </div>

<?php require('footer.php'); ?>

</html>