<?php
//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証はなし（ログインできない人が使う画面なので）

//SESSIONに認証キーがあるか確認、なければリダイレクト
if (empty($_SESSION['auth_key'])) {
  header("Location:passRemindSend.php"); //認証キー送信ページへ
}

// =================================
// 画面処理
// =================================
//post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  //変数に認証キーを代入
  $auth_key = $_POST['token'];

  //未入力チェック
  validRequired($auth_key, 'token');

  if (empty($err_msg)) {
    //固定長チェック
    validLength($auth_key, 'token');
    //半角チェック
    validHalf($auth_key, 'token');

    if (empty($err_msg)) {
      debug('バリデーションOK。');

      if ($auth_key !== $_SESSION['auth_key']) {
        $err_msg['common'] = MSG15;
      }
      if (time() > $_SESSION['auth_key_limit']) {
        $err_msg['common'] = MSG16;
      }

      if (empty($err_msg)) {
        debug('認証OK');

        $pass = makeRandKey(); //パスワード生成

        //例外処理
        try {
          //DBへ接続
          $dbh = dbConnect();
          //SQL文作成
          $sql = "UPDATE users SET pass = :pass WHERE email = :email AND delete_flg = 0";
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          //クエリ実行
          $stmt = queryPost($dbh, $sql, $data);

          //クエリ成功の場合
          if ($stmt) {
            debug('クエリ成功。');

            //メールを送信
            $form = 'info@webkatu.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】| WEBKATUMARKET';
            //EOTでメール文を作成
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://localhost:8888/webservice_practice01/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
            sendMail($form, $to, $subject, $comment);

            //セッション削除
            session_unset();
            $_SESSION['msg_succeed'] = SUC03;
            debug('セッション変数の中身：' . print_r($_SESSION, true));

            header("Location:login.php"); //ログインページへ  
          }
        } catch (Exception $e) {
          error_log('エラー発生:' . $e->getMessage());
          $err_msg['common'] = MSG07;
        }
      }
    }
  }
}

?>

<?php
$siteTitle = 'パスワード再発行';
require('head.php');
?>

<body class="page-signup page-1colum">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <p id="js-show-msg" style="display: none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">

      <div class="form-container">

        <form action="" method="post" class="form">
          <p>ご指定のメールアドレスお送りした【パスワード再発行認証メール】内にある「認証キー」をご入力ください。</p>
          <div class="area-msg">
            <?php echo getErrMsg('common'); ?>
          </div>
          <label class="<?php if (!empty($err_msg['token'])) echo 'err'; ?>">
            認証キー
            <input type="text" name="token" value="<?php echo getFormData('token') ?>">
          </label>
          <div class="area-msg">
            <?php echo getErrMsg('token'); ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="再発行する">
          </div>
        </form>
      </div>
      <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
    </section>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>