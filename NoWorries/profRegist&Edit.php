<?php

require('function.php');

debug('..............................');
debug('プロフィール登録＆編集ページ');
debug('..............................');
debugLogStart();

//ログイン認証
require('auth.php');

//ユーザー情報を取得する。
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報:' .print_r($dbFormData, true));

//POST送信されていた場合、ユーザー情報を変数に代入
if(!empty($_POST)){

  debug('POST送信があります。');
  debug('POST情報' .print_r($_POST, true));

  $username= $_POST['username'];
  $job= $_POST['job'];
  $age= (!empty($_POST['age']))? $_POST['age'] : 0;
  $character= $_POST['character'];
  $favWords= $_POST['favWords'];
  $pic= (!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'], 'pic'): '';
  $pic= (empty($pic) && $dbFormData['pic'])? $dbFormData['pic']: $pic;

//DBに保存されている情報と入力情報が異なっている場合にバリデーションチェックを行う。
  if($dbFormData['username'] !== $username){
    //最大文字数チェック
    validMaxLen($username, 'username');
  }

  if($dbFormData['job'] !== $job){
    //最大文字数チェック
    validMaxLen($job, 'job');
  }
  
  if($dbFormData['age'] !== $age){
    //最大文字数チェック
    validMaxLen($age, 'age');
    //半角チェック
    validHalf($age, 'age');
  }

  if($dbFormData['character'] !== $character){
    //最大文字数チェック
    validMaxLen($character, 'character');
  }

  if($dbFormData['favWords'] !== $favWords){
    //最大文字数チェック
    validMaxLen($favWords, 'favWords');
  }

  if(empty($err_msg)){
    debug('バリデーションOKです');

//DB接続してデータを更新する
try{
  $dbh= dbConnect();
  $sql= 'UPDATE users SET username= :username, job= :job, personality= :character, age= :age, favorite_words= :favWords pic= :pic WHERE id= :u_id';
  $data= array(':username' => $username, ':job' => $job, ':character' => $character, ':age' => $age, ':favWords' => $favWords, ':pic' => $pic, ':u_id' => $dbFormData['id']);
  $stmt= queryPost($dbh, $sql, $data);

  if($stmt){
    debug('マイページへ遷移します。');
    header('Location:mypage.php');
  }else{
    debug('クエリ失敗しました。');
  }

} catch(Exception $e){
  error_log('エラー発生:' .$e->getMessage());
  $err_msg['common']= MSG08;
    }
  }
}
debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>> 画面表示処理終了');
?>

<!--画面パート-->
<html lang='ja'>
  <head>
  <meta charset= 'utf8'>
    <title>ユーザー登録</title>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>

<!--menu-->
<?php require('header.php'); ?>
<?php require('sidebar-mypage.php'); ?>

<!--main-->
<div class='contents' class='site-width'>

  <section id='main'>

    <div class='form-container'>
      <form action='' method='post' enctype='multipart/form-data' class='form'>
        <h2 class='title'>プロフィール編集</h2>

        <div class='area-msg'>
          <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
        </div>

        <label class= '<?php if(!empty($err_msg['username'])) echo 'err'; ?>'>
        名前
        <input type='text' name= 'username' value='<?php echo getFormData('username'); ?>'>
        </label>
        <div class='area-msg'>
          <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
        </div>

        <label class='<?php if(!empty($err_msg['job'])) echo 'err'; ?>'>
        職業
        <input type='text' name='job' value='<?php getFormData('job'); ?>'>
        </label>
        <div class='area-msg'>
          <?php if(!empty($err_msg['job'])) echo $err_msg['job']; ?>
        </div>

        <label class='<?php if(!empty($err_msg['age'])) echo 'err'; ?>'>
        年齢
        <input type='number' name='age' value='<?php getFormData('age'); ?>'>
        </label>
        <div class='area-msg'>
          <?php if(!empty($err_msg['age'])) echo $err_msg['age']; ?>
        </div>

        <label class='<?php if(!empty($err_msg['character'])) echo 'err'; ?>'>
        あなたはどんな人？
        <textarea id='js-count' cols='50' rows='10' name='character' placeholder='あなたが今までどのような経験をされてきたのか簡単にお書きください' value='<?php getFormData('character'); ?>'></textarea>
        </label>
        <p class='counter-text'><span id='js-count-view'>0</span>/250</p>
        <div class='area-msg'>
          <?php if(!empty($err_msg['job'])) echo $err_msg['job']; ?>
        </div>

        <label class='<?php if(!empty($err_msg['favWords'])) echo 'err'; ?>'>
        座右の銘
        <input type='text' name='favWords' value='<?php getFormData('favWords'); ?>'>
        </label>
        <div class='area-msg'>
          <?php if(!empty($err_msg['favWords'])) echo $err_msg['favWords']; ?>
        </div>

        プロフィール画像</br>
          <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:150px;">
          画像を選択してください
            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
            <input type="file" name="pic" class="input-file" >
            <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
          </label>
          <div class="area-msg">
            <?php 
            if(!empty($err_msg['pic'])) echo $err_msg['pic'];
            ?>
          </div>

        <div class='btn-container'>
          <input type='submit' class='btn btn-mid' value='登録する'>
        </div>

          </form>
        </div>
  </section>

</div>

<?php
require('footer.php');
?>
