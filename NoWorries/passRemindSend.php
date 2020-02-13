<?php

require('function.php');
debug('....................................');
debug('パスワード再発行メール送信ページ');
debug('....................................');
debugLogStart();

//ログイン認証は無し

//POST送信されていた場合、emailのバリデーションチェックをする。
if(!empty($_POST['email'])){

  $email= $_POST['email'];
//未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
//email形式チェック
  validEmail($email, 'email');
//最大文字数チェック
  validMaxLen($email, 'email');

//バリデーションがOKだった場合、DB接続をしてユーザー情報がDB上に存在するかを確認する。
if(empty($err_msg)){
try{
  $dbh= dbConnect();
  //sql文実行
  $sql= 'SELECT count(*) FROM users WHERE email= :email';
  //データの流し込み
  $data= array(':email' => $email);
  //クエリ実行
  $stmt= queryPost($dbh, $sql, $data);
  //クエリ結果を取得する
  $result= $stmt->fetch(PDO::FETCH_ASSOC);
  //emailがDBに登録されている時
  if($stmt && array_shift($result)){
    debug('クエリ成功。DBに登録済みです。');
  //認証キーを生成
  $auth_key= makeRandKey();
//メール送信
$from= 'hk0301_everything-is-practice@ezweb.ne.jp';
$to = $email;
$subject= 'パスワード再発行認証';
$comment= <<<EOT
本メールアドレス宛にパスワード再発行依頼がありました。
下記URLにて認証キーを打ち込んでいただくとパスワードが再発行されます。
パスワード再発行認証キー入力ページ: http://localhost:8888/NoWorries/passRemindRecieve.php
認証キー{$auth_key}
*認証キーの有効期限は30分です。

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/webservice_practice07/passRemindSend.php
/////////////////////////////////////////////////////////////////////
株式会社〇〇
住所〇〇〇〇
EOT;
        sendEmail($from, $to, $subject, $comment);
//認証に必要な情報をセッションへ保存する。
        $_SESSION['auth_key']=$auth_key;
        $_SESSION['email']=$email;
        $_SESSION['auth_key_limit']= time()+(60*30);
        debug('セッション変数の中身:' .print_r($_SESSION, true));

        header('Location:passRemindReceive.php');
        }else{
          debug('クエリ失敗　OR　DBに登録のないemailアドレスが入力されました');
          $err_msg['common']= MSG8;
        }
      }catch(Exception $e){
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
  <title>パスワード再発行</title>
  <link rel='stylesheet' type='text/css' href='style.css'>
</head>

<!--menu-->
<?php require('header.php'); ?>

<!--main-->
<div class='contents' class='site-width'>

  <section id='main'>

    <div class='form-container'>
      <form action='' method='post' class='form'>
        <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
        <div class='area-msg'>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>

        <label class='<?php if(!empty($err_msg['email'])) echo 'err'; ?>'>
        Email
        <input type='text' name='email' value='<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>'>
        </label>
        <div class='area-msg'>
          <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
        </div>

        <div class='btn-container'>
          <input type='submit' class='btn btn-mid' value='送信する'>
        </div>

      </form>
      <div>
  </section>
<div>

<?php require('footer.php'); ?>

</html>