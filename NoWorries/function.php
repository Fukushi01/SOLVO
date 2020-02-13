<?php

//ログを出力する
ini_set('log_errors', 'On');
ini_set('error_log', 'php.log');

//デバッグフラグ
$debug_flg= true;
//デバック関数
function debug($str){
  global $debug_flg;
    if(!empty($debug_flg)){
      error_log('デバッグ情報:' .$str);
    }
}

//セッション有効期限を伸ばす

session_save_path('/var/tmp/');

ini_set('session.gc_maxlifetime', 60*60*24*30);

ini_set('session.cookie_lifetime', 60*60*24*30);

session_start();

session_regenerate_id();

//画面表示処理開始関数
function debugLogStart(){
  debug('画面表示処理開始　<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
  debug('セッションID:' .session_id());
  debug('セッション変数の中身:' .print_r($_SESSION, true));
  debug('現在日時タイムスタンプ:' .time());
  if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
    debug('ログイン期限日時タイムスタンプ:' .( $_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

//エラーメッセージを定数に設定する
define('MSG01' , '入力必須です。');
define('MSG02' , 'Emailの形式で入力してください');
define('MSG03' , 'パスワード(再入力)があっていません。');
define('MSG04' , '半角英数字のみご利用いただけます。');
define('MSG05' , '６文字以上で入力してください。');
define('MSG06' , '256文字以内で入力してください');
define('MSG07' , '既に同じemailが登録されています。');
define('MSG08', 'エラーが発生しました。しばらく経ってからやり直してください。');
define('MSG09', 'パスワードまたはemailが違います。');
define('MSG10', '古いパスワードが違います。');
define('MSG11', '古いパスワードと同じです。');
define('MSG12', '半角数字のみご利用頂けます。');
define('MSG13', 'カテゴリを選択してください。');


//エラーメッセージ用の変数を用意
$err_msg = array();

//バリデーション関数（未入力チェック）
function validRequired($str, $key){
  if($str == ''){
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}

//バリデーション関数（email形式チェック）
function validEmail($str, $key){
  if(!preg_match('/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/', $str)){
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}

//バリデーション関数（再入力チェック）
function validMatch($str1, $str2,  $key){
  if($str1 !== $str2){
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}

//バリデーション関数（半角チェック）
function validHalf($str, $key){
  if(!preg_match('/^[a-zA-Z0-9]+$/', $str)){
    $err_msg[$key] = MSG04;
  }
}

//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6){
  if(mb_strlen($str) < $min){
    global $err_msg;
    $err_msg [$key] = MSG05;
  }
}

//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256){
  if(mb_strlen($str) > 256){
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}

//半角チェック
function validNumber($str, $key){
  if(!preg_match('/^[0-9]+$/', $str)){
    global $err_msg;
    $err_msg[$key]= MSG12;
  }
}

//チェックボックス
function validSelect($str, $key){
  if(!preg_match("/^[1-9]+$/", $str)){
    global $err_msg;
    $err_msg[$key] = MSG13;
  }
}
//パスワード形式関数
function validPass($str, $key){
  validHalf($str, $key);
  validMaxLen($str, $key);
  validMinLen($str, $key);
}

//バリデーション関数（email重複チェック）
function validEmailDup($email){
    global $err_msg;
    //例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg=0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //データを取得する
    $result = $stmt ->fetch(PDO::FETCH_ASSOC);
    var_dump($result);
    //レコード検索に引っかかった場合（つまり入力されたemailが既にDBにあった場合）
    if(!empty($result['count(*)'])){
      $err_msg['email'] = MSG07;
    }
  }catch(Exception $e){
    error_log('エラー情報:' .$e->getMessage());
    $err_msg['common'] = MSG08;
  }
}

//DB接続関数
function dbConnect(){
    $dsn = 'mysql:dbname=Noworries;host=localhost;charset=utf8';
    $username= 'root';
    $password= 'root';
    $option= array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
    $dbh = new PDO($dsn, $username, $password, $option);
    return $dbh;
  }

//SQL実行関数
function queryPost($dbh, $sql, $data){
  $stmt = $dbh->prepare($sql);
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL:' .print_r($stmt, true));
    $err_msg['common'] = MSG07;
    return 0;

  }else{
    debug('クエリ成功。');
    return $stmt;
  }
}

//サニタイズ関数
function sanitize($str){
  return htmlspecialchars($str,ENT_QUOTES);
}
// フォーム入力保持
function getFormData($str, $flg = false){
  if($flg){
    $method = $_GET;
  }else{
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if(!empty($dbFormData)){
    //フォームのエラーがある場合
    if(!empty($err_msg[$str])){
      //POSTにデータがある場合
      if(isset($method[$str])){
        return sanitize($method[$str]);
      }else{
        //ない場合（基本ありえない）はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    }else{
      //POSTにデータがあり、DBの情報と違う場合
      if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
        return sanitize($method[$str]);
      }else{
        return sanitize($dbFormData[$str]);
      }
    }
  }else{
    if(isset($method[$str])){
      return sanitize($method[$str]);
    }
  }
}

function getUser($u_id){
  debug('ユーザー情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'SELECT * FROM users  WHERE id = :u_id';
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if($stmt){
      debug('ユーザー情報を取得できました。');
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      debug('ユーザー情報を取得できませんでした。');
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

//メール送信関数
function sendMail($from, $to, $subject, $comment){
  if(!empty($to) && !empty($subject) && !empty($comment)){
    mb_language('Japanese');
    mb_internal_encoding('UTF-8');

    //メール送信
    $result= mb_send_mail($from, $to, $subject, $comment);

    if(!empty($result)){
      debug('メール送信に成功しました');
    }else{
      debug('メール送信に失敗しました');
    }
  }
}

//認証キー関数
function makeRandKey($length=8){
  $words='abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str='';
  for($i = 0; $i<$length; $i++){
    $str .= $words[mt_rand(0,61)];
  }
  return $str;
}

//ログイン認証
function isLogin(){
  if(!empty($_SESSION['user_id'])){
    debug('ログイン済みです。');
    return true;

  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限外です。');
    session_destroy();
    return false;
  }else{
    debug('ログイン有効期限内です。');
    return true;
  }
  }else{
    debug('未ログインユーザーです。');
    return false;
  }
}

//カテゴリーデータ取得関数
function getCategory(){
  debug('カテゴリデータを取得します。');
try{
  $dbh = dbConnect();
  $sql = 'SELECT * FROM category';
  $data = array();
  $stmt = queryPost($dbh, $sql, $data);
  if($stmt){
    debug('クエリ成功しました');
    return $stmt->fetchAll();
  }else{
    debug('クエリ失敗しました。');
    return false;
  }
}catch(Exception $e){
  error_log('エラー発生:' .$e->getMessage());
}
}

//商品情報取得関数（商品登録編集時）
function getProduct($user_id, $p_id){
  debug('商品情報を取得します。');

  try{
    $dbh= dbConnect();
    $sql= 'SELECT * FROM product WHERE user_id= :user_id AND id= :p_id';
    $data= array(':user_id' => $user_id, ':p_id' => $p_id);
    $stmt= queryPost($dbh, $sql, $data);

    if($stmt){
      debug('クエリ成功しました。');
      return $stmt->fetchAll();
    }else{
      debug('クエリ失敗しました。');
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生:' .$e->getMessage());
  }
}

//商品情報取得関数（商品詳細時）
function getProductOne($p_id){
  try{
    $dbh= dbConnect();
    $sql= 'SELECT p.id, p.title, p.content, p.price, p.pic, p.user_id, p.create_date, p.update_date, c.name FROM product AS p LEFT JOIN category AS c ON p.category_id = c.category_id WHERE p.id =:p_id AND p.delete_flg=0 AND c.delete_flg=0';
    $data= array(':p_id' => $p_id);
    $stmt= queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生:' .$e->getMessage());
  }
}

//メッセージ取得関数
function getMessage($id){
  debug('message情報を取得します');
  debug('掲示板ID' .$id);

  try{
    $dbh= dbConnect();
    $sql = 'SELECT m.id AS m_id, product_id, board_id, send_date, to_user, from_user, sale_user, buy_user, msg, b.create_date FROM message AS m RIGHT JOIN board AS b ON b.id = m.board_id WHERE b.id = :id ORDER BY send_date ASC';
    $data= array(':id' => $id);
    $stmt= queryPost($dbh, $sql, $data);

    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生' .$e->getMessage());
  }
}

//商品情報リスト取得関数
function getProductList($currentMinNum, $category, $sort, $span = 12){
  debug('商品情報を取得します。');
  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // 件数用のSQL文作成 商品idの数を把握することで総レコード数を算出することができる。
    $sql = 'SELECT id FROM product';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total']/$span); //総ページ数 ceilを用いることで端数を切り捨てることができる。
    
    if(!$stmt){
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM product';
    if(!empty($category)) $sql .= ' WHERE category_id = '.$category;
    if(!empty($sort)){
      switch($sort){
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
    $data = array();
    debug('SQL：'.$sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      // クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    }else{
      return false;
    }

  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
//ページネーション関数
//現在のページ数　$currentPageNum
//総ページ数　$totalPage
//ページネーション基準値 $pageNum
//検索用パラメータ $link
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum > $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }else{
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  }

  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1'.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum && $maxPageNum > 1){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}
//GETパラメータ付与
function appendGetParam($arr_del_key = array()){
  if(!empty($_GET)){
    $str= '?';
    foreach($_GET as $key => $val){
      if(!in_array($key, $arr_del_key, true)){
        $str .= $key .'='.$val.'&';
      }
    }
    $str = mb_substr($str, 0, -1, 'UTF-8');
    return $str;
  }
}

//画像処理関数
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報' .print_r($file, true));

  if(isset($file['error']) && is_int($file['error'])){
    try{

      switch($file['error']){
        case UPLOAD_ERR_OK:
         break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません。');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます。');
        default:
          throw new RuntimeException('その他のエラーが発生しました。');
      }

      $type= @exif_imagetype($file['tmp_name']);
      if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)){
        throw new RuntimeException('画像形式が未対応です。');
      }

      $path='uploads/' .sha1_file($file['tmp_name']).image_type_to_extension($type);
      if(!move_uploaded_file($file['tmp_name'], $path)){
        throw new RuntimeException('ファイル保持時にエラーが発生しました。');
      }

      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました。');

      return $path;

    }catch (RuntimeException $e){
      error_log('エラー発生' .$e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

//画像表示用関数
function showImg($pass){
  if(empty($pass)){
    return 'img/sample-img.png';
  }else{
    return $pass;
  }
}

//自分の商品情報を取得する。
function getMyProduct($user_id){
debug('自分の商品情報を取得します。');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM product WHERE user_id= :id AND delete_flg=0';
    $data= array(':id' => $user_id);
    $stmt= queryPost($dbh, $sql, $data);

    if($stmt){
      debug('商品情報が取得できました。');
      return $stmt->fetchAll();
    }else{
      debug('商品情報が取得できませんでした。');
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生' .$e->getMessage());
  }
}

//自分の連絡掲示板情報を取得する
function getMyMessage($user_id){
debug('自分のメッセージ情報を取得します。');
try{
  $dbh= dbConnect();
  $sql= 'SELECT * FROM board AS b WHERE b.sale_user= :id OR b.buy_user = :id AND b.delete_flg= 0';
  $data= array(':id' => $user_id);

  $stmt= queryPost($dbh, $sql, $data);
  $rst= $stmt->fetchAll();

  if(!empty($rst)){
    foreach($rst as $key => $val){
      $sql= 'SELECT * FROM message WHERE board_id= :id AND delete_flg=0 ORDER BY send_date DESC';
      $data= array(':id' => $val['id']);
      $stmt= queryPost($dbh, $sql, $data);
      $rst[$key]['msg']= $stmt->fetchAll();
    }
  }

  if($stmt){
    return $rst;
  }else{
    return false;
  }
}catch(Exception $e){
  error_log('エラー発生' .$e->getMessage());
}
}