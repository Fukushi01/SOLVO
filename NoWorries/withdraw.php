<?php

require('function.php');
debug('................................');
debug('退会ページ');
debug('.................................');
debugLogStart();

//ログイン認証
require('auth.php');

//post送信された時にDBへ接続
if(!empty($_POST)){
  debug('POST送信があります');

  try{
    //DB connect
    $dbh = dbConnect();

    //SQL statement (Likeテーブルも後ほど論理削除する)
    $sql1= 'UPDATE users SET delete_flg= 1 WHERE id= :user_id';
    $sql2= 'UPDATE product SET delete_flg= 1 WHERE user_id= :user_id';
    
    //from placeholder to data
    $data = array(':user_id' => $_SESSION['user_id']);

    //execute query
    $stmt1= queryPost($dbh, $sql1, $data);
    
    //success Query 
    if($stmt1){
      session_destroy();
      debug('セッション変数の中身;' .print_r($_SESSION, true));
      debug('マイページへ遷移します');
      header('Location:signup.php');

    }else{
      debug('クエリが失敗しました');
      $err_msg['common'] = MSG08;
    }

  }catch(Exception $e){
    error_log('エラー発生:' .$e->getMessage());
    $err_msg['common'] = MSG08;
  }
}
?>

<!--画面部分-->
<html lang='ja'>
  <head>
    <meta charset= 'utf8'>
    <title>退会画面</title>
    <link rel= 'stylesheet' type='text/css' href='style.css'>
  </head>

<!--menu-->
<?php require('header.php'); ?>

<!--main-->
  <div id='contents' class='site-width'>

    <section id= 'main'>
    <?php require('sidebar-mypage.php'); ?>
    <div class= 'form-container'>
      <form action='' method='post' class= 'form'>
        <h2 class='title'>退会</h2>
        <div class= 'area-msg'>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>

        <div class= 'btn-container'>
          <input type='submit' name='submit' class='btn btn-mid' value='退会する'>
        </div>
      </form>
    </div>

    <a href='mypage.php'>マイページに戻る</a>
    </section>
</div>

<?php require('footer.php'); ?>
</html>



