<?php

require('function.php');
debug('.................................');
debug('パスワード再発行認証キー入力ページ');
debug('.................................');
debugLogStart();

//ログイン認証はなし

//SESSIONに認証キーがあるか
if(empty($_SESSION['auth_key'])){
  header('Location:passRemindSend.php');
}

//POST送信されていた場合
if(!empty($err_msg)){
  $auth_key= $_POST['token'];

//未入力チェック
validRequired($auth_key, 'token');

if(empty($err_msg)){

//固定長チェック

//半角チェック
validHalf($auth_key, 'token');

if(empty($err_msg)){

  //セッションに保存されている認証キーと入力された認証キーの値が違う場合
  if($auth_key !== $_SESSION['auth_key']){
    $err_msg= MSG;
  }
  //認証キーの有効期限が過ぎている場合
  if(time() > $_SESSION['auth_key_limit']){
    $err_msg['common']= MSG;
  }
if(empty($err_msg)){
  debug('認証OK');
//パスワード生成
$pass= makeRandKey();

try{
  $dbh= dbConnect();
  $sql= 'UPDATE users SET pass= :pass WHERE email=:email';
  $data= array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
  $stmt= queryPost($dbh, $sql, $data);
  if($stmt){
    debug('クエリ成功');

//メール送信
    $from = 'hk0301_everything-is-practice@ezweb.ne.jp';
    $to = $_SESSION['auth_email'];
    $subject = 'パスワード再発行';
    $comment =<<<EOT
本メールアドレス宛にパスワードを再発行致しました。
下記のURLより再発行用のパスワードをご入力いただきログインしてください。

ログイン：http://localhost:8888/webservice_practice07/login.php
再発行用パスワード　{$pass}
*ログイン後、パスワードの再設定をお願いいたします。
/////////////////////////////////////////////////////////////
株式会社〇〇
住所〇〇〇〇
EOT;
                    //メール送信
                    sendMail($from, $to, $subject);
                    //セッション削除
                    session_unset();
                    //ログインページに遷移させる
                    header('Location:login.php');


                  }else{
                       debug('クエリ失敗');
                        $err_msg['common']= MSG08;
                        }
                    }catch(Exception $e){
                       error_log('エラー発生:' .$e->getMessage());
                        $err_msg['common']= MSG08;
                    }
        }
      }
    }
  }

?>
<!--画面部分-->
<html lang='ja'>
<head>
  <meta charset="utf-8">
  <title>パスワード再発行認証</title>
  <link rel='stylesheet' type='text/css' href='style.css'>
</head>
<!--menu-->
<?php require('header.php'); ?>

<!--main-->
<div class='contents' class='site-width'>

  <section id='main'>

    <div class='form-container'>

      <form action='method' method='post' class='form'>
        <p>認証キーをご入力ください</p>
        <div class='area-msg'>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>

      <label class='<?php if(!empty($err_msg['token'])) echo 'err'; ?>'>
        認証キー
        <input type='text' name='token' value='<?php echo getFormData('token'); ?>'>
      </label>
      <div class='area-msg'>
          <?php if(!empty($err_msg['token'])) echo $err_msg['token']; ?>
      </div>

      <div class='btn-container'>
        <input type='submit' class='btn btn-mid' value='再発行する'>
      </div>

      </form>
    </div>
    <a href='passRemindSend.php'>パスワード再発行用メールを再度送信する</a>
  </section>

</div>

<?php require('footer.php'); ?>

</html>