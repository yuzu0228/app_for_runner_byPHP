<?php
//エラーログnoticeは非表示
error_reporting(E_ALL & ~E_NOTICE);

require('dbconnect.php');

session_start();

if($_COOKIE['name'] != '') {
    //クッキーによる自動ログイン処理
    $_POST['name'] = $_COOKIE['name'];
    $_POST['password'] = $_COOKIE['password'];
    $_POST['save'] = 'on';
}

if(!empty($_POST)) {
    if($_POST['name'] != '' && $_POST['password'] != '') {
        $login = $db -> prepare('SELECT * FROM members WHERE name=? AND password=?');
        $login -> execute([$_POST['name'], sha1($_POST['password'])]);
        $member = $login -> fetch();

        if($member) {
            //ログイン成功
            $_SESSION['id'] = $member['id'];
            $_SESSION['time'] = time();

            //ログイン情報を記録
            if($_POST['save'] == 'on') {
                setcookie('name', $_POST['name'], time()+60*60*24*14);
                setcookie('password', $_POST['password'], time()+60*60*24*14);
            }

            header('Location: index.php');
            exit();
        } else {
            $error['login'] = 'failed';
        }
    } else {
        $error['login'] = 'blank';
    }
}
?>

<?php require('header.php'); ?>

<body>
<div class="wrap login">
    <header>
        <i class="fas fa-running"></i><h1>ランニング管理アプリ</h1><i class="fas fa-running"></i>
    </header>
    <h2>ログイン画面</h2>
        <p>メースアドレスとパスワードを記入してログインしてください。</p>
        <p>メンバー登録がまだの方はこちらからどうぞ。</p>
        <p><a href="register.php"><i class="fas fa-arrow-circle-right"></i>登録手続きをする</a></p>
    <form action="" method="post">
        <dl>
            <dt>〇 ニックネーム</dt>
            <dd><input type="text" name="name" size="35" maxlengh="255" value="<?php echo htmlspecialchars($_POST['name'], ENT_QUOTES); ?>"></dd>
            <?php if($error['login'] == 'blank'): ?>
            <p class="error">* ニックネームとパスワードをご記入ください</p>
            <?php endif; ?>
            <?php if($error['login'] == 'failed'): ?>
            <p class="error">* ログインに失敗しました。正しくご記入ください</p>
            <?php endif; ?>
            <dt>〇 パスワード</dt>
            <dd><input type="password" name="password" size="35" maxlengh="255" value="<?php echo htmlspecialchars($_POST['password'], ENT_QUOTES); ?>"></dd>
            <dd><input id="save" type="checkbox" name="save" value="on"><label for="save">次回からは自動的にログインする</label></dd>
        </dl>
        <div><input type="submit" value="ログインする"></div>
    </form>
</div>
</body>
</html>
