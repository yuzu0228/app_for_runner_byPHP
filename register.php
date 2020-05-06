<!--会員情報入力ページ-->
<?php
//エラーログnoticeは非表示
error_reporting(E_ALL & ~E_NOTICE);

require('dbconnect.php');

session_start();

if(!empty($_POST)) {
    //登録内容のバリデーション
    if($_POST['name'] == '') {
        $error['name'] = 'blank';
    }
    if(strlen($_POST['password']) < 4) {
        $error['password'] = 'length';
    }
    if($_POST['password'] == '') {
        $error['password'] = 'blank';
    }
    if(strlen($_POST['password2']) < 4) {
        $error['password2'] = 'length';
    }
    if($_POST['password2'] == '') {
        $error['password2'] = 'blank';
    }
    if($_POST['password'] !== $_POST['password2']) {
        $error['password'] = 'wrong';
    }

    //重複垢のcheck
    if(empty($error)) {
        $member = $db -> prepare('SELECT COUNT(*) AS cnt FROM members WHERE name=?');
        $member -> execute([$_POST['name']]);
        $record = $member -> fetch();
        if($record['cnt'] > 0) {
            $error['name'] = 'duplicate';
        }
    }

    if(empty($error)) {

        $_SESSION['join'] = $_POST;
        
        header('Location: check.php');
        exit();
    }
}

if($_REQUEST['action'] == 'rewrite') {
    $_POST = $_SESSION['join'];
    $error['rewrite'] = true;
}
?>

<?php require('header.php'); ?>

<body>
<div class="wrap register">
    <header>
        <i class="fas fa-running"></i><h1>ランニング管理アプリ</h1><i class="fas fa-running"></i>
    </header>

    <h2>メンバー登録画面</h2>

    <p>次のフォームに必要事項をご記入ください。</p>
    <form action="" method="post">
        <dl>
            <dt>〇 ニックネーム<span class="required">必須</span></dt>
            <dd>
                <?php if($error['name'] == 'blank'): ?>
                    <p class="error">* ニックネームを入力してください</p>
                <?php endif; ?>
                <?php if($error['name'] == 'duplicate'): ?>
                    <p class="error">* 指定されたニックネームはすでに登録されています</p>
                <?php endif; ?>
            <input type="text" name="name" size="35" maxlength="255" value="<?php if(!empty($_POST)) { echo htmlspecialchars($_POST['name'], ENT_QUOTES);} ?>">
            </dd>

            <dt>〇 パスワード<span class="required">必須</span></dt>
            <dd>
                <?php if($error['password'] == 'blank'): ?>
                    <p class="error">* パスワードを入力してください</p>
                <?php endif; ?>
                <?php if($error['password'] == 'length'): ?>
                    <p class="error">* パスワードは4文字以上で入力してください</p>
                <?php endif; ?>
                <?php if($error['password'] == 'wrong'): ?>
                    <p class="error">* パスワードが一致しませんでした</p>
                <?php endif; ?>
            <input type="password" name="password" size="10" maxlength="20" value="<?php if(!empty($_POST)) { echo htmlspecialchars($_POST['password'], ENT_QUOTES);} ?>">
            </dd>

            <dt>〇 確認用パスワード<span class="required">必須</span></dt>
            <dd>
                <?php if($error['password2'] == 'blank'): ?>
                    <p class="error">* 確認用パスワードを入力してください</p>
                <?php endif; ?>
                <?php if($error['password2'] == 'length'): ?>
                    <p class="error">* 確認用パスワードは4文字以上で入力してください</p>
                <?php endif; ?>
            <input type="password" name="password2" size="10" maxlength="20" value="<?php if(!empty($_POST)) { echo htmlspecialchars($_POST['password2'], ENT_QUOTES);} ?>">
            </dd>

        </dl>
        <div><input type="submit" value="入力内容を確認する"></div>
    </form>

    <p>すでに登録されている方は<a href="login.php">こちらからログイン</a>してください</p>
</div>
</body>
</html>
