<?php

require('function.php');

debug('....................................');
debug('ログイン変更画面');
debug('....................................');
debugLogStart();

//ログイン認証
require('auth.php');

//DB上にあるパスワードを取得する。
$dbPassData= getUser($_SESSION['user_id']);

//POST送信されていた場合、ユーザー情報を変数に格納する。
if(!empty($_POST)){

$pass_old= $_POST['pass_old'];
$pass_new= $_POST['pass_new'];
$pass_new_re= $_POST['pass_new_re'];

//未入力チェック
validRequired($pass_old, 'pass_old');
validRequired($pass_new, 'pass_new');
validRequired($pass_new_re, 'pass_new_re');

if(empty($err_msg)){

//古いパスワードと新しいパスワードの形式チェック
validPass($pass_old, 'pass_old');
validPass($pass_new, 'pass_new');

//古いパスワードとDBパスワードを照合する。
if(!password_verify($pass_old, $dbPassData['pass'])){
  $err_msg['pass_old']= MSG10;
}

//古いパスワードと新しいパスワードが同じかどうか。
if($pass_old === $pass_new){
$err_msg['pass_new'] = MSG11;
}
//新しいパスワードと再入力があっているかどうか。
validMatch($pass_new, $pass_new_re, 'pass_new_re');

if(empty($err_msg)){

//DBに接続してデータを再更新する。
try{
  $dbh= dbConnect();
  $sql= 'UPDATE users SET pass= :pass WHERE id= :id';
  $data= array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
  $stmt= queryPost($dbh, $sql, $data);

  if($stmt){

    //メールを送信
    $username= ($dbPassData['username']) ? $dbPassData['username']: '名無し';
    $from= 'hk0301_everything-is-practice@ezweb.ne.jp';
    $to= $dbPassData['email'];
    $subject= 'パスワード変更';
    $comment= <<<EOT
{$username}さん
パスワードが変更されました。
/////////////////////////////////////////
株式会社〇〇
住所〇〇〇〇
EOT;
    sendMail($from, $to, $subject, $comment);
    header('Location:mypage.php');
  }

    }catch(EXCEPTION $e){
    error_log('エラー発生:' .$e->getMessage());
    $err_msg['common'] = MSG08;
    }
    }
  }
}
?>

<!--画面部分-->
<html lang='ja'>
  <head>
    <meta charset='utf-8'>
    <title>パスワード変更</title>
    <link rel='stylesheet' type='text/css' href='style.css'>
</head>

<!--menu-->
<?php require('header.php'); ?>

<!--main-->
<div id='contents' class='site-width'>

  <section id='main'>

    <div class='form-container'>
      <form action='' method='post' class='form'>
        <h2 class='title'>パスワード変更</h2>
        <div class='area-msg'>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>

    <label class='<?php if(!empty($err_msg['pass_old'])) echo 'err';?>'>
      古いパスワード
      <input type='password' name='pass_old' value='<?php if(!empty($_POST['pass_old'])) echo $_POST['pass_old']; ?>'>
    </label>
    <div class='area-msg'>
      <?php if(!empty($err_msg['pass_old'])) echo $err_msg['pass_old']; ?>
    </div>

    <label class='<?php if(!empty($err_msg['pass_new'])) echo 'err';?>'>
      新しいパスワード
      <input type='password' name='pass_new'  value='<?php if(!empty($_POST['pass_new'])) echo $_POST['pass_new']; ?>'>
    </label>
    <div class='area-msg'>
      <?php if(!empty($err_msg['pass_new'])) echo $err_msg['pass_new']; ?>
    </div>

    <label class='<?php if(!empty($err_msg['pass_new_re'])) echo 'err';?>'>
      新しいパスワード（再入力）
      <input type='password' name='pass_new_re' value='<?php if(!empty($_POST['pass_new_re'])) echo $_POST['pass_new_re']; ?>'>
    </label>
    <div class='area-msg'>
      <?php if(!empty($err_msg['pass_new_re'])) echo $err_msg['pass_new_re']; ?>
    </div>

    <div class='btn-container'>
      <input type='submit' class='btn btn-mid' value='変更する'>
    </div>

          </form>
        </div>
  </section>

</div>

<?php require('footer.php'); ?>

</html>
