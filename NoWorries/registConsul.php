<?php

require('function.php');
debug('...............................');
debug('コンサル内容登録編集画面');
debug('...............................');
debugLogStart();

//ログイン認証
require('auth.php');

//GETデータを取得する
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//DBから商品情報を取得する
$dbFormData= (!empty($p_id)) ? getProduct($_SESSION['user_id'], $p_id) : '';
//新規か編集画面かを判別するためのフラグを用意する
$edit_flg= (empty($dbFormData))? false : true;
//DBからカテゴリデータを取得する
$dbCategoryData= getCategory();
debug('商品ID' .$p_id);
debug('フォーム用データ' .print_r($dbFormData, true));
debug('カテゴリー用データ' .print_r($dbCategoryData, true));
debug('編集フラグ' .print_r($edit_flg));

//GETパラメーター改ざん時
if(!empty($p_id) && empty($dbFormData)){
  debug('ゲットパラメーターが改ざんされてるよ！');
  header('Location:mypage.php');
}

//POST送信された時の処理
if(!empty($_POST)){
  debug('POST送信されました。');
  debug('POST情報' .print_r($_POST, true));
  debug('FILE情報' .print_r($_FILES, true));

//ユーザー情報を変数に格納する。
$name= $_POST['name'];
$category= $_POST['category_id'];
$price= $_POST['price'];
$comment= $_POST['comment'];

//画像アップロード処理
$pic =(!empty($_FILES['pic']['name']))? uploadImg($_FILES['pic'], 'pic') : '';
$pic =(empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

//バリデーションチェック開始
//新規の場合は単にバリデーションを行うだけ。更新の場合はDBにある情報と異なる場合にバリデーションを行う。
//新規の場合
if(empty($dbFormData)){
//商品名
validRequired($name, 'name');
validMaxLen($name, 'name');
//カテゴリID
validSelect($category, 'category_id');
//詳細
validRequired($comment, 'comment');
validMaxLen($comment, 'comment', 500);
//金額
validRequired($price, 'price');
validNumber($price, 'price');
//更新の場合
}else{
if($dbFormData['title'] !== $name){
//商品名
validRequired($name, 'name');
validMaxLen($name, 'name');
}
if($dbFormData['category_id'] !== $category){
//カテゴリ　validSelect関数を用いる
validSelect($category, 'category_id');
}
if($dbFormData['content'] !== $comment){
//詳細
validRequired($comment, 'comment');
validMaxLen($comment, 'comment');
}
if($dbFormData['price'] !== $price){
//金額
validRequired($price, 'price');
validNumber($price, 'price');
  }
}
//エラーがなかった場合にDBに接続して情報を更新する。
if(empty($err_msg)){
try{
  $dbh= dbConnect();

if($edit_flg){
  debug('DB更新です。');
  $sql= 'UPDATE product SET title= :name, category_id= :category, content= :comment, price= :price, pic= :pic WHERE user_id=:user_id AND id=:p_id';
  $data= array(':name' => $name, ':category' =>$category, ':comment' => $comment, ':price' => $price, ':pic' => $pic, ':user_id' => $_SESSION['user_id'], ':p_id' => $p_id);
//新規時の処理
}else{
  debug('DB新規登録です。');
  $sql= 'INSERT INTO product (title, category_id, content, price, pic, user_id, create_date) VALUE(:name, :category, :comment, :price, :pic, :user_id, :create_date)';
  $data= array(':name' => $name, ':category' => $category, ':comment' => $comment, ':price' => $price, ':pic'=>$pic, 'user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));
  }
//クエリ実行
  $stmt= queryPost($dbh, $sql, $data);

  if($stmt){
    debug('商品データ更新の処理がうまくいきました。');
    header('Location:mypage.php');
  }
}catch(Exception $e){
  error_log('エラー発生:' .$e->getMessage());
  $err_msg['common']= MSG08;
}
}

}

debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理終了');
?>

<!--画面部分-->
<html lang='ja'>

  <head>
  <title><?php echo (empty($edit_flg)) ?'コンサル登録': 'コンサル内容編集'; ?></title>
  <meta charset='utf8'>
  <link rel="stylesheet" type="text/css" href="style.css">
  </head>

<!--menu-->
<?php require('header.php'); ?>
<?php require('sidebar-mypage.php'); ?>

<div class='contents' class='site-width'>
  <secition id='main'>
    <div class='form-container'>
      <form action='' method='post' enctype='multipart/form-data' class='form-consul-ver'>
      <h2 class='title'><?php echo (!$edit_flg)? 'コンサル登録':'コンサル内容編集'; ?></h2>
      <div class='area-msg'>
        <?php if (!empty($err_msg['common'])) echo $err_msg['common']; ?>
      </div>

      <label class='<?php if(!empty($err_msg['name'])) echo 'err'; ?>'>
      タイトル<span class='label-require'>必須</span>
      <input type='text' name='name' value='<?php echo getFormData('name'); ?>'>
      </label>
      <div class='area-msg'>
        <?php if(!empty($err_msg['name'])) echo $err_msg['name']; ?>
      </div>

      <label class="<?php if(!empty($err_msg['category_id'])) echo 'err'; ?>">
              カテゴリ<span class="label-require">必須</span>
              <select name="category_id" id="">
                <option value="0" <?php if(getFormData('category_id') == 0 ){ echo 'selected'; } ?> >選択してください</option>
                <?php
                  foreach($dbCategoryData as $key => $val){
                ?>
                  <option value="<?php echo $val['category_id'] ?>" <?php if(getFormData('category_id') == $val['category_id'] ){ echo 'selected'; } ?> >
                  <?php echo $val['name']; ?>
                  </option>
                <?php
                  }
                ?>
              </select>
            </label>
            <div class="area-msg">
              <?php
              if(!empty($err_msg['category_id'])) echo $err_msg['category_id'];
              ?>
            </div>

      <label class='<?php if(!empty($err_msg['comment'])) echo 'err'; ?>'>
          詳細<span class='label-require'>必須</span>
          <textarea name='comment' cols='30' rows='10' style='height:150px' placeholder='誰のどんな悩みの相談に乗ってあげられるのかをあなた自身の経験を踏まえてお書きください。' id='js-count'><?php echo getFormData('comment'); ?></textarea>
      </label>
      <p class='counter-text'><span id='js-count-view'>0</span>/256</p>
      <div class='area-msg'>
        <?php if(!empty($err_msg['comment'])) echo $err_msg['comment']; ?>
      </div>

      <label class='<?php if(!empty($err_msg['price'])) echo 'err'; ?>'>
          希望食事代<span class='label-require'>必須</span>
          <input type='text' name='price' style='width:150px' value='<?php echo (!empty(getFormData('price'))) ? getFormData('price') : 0;?>'><span class='option'>円</span>
      </label>
      <div class='area-msg'>
        <?php if(!empty($err_msg['price'])) echo $err_msg['price']; ?>
      </div>

    <div class='imgDrop-container'>
      画像</br>
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
      </div>
      <div class='btn-container'>
        <input type='submit' class='btn btn-mid' value='<?php echo (!empty($edit_flg))? '更新する':'登録する'; ?>'>
      </div>
              </form>
          </div>
    </section>
  </div>
    <?php
        require('footer.php');
    ?>
</html>