<?php

//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('「　トップページ　');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// ソート順
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

// 表示件数
$listSpan = 12;
// 現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan);
// DBから商品データを取得
$dbProductData = getProductList($currentMinNum, $category, $sort);
// DBからカテゴリデータを取得
$dbCategoryData = getCategory();

debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<html>
  <head>
    <title>商品一覧</title>
    <meta charset='utf8'>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

  <?php require('header.php'); ?>
  
    <!-- メインコンテンツ -->
    <div id="contents" class="site-width">

      <!-- サイドバー -->
      <section id="sidebar">
        <form name="" method="get">
          <h1 class="title">カテゴリー</h1>
          <div class="selectbox">
            <span class="icn_select"></span>
            <select name="c_id" id="">
              <option value="0" <?php if(getFormData('c_id',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
              <?php
                foreach($dbCategoryData as $key => $val):
              ?>
                <option value="<?php echo $val['category_id'] ?>" <?php if(getFormData('c_id',true) == $val['category_id'] ){ echo 'selected'; } ?> >
                  <?php echo $val['name']; ?>
                </option>
              <?php
                endforeach;
              ?>
            </select>
          </div>
          <h1 class="title">表示順</h1>
          <div class="selectbox">
            <span class="icn_select"></span>
            <select name="sort">
              <option value="0" <?php if(getFormData('sort',true) == 0 ){ echo 'selected'; } ?> >選択してください</option>
              <option value="1" <?php if(getFormData('sort',true) == 1 ){ echo 'selected'; } ?> >金額が安い順</option>
              <option value="2" <?php if(getFormData('sort',true) == 2 ){ echo 'selected'; } ?> >金額が高い順</option>
            </select>
          </div>
          <input type="submit" value="検索">
        </form>

      </section>

      <!-- Main -->
      <section id="main" >
        <div class="search-title">
          <div class="search-left">
            <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の商品が見つかりました
          </div>
          <div class="search-right">
            <span class="num"><?php echo (!empty($dbProductData['data'])) ? $currentMinNum+1 : 0; ?></span> - <span class="num"><?php echo $currentMinNum+count($dbProductData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
          </div>
        </div>
        <div class="panel-list">
        <?php
            foreach($dbProductData['data'] as $key => $val): //DB上にあるデータを画面上に自動的に生成する。
        ?>
          <!--ページ毎にリンクを生成する-->
            <a href="contentDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&p_id='.$val['id'] : '?p_id='.$val['id']; ?>" class="panel">
              <div class="panel-head">
                <img src="<?php echo showImg(sanitize($val['pic'])); ?>" alt="<?php echo sanitize($val['title']); ?>">
              </div>
              <div class="panel-body">
                <p class="panel-title"><?php echo sanitize($val['title']); ?> <span class="price">¥<?php echo sanitize(number_format($val['price'])); ?></span></p>
              </div>
            </a>
          <?php
            endforeach;
          ?>
        </div>

        <?php pagination($currentPageNum, $dbProductData['total_page']); ?>

      </section>

    </div>

    <!-- footer -->
    <?php
      require('footer.php');
    ?>
