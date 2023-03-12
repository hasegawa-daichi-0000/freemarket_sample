<?php

// 共通関数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード変更ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

// ログイン認証はなし（ログインできない人が使う画面なので）

// =================================================================
// 画面処理
// =================================================================

// post送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));

  // 変数にPOST情報を代入
  $email = $_POST['email'];

  // 未入力チェック
  validRequired($email, 'email');

  if (empty($err_msg)) {
    debug('未入力チェックOK。');

    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMaxLen($email, 'email');

    if (empty($err_msg)) {
      // 例外処理
      try {
        // DBへ接続
        $dbh = dbConnect();
        // SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $email);
        // クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // EmailがDBに登録されている場合
        if ($result && array_shift($result)) {
          debug('クエリ成功。DB登録あり');
          $_SESSION['msg_success'] = SUC03;

          $auth_key = makeRandKey(); // 認証キー生成

          // メールを送信
          $form = 'info@webukatu.com';
          $to = $email;
          $subject = '【パスワード再発行認証】|WEBUKATUMAREKET';
          // EOTはEndOfFileの略。
          // EOT内の半角空白も全てそのまま半角空白として扱われるのでインデントはしないこと
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力いただくパスワードの再発行されます。

パスワード再発行認証キー入力ページ: http://localhost:8888/webservice_practice01/passRemindRecieve.php
認証キー: {$auth_key}
※認証キーの有効期限は30分となります。

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/webservice_practice01/passRemindSend.php

////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL  http://webukatu.com/
E-mail info@webukatu.com
////////////////////////////////////////
EOT;
          sendMail($form, $to, $subject, $comment);

          // 認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          $_SESSION['auth_key_limit'] = time() + (60 + 30); // 現在時刻より30分後のUNIXタイムスタンプを入れる
          debug('セッション変数の中身：' . print_r($_SESSION, true));

          header("Location:passRemindRecieve.php"); // 認証キー入力ページへ

        } else {
          debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
          $err_msg['common'] = MSG07;
        }
      } catch (Exception $e) {
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
      }
    }
  }
}

?>

<?php
$siteTitle = 'パスワード再発行メール送信';
require('head.php');
?>

<body class="page-signup page-1colum">

  <!-- メニュー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div id="contents" class="site-width">

    <!-- Main -->
    <section id="main">

      <div class="form-container">

        <form action="" method="post" class="form">
          <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
          <div class="area-msg">
            <?php
            echo getErrMsg('common');
            ?>
          </div>
          <label class="<?php if (!empty($err_msg['email'])) echo 'err'; ?>">
            Email
            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
          </label>
          <div class="area-msg">
            <?php
            echo getErrMsg('email');
            ?>
          </div>
          <div class="btn-container">
            <input type="submit" class="btn btn-mid" value="送信する">
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