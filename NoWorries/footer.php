<footer>
  Copyright
</footer>

<script src='js/vendor/jquery-2.2.2.min.js'></script>
<script>
$(function(){

//テキストカウント
var $count= $('#js-count'),
    $count_view= $('#js-count-view');
$count.on('keyup', function(e){
  $count_view.html($(this).val().length);
});

//画像ライブプレビュー
var $dropArea= $('.area-drop');
var $fileInput= $('.input-file');
$dropArea.on('dragover', function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border', '3px #ccc dashed');
});
$dropArea.on('dragleave', function(e){
  e.stopPropagation();
  e.preventDefault();
  $(this).css('border', 'none');
});
$fileInput.on('change', function(e){
      $dropArea.css('border', 'none');
      var file = this.files[0],            // 2. files配列にファイルが入っています
          $img = $(this).siblings('.prev-img'), // 3. jQueryのsiblingsメソッドで兄弟のimgを取得
          fileReader = new FileReader();   // 4. ファイルを読み込むFileReaderオブジェクト

      // 5. 読み込みが完了した際のイベントハンドラ。imgのsrcにデータをセット
      fileReader.onload = function(event) {
        // 読み込んだデータをimgに設定
        $img.attr('src', event.target.result).show();
      };

      // 6. 画像読み込み
      fileReader.readAsDataURL(file);

    });


});
</script>