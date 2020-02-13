<?php
require('function.php');
debug('..........................');
debug('ユーザー登録画面');
debug('..........................');
debugLogStart();

//POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信されています');
//ユーザー情報を変数に格納する
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  //未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  //email形式チェック
  validEmail($email, 'email');
  //email最大文字数チェック
  validMaxLen($email, 'email');
  //email重複チェック
  validEmailDup($email, 'email');

  //パスワードの半角チェック
  validHalf($pass, 'pass');
  //パスワードの最大文字数チェック
  validMaxLen($pass, 'pass');
  //パスワードの最小文字チェック
  validMinLen($pass, 'pass');

  if(empty($err_msg)){
    //パスワードと再入力があっているかどうか
  validMatch($pass, $pass_re,'pass');


  if(empty($err_msg)){
    //DB接続
    try{
      $dbh= dbConnect();
      $sql= 'INSERT INTO users (email, pass, login_time, create_date) VALUES(:email, :pass, :login_time, :create_date)';
      $data= array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
      $stmt= queryPost($dbh, $sql, $data);

    //もしもクエリが成功した場合
      if($stmt){

        $_SESSION['login_date'] = time();
        $_SESSION['login_limit'] = 60*60;
        $_SESSION['user_id'] = $dbh -> lastInsertId();

        debug('セッション変数の中身;' .print_r($_SESSION, true));
        
        header('Location:login.php');
        }
      }catch(Exception $e){
        error_log('エラー情報発生' .$e->getMessage());
        $err_msg['common'] = MSG08;
      }
    }
  }
}
?>

<!--画面処理部分-->
<html lang= 'ja'>
  <head>
    <meta charset= 'utf8'>
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>

<!--menu-->
<?php require('header.php'); ?>
<!--main部分-->
<div id="contents" class="site-width">

      <section id="main" >

        <div class="form-container">
        
          <form action="" method="post" class="form">
            <h2 class="title">ユーザー登録</h2>
            <div class="area-msg">
              <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
            </div>
            <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['email'])) echo $err_msg['email'];
              ?>
            </div>
            <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
              パスワード <span style="font-size:12px">※英数字６文字以上</span>
              <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['pass'])) echo $err_msg['pass'];
              ?>
            </div>
            <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
              パスワード（再入力）
              <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
            </label>
            <div class="area-msg">
              <?php 
              if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re'];
              ?>
            </div>
            <div class="btn-container">
              <input type="submit" class="btn btn-mid" value="登録する">
            </div>
          </form>
        </div>

      </section>

    </div>
    <?php require('footer.php'); ?>
    
</html>