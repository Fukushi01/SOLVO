<?php
require('function.php');
debug('.............................................');
debug('マイページ');
debug('.............................................');
debugLogStart();

require('auth.php');

$user_id = $_SESSION['user_id'];
//プロダクト情報を取得する。
$productData = getMyProduct($user_id);
//連絡掲示板データを取得する。
$boardData= getMyMessage($user_id);
//気になるデータを取得する


//取得した商品データを取得する。
debug('商品データ' .print_r($productData, true));
debug('連絡掲示板データ' .print_r($boardData, true));

debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理終了');

?>

<!--画面部分-->
<html lang='ja'>
  <head>
    <title>マイページ</title>
    <meta charset='utf-8'>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body class="page-mypage page-2colum">
    <style>
      #main{
        border: none !important;
      }
    </style>
    
    <!-- メニュー -->
    <?php require('header.php'); ?>

    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">
      
      <h1 class="page-title">MYPAGE</h1>

      <!--サイドバー-->
      <?php require('sidebar-mypage.php');?>
      
      <!-- Main -->
      <section id="main" >
         <section class="list panel-list">
           <h2 class="title" style="margin-bottom:15px;">
            登録商品一覧
           </h2>
           <?php
             if(!empty($productData)):
              foreach($productData as $key => $val):
            ?>
              <a href="registConsul.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
                <div class="panel-head">
                  <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['title']); ?>">
                </div>
                <div class="panel-body">
                  <p class="panel-title"><?php echo sanitize($val['title']); ?> <span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
                </div>
              </a>
            <?php
              endforeach;
             endif;
            ?>
         </section>
         
         <style>
           .list{
             margin-bottom: 30px;
           }
        </style>
         
        <section class="list list-table">
          <h2 class="title">
            連絡掲示板一覧
          </h2>
          <table class="table">
            <thead>
              <tr>
                <th>最新送信日時</th>
                <th>取引相手</th>
                <th>メッセージ</th>
              </tr>
            </thead>
            <tbody>
             <?php
              if(!empty($boardData)){
                foreach($boardData as $key => $val){
                  if(!empty($val['msg'])){
                    $msg = array_shift($val['msg']);
             ?>
                 <tr>
                    <td><?php echo sanitize(date('Y.m.d H:i:s',strtotime($msg['send_date']))); ?></td>
                    <td>◯◯ ◯◯</td>
                    <td><a href="chat.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']),0,40); ?>...</a></td>
                </tr>
             <?php
                  }else{
             ?>
                 <tr>
                    <td>--</td>
                    <td>◯◯ ◯◯</td>
                    <td><a href="chat.php?m_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
                </tr>
              <?php
                  }
                }
              }
            ?>
            </tbody>
          </table>
        </section>

            </section>
    </div>

    <!-- footer -->
    <?php
      require('footer.php'); 
    ?>