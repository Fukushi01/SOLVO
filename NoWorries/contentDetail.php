<?php

require('function.php');

debug('...................................');
debug('コンテンツ詳細画面');
debug('...................................');
debugLogStart();

//ログイン認証
require('auth.php');

//商品idを取得する。
$p_id= (!empty($_GET['p_id']))? $_GET['p_id'] : '';
//DBから商品データを取得する。
$viewData= getProductOne($p_id);
//ゲットパラメータに不正な値が入っている場合
if(!empty($_GET['id']) && empty($viewData)){
  debug('不正な値が入っています。');
  header('Location:display.php');
}
debug('取得したデータ' .print_r($viewData, true));

//話を聞いてみるボタンが押された場合
if(!empty($_POST['submit'])){
  debug('POST送信があります。');

//ログイン認証
require('auth.php');

try{
  $dbh= dbConnect();
  $sql='INSERT INTO board(sale_user, buy_user, product_id, create_date) VALUES(:sale_user, :buy_user, :product_id, :create_date)';
  $data= array(':sale_user' => $viewData['user_id'], ':buy_user' => $_SESSION['user_id'], ':product_id' => $p_id, ':create_date' => date('Y-m-d H-i-s'));
  $stmt= queryPost($dbh, $sql, $data);

  if($stmt){
    debug('連絡掲示板へ遷移します。');
    header("Location:chat.php?m_id=".$dbh->lastInsertID());
  }
}catch(Exception $e){
  error_log('エラー発生:' .$e->getMessage());
  $err_msg['common'] = MSG08;
}

}
debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理終了');
?>

<html lang='ja'>
  <head>
  <title>コンテンツ詳細</title>
  <meta charset= 'utf8'>
  <link rel="stylesheet" type="text/css" href="style.css">
</head>

  <style>
    .badge{
      padding: 5px 10px;
      color: black;
      background: #f6f5f4;
      margin-right: 10px;
      font-size: 16px;
      vertical-align: middle;
      position: relative;
      top: -4px;
    }

    .title{
      font-size: 28px;
      padding: 10px 0;
    }

    .detail{
      background: #f6f5f4;
      padding: 15px;
      margin-top: 15px;
      min-height: 150px;

    }

    .product-buy{
      overflow: hidden;
      margin-top: 15px;
      margin-bottom: 50px;
      height: 50px;
      line-height: 50px;
    }

    .product-buy .item-left{
      float: left
    }

    .product-buy .item-right{
      float: right;
    }

    .product-buy .price{
      font-size: 32px;
      margin-right: 30px;
    }

    .product-buy .btn{
      border: none;
      font-size: 18px;
      padding: 10px 30px;
    }

    .product-buy .btn:hover{
      cursor: pointer;
    }
    </style>

    <?php require('header.php'); ?>
    
<!--メインコンテンツ-->
<div id='contents' class='site-width'>

  <section id='main'>

  <span class='badge'><?php echo sanitize($viewData['name']); ?></span>

<div class='title'>
<?php echo sanitize($viewData['title']); ?>
</div>

<div class='detail'>
  <?php echo sanitize($viewData['content']); ?>
</div>

<div class='product-buy'>
  <div class='item-left'>
    <a href='display.php<?php echo appendGetParam(array('p_id')); ?>'>&lt;コンテンツ一覧に戻る</a>
</div>

<div class='item-right'>
<form action='' method='post'>
    <input type='submit' value='話を聞いてみる' name='submit' class='btn' style='margin-top:0;'>
</form>
  </div>
<div class='item-right'>
  希望食事代<p class='price'>¥<?php echo sanitize(number_format($viewData['price'])); ?>~</p>
</div>

</div>
</section>
</div>

<?php require('footer.php'); ?>

</html>