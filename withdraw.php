<?php
//共通関数・関数ファイルの読み込み
require('function.php');
require('Class/Auth/Withdraw.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('退会ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

////////////////////////////////
// 画面処理
////////////////////////////////
//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  $userId = $_SESSION['user_id'];
  $deleteUser = new Withdraw($userId);
  $deleteUser->withDraw($userId);
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>
<?php
$siteTitle = '退会';
require('head.php')
?>

<body class="page-withdraw page-1colum">
  <style>
    .form .btn {
      float: none;
    }

    .form {
      text-align: center;
    }
  </style>

  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">
    <!-- Main -->
    <section id="main">
      <div class="form-container">
        <form action="" method="post" class="form">
          <h2 class="title">退会</h2>
          <div class="area-msg">
            <?php
            if (!empty($err_msg['common'])) echo $err_msg['common'];
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="退会する" name=submit>
          </div>
        </form>
      </div>
      <a href="mypage.php">&lt; マイページに戻る</a>
    </section>
  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>