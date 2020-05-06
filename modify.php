<!--投稿画面-->
<?php
//エラーログnoticeは非表示

error_reporting(E_ALL & ~E_NOTICE);

session_start();

require('dbconnect.php');

if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
    //ログインしている
    $_SESSION['time'] = time();

    $members = $db -> prepare('SELECT * FROM members WHERE id=?');
    $members -> execute([$_SESSION['id']]);
    $member = $members -> fetch();
} else {
    //ろぐいんしてない場合;
    header('Location: login.php');
    exit();
}


//投稿をdbに記録
if(!empty($_POST)) {

    if($_POST['run_distance'] == '') {
        $error['run_distance'] = 'blank';
    }
    if($_POST['run_time_hour'] == '') {
        $error['run_time_hour'] = 'blank';
    }
    if($_POST['run_time_minute'] == '') {
        $error['run_time_minute'] = 'blank';
    }
    if($_POST['run_time_second'] == '') {
        $error['run_time_second'] = 'blank';
    }
    if($_POST['month'] == '') {
        $error['month'] = 'blank';
    }
    if($_POST['day'] == '') {
        $error['day'] = 'blank';
    }
    if($_POST['hour'] == '') {
        $error['hour'] = 'blank';
    }
    if(!is_numeric($_POST['month']) || !is_numeric($_POST['day']) || !is_numeric($_POST['hour']) 
    || !is_numeric($_POST['run_distance']) || !is_numeric($_POST['run_time_hour']) || !is_numeric($_POST['run_time_minute']) ||
     !is_numeric($_POST['run_time_second'])) {
        $error['digit'] = 'not-digit';
    }
    if(mb_strlen($_POST['memo']) >= 100) {
        $error['memo'] = 'over';
    }
    if($_POST['month'] > 12) {
        $error['number'] = 'month_wrong'; 
    }
    if($_POST['month'] == 4 || $_POST['month'] == 6 || $_POST['month'] == 9 || $_POST['month'] == 11) {
        if($_POST['day'] > 30) {
            $error['number'] = 'day_wrong'; 
        }
    }
    if($_POST['month'] == 2)
        if($_POST['day'] > 28) {
            $error['number'] = 'day_wrong'; 
    }
    if($_POST['month'] == 1 || $_POST['month'] == 3 || $_POST['month'] == 5 || $_POST['month'] == 7 || $_POST['month'] == 8 ||
    $_POST['month'] == 10 || $_POST['month'] == 12) {
        if($_POST['day'] > 31) {
            $error['number'] = 'day_wrong';
        }
    }
    if($_POST['hour'] > 24) {
        $error['number'] = 'hour_wrong'; 
    }
    if($_POST['run_time_minute'] > 60) {
        $error['number'] = 'minute_wrong'; 
    }
    if($_POST['run_time_second'] > 60) {
        $error['number'] = 'second_wrong'; 
    }

    if(empty($error)) {
        $total_run_time = ($_POST['run_time_hour'] * 60) + $_POST['run_time_minute'] + round(($_POST['run_time_second'] / 60),3);
        $velocityKmVer = round($total_run_time / $_POST['run_distance'], 2);$velocity = ($_POST['run_distance'] * 1000) / $total_run_time; // m / min 
        $VO2 = -4.6 + 0.182258 * $velocity + 0.000104 * ($velocity*$velocity);
        $VO2max = (0.8 + 0.1894393 * pow(2.71828, (-0.012788 * $total_run_time)) + 0.2989558 * pow(2.71828, (-0.1932605 * $total_run_time))) * 100;
        $VDOT = round(($VO2 / $VO2max) * 100);

        $update = $db -> prepare('UPDATE run_data SET distance=?, total_run_time=?,
        run_time_hour=?, run_time_minute=?, run_time_second=?, velocity=?, VDOT=?, memo=?, month=?, day=?, hour=?, modified=NOW() WHERE run_data.id=?');
        $update -> execute([$_POST['run_distance'], $total_run_time,
        $_POST['run_time_hour'],$_POST['run_time_minute'], $_POST['run_time_second'], $velocityKmVer, $VDOT, $_POST['memo'],
        $_POST['month'], $_POST['day'], $_POST['hour'], $_REQUEST['id']]);
        

        header('Location: index.php');
        exit();
    }
}

//htmlspecialcharsのショートカット関数定義
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES);
}

?>

<?php require('header.php'); ?>

<body>
<div class="wrap modify">
    <header>
        <i class="fas fa-running"></i><h1>ランニング管理アプリ</h1><i class="fas fa-running"></i>
    </header>
    
    <p><a href="index.php">メインメニューに戻る</a></p>
    <h2>ランニング内容を変更する</h2>
    
    <form action="" method="post">
        <ul>
            <?php date_default_timezone_set('Asia/Tokyo'); ?>
            <?php if($error['month'] == 'blank' || $error['day'] == 'blank' || $error['hour'] == 'blank'): ?>
                <li class="error">時間が未入力でした</li>
            <?php endif; ?>
            <?php if($error['digit'] == 'not-digit'): ?>
                <li class="error">メモ以外には数字のみを入力してください</li>
            <?php endif; ?>
            <?php if($error['number'] == 'month_wrong'): ?>
                <li class="error">12以下の数値を入力してください</li>
            <?php endif; ?>
            <li><i class="far fa-hand-point-right"></i>走った日付を入力してください</li>
            <li class="form-parts"><span><input type="text" name="month" value="<?php $timeStamp=time(); echo h(date('n', $timeStamp)); ?>">月</span>
            <?php if($error['number'] == 'day_wrong'): ?>
                <li class="error">月に対応した日付を入力してください</li>
            <?php endif; ?>
            <span><input type="text" name="day" value="<?php $timeStamp=time(); echo h(date('j', $timeStamp)); ?>">日</span>
            <?php if($error['number'] == 'hour_wrong'): ?>
                <li class="error">24以下の数値を入力してください</li>
            <?php endif; ?>
            <span><input type="text" name="hour" value="<?php $timeStamp=time(); echo h(date('g', $timeStamp)); ?>">時ごろ</span>
            </li>
            <li><i class="far fa-hand-point-right"></i>走った距離を入力してください</li>
            <?php if($error['run_distance'] == 'blank'): ?>
                <li class="error">距離が未入力でした</li>
            <?php endif; ?>
            <li class="form-parts"><input type="text" name="run_distance" value="<?php echo h($_POST['run_distance']); ?>">km</li>
            <li><i class="far fa-hand-point-right"></i>走った時間を入力してください</li>
            <li>例）35分15秒の場合： 00時間 35分 15秒</li>
            <?php if($error['run_time_hour'] == 'blank' || $error['run_time_minute'] == 'blank' || $error['run_time_second'] == 'blank'): ?>
                <li class="error">時間が未入力でした</li>
            <?php endif; ?>
            <?php if($error['number'] == 'minute_wrong' || $error['number'] == 'second_wrong'): ?>
                <li class="error">適切な数値を入力してください</li>
            <?php endif; ?>
            <li class="form-parts"><span><input type="text" name="run_time_hour" value="<?php echo h($_POST['run_time_hour']); ?>">時間</span>
                <span><input type="text" name="run_time_minute" value="<?php echo h($_POST['run_time_minute']); ?>">分</span>
                <span><input type="text" name="run_time_second" value="<?php echo h($_POST['run_time_second']); ?>">秒</span></li>
            <li>
            <p><i class="far fa-comments"></i>メモ(100文字以下)</p>
            <textarea name="memo" rows="4" cols="40"><?php echo h($_POST['memo']); ?></textarea>
            </li>
            <?php if($error['memo'] == 'over'): ?>
                <li class="error">100文字以下で入力してください</li>
            <?php endif; ?>
        </ul>
        <div><input type="submit" value="変更する"></div>
    </form>
</div>
</body>
</html>