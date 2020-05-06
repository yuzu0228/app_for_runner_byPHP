<?php
session_start();
require('dbconnect.php');

if(!isset($_SESSION['join'])) {
    header('Location: register.php');
    exit();
}

if(!empty($_POST)) {
    $statement = $db -> prepare('INSERT INTO members SET name=?, password=?, created=NOW()');
    $statement -> execute([
        $_SESSION['join']['name'], sha1($_SESSION['join']['password'])
    ]);
    unset($_SESSION['join']);

    header('Location: thanks.php');
    exit();
}
?>
<?php require('header.php'); ?>

<body>
<div id="wrap">
    <div id="head">
        <h1>メンバー登録</h1>
    </div>
    <div id="content">
        <p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
        <form action="" method="post">
        <input type="hidden" name="action" value="submit">
            <dl>
                <dt>ニックネーム</dt>
                <dd><?php echo htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES); ?></dd>
                <dt>パスワード</dt>
                <dd>【表示されません】</dd>
            </dl>
            <div><a href="register.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" value="登録する"></div>
        </form>
    </div>
</div>
</body>
</html>
