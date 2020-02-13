<?php

require('function.php');

debug('..............................');
debug('連絡掲示板');
debug('..............................');
debugLogStart();

//掲示板のGETパラメータを取得
$m_id= (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
//掲示板内のメッセージ情報を取得する。
$viewData= getMessage($m_id);
debug('取得したDBデータ' .print_r($viewData, true));

//パラメータに不正な値が入っていた場合の処理
if(empty($viewData)){
  error_log('エラー発生:　パラメータが改ざんされました。');
  header('Location:mypage.php');
}

//商品情報を取得する。
$productInfo = getProductOne($viewData[0]['product_id']);
debug('取得したDBデータ' .print_r($productInfo, true));

//商品情報がなかった場合
if(empty($productInfo)){
  error_log('エラー発生:商品情報が取得できませんでした。');
  header('Location:mypage.php');
}

//viewdataから相手のユーザーidを取り出す。
$dealUserId[]= $viewData[0]['sale_user'];
$dealUserId[]= $viewData[0]['buy_user'];
if(($key= array_search($_SESSION['user_id'], $dealUserId)) !=false){
  unset($dealUserId[$key]);
}

$partnerUserId= array_shift($dealUserId);
debug('取得した相手のユーザーID' .print_r($partnerUserId));

//相手のユーザー情報が取れたかどうか
if(isset($partnerUserId)){
$partnerUserInfo= getUser($partnerUserId);
}

//相手のユーザー情報が取れなかった場合
if(empty($partnerUserInfo)){
  debug('相手のユーザー情報が取得できませんでした。');
  header('Location:mypage.php');
}

//自分のユーザー情報を取得
$myUserInfo= getUser($_SESSION['user_id']);

if(empty($myUserInfo)){
  debug('自分のユーザー情報を取得できませんでした。');
  header('Location:mypage.php');
}

//POST送信されていた場合
if(!empty($_POST)){

  require('auth.php');

  $msg= (isset($_POST['msg']))? $_POST['msg'] : '';

  //バリデーション
  validMaxLen($msg, 'msg', 500);
  validRequired($msg, 'msg');

  if(empty($err_msg)){
    debug('バリデーションクリア');

  try{
    $dbh= dbConnect();
    $sql= 'INSERT INTO message (board_id, send_date, to_user, from_user, msg, create_date) VALUES (:id, :send_date, :to_user, :from_user, :msg, :create_date)';
    $data= array(':id' => $m_id, ':send_date' => date('Y-m-d H:i:s'), ':to_user' => $partnerUserId, ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':create_date' => date('Y-m-d H:m:s'));
    $stmt= queryPost($dbh, $sql, $data);

    if($stmt){
      $_POST= array();
      debug('連絡掲示板へ遷移します。');
      header('Location: ' . $_SERVER['PHP_SELF'] .'?m_id=' .$m_id);
    }

  }catch (Exception $e){
    error_log('エラー発生:' .$e->getMessage());
    $err_msg['common'] = MSG08;
  }
  }
}
debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面終了');
?>

<html lang='ja'>
  <head>
    <title>連絡掲示板</title>
    <meta charset='utf8'>
    <link rel="stylesheet" type="text/css" href="style.css">
  </head>

<style>
.msg-info{
        background: #f6f5f4;
        padding: 15px;
        overflow: hidden;
        margin-bottom: 15px;
      }
      .msg-info .avatar{
        width: 80px;
        height: 80px;
        border-radius: 40px;
      }
      .msg-info .avatar-img{
        text-align: center;
        width: 100px;
        float: left;
      }
      .msg-info .avatar-info{
        float: left;
        padding-left: 15px;
        width: 500px;
      }
      .msg-info .product-info{
        float: left;
        padding-left: 15px;
        width: 315px;
      }
      .msg-info .product-info .left,
      .msg-info .product-info .right{
        float: left;
      }
      .msg-info .product-info .right{
        padding-left: 15px;
      }
      .msg-info .product-info .price{
        display: inline-block;
      }
      .area-bord{
        height: 500px;
        overflow-y: scroll;
        background: #f6f5f4;
        padding: 15px;
      }
      .area-send-msg{
        background: #f6f5f4;
        padding: 15px;
        overflow: hidden;
      }
      .area-send-msg textarea{
        width:100%;
        background: white;
        height: 100px;
        padding: 15px;
      }
      .area-send-msg .btn-send{
        width: 150px;
        float: right;
        margin-top: 0;
      }
      .area-bord .msg-cnt{
        width: 80%;
        overflow: hidden;
        margin-bottom: 30px;
      }
      .area-bord .msg-cnt .avatar{
        width: 5.2%;
        overflow: hidden;
        float: left;
      }
      .area-bord .msg-cnt .avatar img{
        width: 40px;
        height: 40px;
        border-radius: 20px;
        float: left;
      }
      .area-bord .msg-cnt .msg-inrTxt{
        width: 85%;
        float: left;
        border-radius: 5px;
        padding: 10px;
        margin: 0 0 0 25px;
        position: relative;
      }
      .area-bord .msg-cnt.msg-left .msg-inrTxt{
        background: #f6e2df;
      }
      .area-bord .msg-cnt.msg-left .msg-inrTxt > .triangle{
        position: absolute;
        left: -20px;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-right: 15px solid #f6e2df;
        border-left: 10px solid transparent;
        border-bottom: 10px solid transparent;
      }
      .area-bord .msg-cnt.msg-right{
        float: right;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt{
        background: #d2eaf0;
        margin: 0 25px 0 0;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt > .triangle{
        position: absolute;
        right: -20px;
        width: 0;
        height: 0;
        border-top: 10px solid transparent;
        border-left: 15px solid #d2eaf0;
        border-right: 10px solid transparent;
        border-bottom: 10px solid transparent;
      }
      .area-bord .msg-cnt.msg-right .msg-inrTxt{
        float: right;
      }
      .area-bord .msg-cnt.msg-right .avatar{
        float: right;
      }
    </style>

    <!-- メニュー -->
    <?php
      require('header.php'); 
    ?>


    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      <!-- Main -->
      <section id="main" >
        <div class="msg-info">
          <div class="avatar-img">
            <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])); ?>" alt="" class="avatar"><br>
          </div>
          <div class="avatar-info">
            名前：<?php echo sanitize($partnerUserInfo['username']); ?> </br>
            年齢：<?php echo sanitize($partnerUserInfo['age']).'歳' ?> </br>
            職業：<?php echo sanitize($partnerUserInfo['job']); ?> </br>
            座右の銘：<?php echo sanitize($partnerUserInfo['favorite_words']); ?>
          </div>
          <div class="product-info">

            <div class="right">
              カテゴリ：<?php echo sanitize($productInfo['name']); ?><br>
              希望食事代：<span class="price">¥<?php echo number_format(sanitize($productInfo['price'])); ?></span><br>
              打ち合わせ日：<?php echo date('Y/m/d', strtotime(sanitize($viewData[0]['create_date']))); ?>
            </div>
          </div>
        </div>
        <div class="area-bord" id="js-scroll-bottom">
         <?php
            if(!empty($viewData[0]['msg'])){
              foreach($viewData as $key => $val){
                  if(!empty($val['from_user']) && $val['from_user'] == $partnerUserId){
            ?>
                    <div class="msg-cnt msg-left">
                      <div class="avatar">
                        <img src="<?php echo sanitize(showImg($partnerUserInfo['pic'])); ?>" alt="" class="avatar">
                      </div>
                      <p class="msg-inrTxt">
                        <span class="triangle"></span>
                        <?php echo sanitize($val['msg']); ?>
                      </p>
                      <div style="font-size:.5em;"><?php echo sanitize($val['send_date']); ?></div>
                    </div>
            <?php
                  }else{
            ?>
                    <div class="msg-cnt msg-right">
                      <div class="avatar">
                        <img src="<?php echo sanitize(showImg($myUserInfo['pic'])); ?>" alt="" class="avatar">
                      </div>
                      <p class="msg-inrTxt">
                        <span class="triangle"></span>
                        <?php echo sanitize($val['msg']); ?>
                      </p>
                      <div style="font-size:.5em;text-align:right;"><?php echo sanitize($val['send_date']); ?></div>
                    </div>
            <?php
                  }
                }
              }else{
            ?>
                <p style="text-align:center;line-height:20;">メッセージ投稿はまだありません</p>
            <?php
              }
          ?>
          
        </div>
        <div class="area-send-msg">
          <form action="" method="post">
            <textarea name="msg" cols="30" rows="5" placeholder='一緒にご飯を食べる約束を取り付けてみよう！'></textarea>
            <input type="submit" value="送信" class="btn btn-send">
          </form>
        </div>
      </section>
      
      <script src="js/vendor/jquery-2.2.2.min.js"></script>
      
      <script>
        $(function(){
          //scrollHeightは要素のスクロールビューの高さを取得するもの
          $('#js-scroll-bottom').animate({scrollTop: $('#js-scroll-bottom')[0].scrollHeight}, 'fast');
        });
      </script>

    </div>

    <!-- footer -->
    <?php
      require('footer.php'); 
    ?>
