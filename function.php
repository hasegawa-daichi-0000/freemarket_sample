<?php
////////////////////////////////
// ログ
////////////////////////////////
//ログを取るか
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');

////////////////////////////////
// デバック
////////////////////////////////
// デバックログフラグ
$debug_flg = true;
// デバックログ関数
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバック：' . $str);
  }
}

////////////////////////////////////////////////////////////////
// セッションファイルの置き場を変更する（/var/tmp/以下におくと30日間は削除されない）
session_save_path("/var/tmp");
// ガーページコレクションが削除するセッションの有効期限を設定（30日以上経っているものに対してだけ100分の1の確率で削除）
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
// ブラウザを閉じても削除されないようにクッキーの有効期限を伸ばす
ini_set("session.cookie_lifetime", 60 * 60 * 24 * 30);
// セッションを使う
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

////////////////////////////////////////////////
// 画面表示処理開始ログ吐き出し関数
////////////////////////////////////////////////
function debugLogStart()
{
  debug('>>>>>>>>>>>>>>>>>>>>>> 画面表示処理開始');
  debug('セッションID:' . session_id());
  debug('セッション変数の中身:' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ:' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ:' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}


////////////////////////////////////////////////
// 定数
////////////////////////////////////////////////
//メッセージを定数に設定
define('MSG01', '入力必須です');
define('MSG02', 'Emailの形式で入力してください');
define('MSG03', 'パスワード（再入力）が合っていません');
define('MSG04', '半角英数字のみご利用いただけます');
define('MSG05', '6文字以上で入力してください');
define('MSG06', '256文字以内で入力してください');
define('MSG07', 'エラーが発生しました。しばらく経ってからやり直しください');
define('MSG08', 'そのEmailは既に登録されています');
define('MSG09', 'メールアドレスまたはパスワードが違います');
define('MSG10', '電話番号の形式が違います');
define('MSG11', '郵便番号の形式が違います');
define('MSG12', '古いパスワードが違います');
define('MSG13', '古いパスワードと同じです');
define('MSG14', '文字で入力してください');
define('MSG15', '正しくありません');
define('MSG16', '有効期限が切れています');
define('MSG17', '半角数字のみご利用いただけます');
define('SUC01', 'パスワードを変更しました');
define('SUC02', 'プロフィールを変更しました');
define('SUC03', 'メールを送信しました');
define('SUC04', '登録しました');
define('SCU05', '購入しました！相手と連絡を取りましょう！');


// ----------------------------------------------------------------
//エラーメッセージ格納用の配列
$err_msg = array();
// ----------------------------------------------------------------

////////////////////////////////////////////////
// バリデーション関数
////////////////////////////////////////////////
//バリデーション関数（未入力チェック）
function validRequired($str, $key)
{
  if ($str === '') { //金額フォームなどを考えると数値の0はOKにし、空文字はダメにする
    global $err_msg;
    $err_msg[$key] = MSG01;
  }
}
//バリデーション関数（Email形式チェック）
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG02;
  }
}
function validEmailDup($email)
{
  global $err_msg;
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
    $data = array(':email' => $email);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //クエリ結果の値を取得
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!empty($result['count(*)'])) {
      $err_msg['email'] = MSG08;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = MSG07;
  }
}
//バリデーション関数（同値チェック）
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = MSG03;
  }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str, $key, $min = 6)
{
  if (mb_strlen($str) < $min) {
    global $err_msg;
    $err_msg[$key] = MSG05;
  }
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str, $key, $max = 256)
{
  if (mb_strlen($str) > $max) {
    global $err_msg;
    $err_msg[$key] = MSG06;
  }
}
//バリデーション関数（半角チェック）
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG04;
  }
}
//バリデーション関数（電話番号形式チェック）
function validTel($str, $key)
{
  if (!preg_match("/0\d{1,4}\d{1,4}\d{4}/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG10;
  }
}
//バリデーション関数（郵便番号形式チェック）
function validZip($str, $key)
{
  if (!preg_match("/^\d{3}\d{4}$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG11;
  }
}
//バリデーション関数（半角数字チェック）
function validNumber($str, $key)
{
  if (!preg_match("/^[0-9]*$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG17;
  }
}
//固定長チェック
function validLength($str, $key, $len = 8)
{
  if (mb_strlen($str) !== $len) {
    global $err_msg;
    $err_msg[$key] = $len . MSG14;
  }
}
//パスワードチェック
function validPass($str, $key)
{
  //半角英数字チェック
  validHalf($str, $key);
  //最大文字数チェック
  validMaxLen($str, $key);
  //最小文字数チェック
  validMinLen($str, $key);
}
//SelectBoxチェック
function validSelect($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = MSG15;
  }
}
//エラーメッセージ表示
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}

// =============================
// ログイン認証
// =============================
function isLogin()
{
  //ログインしている場合
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');

    //現在日時が最終ログイン+有効期限を超えていた場合
    if ($_SESSION['login_date'] + $_SESSION['login_limit'] < time()) {
      debug('ログイン有効期限オーバーです。');

      //セッションを削除(ログアウトする)
      session_destroy();
      return false;
    } else {
      debug('ログイン有効期限内です。');
      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}

// ------------------------------
// データベース
// ------------------------------
//DB接続関数
function dbConnect()
{
  //DBへの接続準備
  $dsn = 'mysql:dbname=freemarket;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    // SQL実行失敗時に例外をフォロー
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う（一度に結果セットをすべて取得し、サーバー負荷を軽減）
    // SELECTで得た結果に対してもrowCountメソッドをつかるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  //PDOオブジェクト生成（DBへ接続）
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}
function queryPost($dbh, $sql, $data)
{
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダーに値をセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    $err_msg['common'] = MSG07;
    return 0;
  };
  debug('クエリ成功');
  return $stmt;
}
function getUser($u_id)
{
  debug('ユーザー情報を取得します。');
  // 例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = "SELECT * FROM users WHERE id = :u_id AND delete_flg = 0";
    $data = array(':u_id' => $u_id);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (PDOException $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
function getProduct($u_id, $p_id)
{
  debug('商品情報を取得します。');
  debug('ユーザーID：' . $u_id);
  debug('商品ID：' . $p_id);
  // 例外処理
  try {
    //DBh接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM products WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id, ':p_id' => $p_id);
    //クエリの実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}
function getProductList($currentMinNum = 1, $category, $sort, $span = 5)
{
  debug('商品情報を取得します');
  //例外処理
  try {
    //DB接続
    $dbh = dbConnect();
    //件数用のSQL文作成
    $sql = 'SELECT id FROM products';
    //もしカテゴリーが空じゃなかったら、categoryを検索条件に追加
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    //もしソートが空じゃなかったら、switch文に処理を流す
    if (!empty($sort)) {
      //渡ってきた値が1の場合は、昇順。
      //渡ってきた値が2の場合は、降順。
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst['total'] = $stmt->rowCount(); //総レコード数
    $rst['total_page'] = ceil($rst['total'] / $span); //総ページ数
    if (!$stmt) {
      return false;
    }

    //ペーじング用のSQL文作成
    $sql = 'SELECT * FROM products';
    //もしカテゴリーが空じゃなかったら（パラメータとして渡ってきたら）検索条件にカテゴリーを追加
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY price ASC';
          break;
        case 2:
          $sql .= ' ORDER BY price DESC';
          break;
      }
    }
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL:' . $sql);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果のデータを全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}
function getProductOne($p_id)
{
  debug('商品情報を取得します。');
  debug('商品ID:', $p_id);

  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT p.id, p.name, p.detail, p.price, p.pic1, p.pic2, p.pic3, p.user_id, p.create_date, p.update_date, c.name AS category 
    FROM products AS p LEFT JOIN category AS c ON p.category_id = c.id WHERE p.id = :p_id AND p.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果のデータを1レコード返却
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getMyProducts($u_id)
{
  debug('自分の商品情報を取得します。');
  debug('ユーザーID:', $u_id);

  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM products WHERE user_id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getMsgsAndBord($id)
{
  debug('msg情報を取得します。');
  debug('掲示板ID:', $id);
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT m.id AS m_id, product_id, bord_id, send_date, to_user, from_user, sale_user, buy_user, msg, b.create_date FROM message AS m RIGHT JOIN bord AS b ON b.id = m.bord_id WHERE b.id = :id AND m.delete_flg = 0 ORDER BY send_date ASC';
    $data = array(':id' => $id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getMyMsgAndBord($u_id)
{
  debug('自分のmsg情報を取得します。');

  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM bord AS b WHERE b.sale_user = :id OR b.buy_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if (!empty($rst)) {
      foreach ($rst as $key => $val) {
        // SQL文作成
        $sql = 'SELECT * FROM message WHERE bord_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
        $data = array(':id' => $val['id']);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }

    if ($stmt) {
      //クエリ結果の全データを返却
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getMyLike($u_id)
{
  debug('自分のお気に入り情報を取得します。');
  debug('ユーザーID：' . $u_id);

  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM `like` AS l LEFT JOIN products AS p ON l.product_id = p.id WHERE l.user_id = :u_id';
    $data = array(':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function getCategory()
{
  debug('カテゴリー情報を取得します');
  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM category';
    $data = array();
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      //クエリ結果の全データを返却
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

function isLike($u_id, $p_id)
{
  debug('お気に入り情報があるか確認します。');
  debug('ユーザーID:' . $u_id);
  debug('商品ID:' . $p_id);

  //例外処理
  try {
    //DBへ接続
    $dbh = dbConnect();
    //SQL文作成
    $sql = 'SELECT * FROM `like` WHERE product_id = :p_id AND user_id = :u_id ';
    $data = array(':p_id' => $p_id, ':u_id' => $u_id);
    //クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt->rowCount()) {
      debug('お気に入りです');
      return true;
    } else {
      debug('特に気に入ってません');
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}

// ----------------------------------------------------------------
//メール送信
// ----------------------------------------------------------------
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($to) && !empty($subject) && !empty($comment)) {
    //文字化けしないように設定（お決まりパターン）
    mb_language("Japanese"); //現在使っている言語を設定する
    mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械がわかる言葉へ変換）するか設定

    //メールを送信（送信結果はtrueかfalseで返ってくる）
    $result = mb_send_mail($to, $subject, $comment, "From: " . $from);
    //送信結果を判定
    if ($result) {
      debug('メールを送信しました');
    } else {
      debug('【エラー発生】メールの送信に失敗しました。');
    }
  }
}

// --------------------------------
// その他
// --------------------------------
// サニタイズ
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

// フォームの入力保持
function getFormData($str, $flg = false)
{
  //もしフラグがtrueだったら,$method変数に$_GETを代入
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }
  global $dbFormData;
  // ユーザーデータがある場合
  if (!empty($dbFormData)) {
    // フォームのエラーがある場合
    if (!empty($err_msg[$str])) {
      // POSTにデータがある場合
      if (isset($method[$str])) { // 金額や郵便番号などのフォームで数字や数値の0が入っている場合もあるので、issetを使うこと
        return sanitize($method[$str]);
      } else {
        // ない場合（フォームにエラーがある＝POSTされているはずなので、まずありえないが）はDBの情報を表示
        return $dbFormData[$str];
      }
    } else {
      // POSTにデータがあり、DBの情報と違う場合（このフォームも変更していてエラーはないが、他のフォームで引っかかっている状態）
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return $dbFormData[$str];
      }
    }
  } else {
    if (isset($method[$str])) {
      return $method[$str];
    }
  }
}
// sessionを一回だけ取得できる
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = '';
    return $data;
  }
}
// 認証キー生成
function makeRandKey($length = 8)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = '';
  for ($i = 0; $i < $length; ++$i) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}
// 画像処理
function uploadImg($file, $key)
{
  debug('画像アップロード');
  debug('FILE情報:' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      //バリデーション
      //$file['error']の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
        case UPLOAD_ERR_OK: //OK
          break;
        case UPLOAD_ERR_NO_FILE: //ファイル未選択の場合
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE: //php.ini定義の最大サイズが超過した場合
        case UPLOAD_ERR_FORM_SIZE: //フォーム定義の最大サイズ超過した場合
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default: //その他の場合
          throw new RuntimeException('その他のエラーが発生しました');
      }

      // $file['mine']の値はブラウザ側で偽装可能なので、MINEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGE_JPEG」などの定数を渡す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { //第三引数にはtrueをつけると厳密にチェックしてくれるので必ずつける
        throw new RuntimeException('画像形式が未対応です');
      }

      //ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      //ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      //DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      //image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);

      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      //保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：' .  $path);
      return $path;
    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}
//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5)
{
  //現在のページが、総ページと同じ　かつ　総ページ数が表示項目数以上なら、左にリンク4個出す
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
    //現在のページが、総ページ数の1ページ前なら、左にリンク3個、右に1個出す
  } elseif ($currentPageNum == ($totalPageNum - 1) && $currentPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
    //現ページが2の場合は左にリンク1個、右にリンク3個だす。
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
    //現ぺーじが1の場合は左に何にも出さない。右に5個出す。
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
    //総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを1に設定
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
    //それ以外は左に2個出す。
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }

  echo '<div class="pagination">';
  echo '<ul class="pagination-list">';
  if ($currentPageNum != 1) {
    echo '<li class="list-item"><a href="?p=1">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
    echo '<li class="list-item ';
    if ($currentPageNum == $i) {
      echo 'active';
    }
    echo '"><a href="?p=' . $i . $link . '">' . $i . '</a></li>';
  }
  if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
    echo '<li class="list-item"><a href="?p=' . $maxPageNum . $link . '">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
}
//画像表示用関数
function showImg($path)
{
  if (empty($path)) {
    return 'img/sample-img.png';
  } else {
    return $path;
  }
}
//GETパラメータ付与
function appendGetParam($arr_del_key = array())
{
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) { //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
