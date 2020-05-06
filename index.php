<!--投稿画面-->
<?php
//エラーログnoticeは非表示

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);

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

    


//バリデーションチェック
if(!empty($_POST)) {

    

    if(!is_numeric($_POST['month']) || !is_numeric($_POST['day']) || !is_numeric($_POST['hour']) 
    || !is_numeric($_POST['run_distance']) || !is_numeric($_POST['run_time_hour']) || !is_numeric($_POST['run_time_minute']) ||
     !is_numeric($_POST['run_time_second'])) {
        $error['digit'] = 'not-digit';
    }
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

    

    //走った時間を分に換算
    $total_run_time = ($_POST['run_time_hour'] * 60) + $_POST['run_time_minute'] + round(($_POST['run_time_second'] / 60),3);
    $velocityKmVer = round($total_run_time / $_POST['run_distance'], 2); // min / km

    //距離と時間からVDOTの算出
    //VDOT = 酸素摂取量(VO2) / %VO2max
    $velocity = ($_POST['run_distance'] * 1000) / $total_run_time; // m / min 
    $VO2 = -4.6 + 0.182258 * $velocity + 0.000104 * ($velocity*$velocity);
    $VO2max = (0.8 + 0.1894393 * pow(2.71828, (-0.012788 * $total_run_time)) + 0.2989558 * pow(2.71828, (-0.1932605 * $total_run_time))) * 100;
    $VDOT = round(($VO2 / $VO2max) * 100);

    if($VDOT > 85 || $VDOT < 30) {
        $VDOTerror['VDOT'] = 'VDOT_wrong';
    }

    //バリデーションおｋなら以下の処理
    if(empty($error)) {
    
        $insert = $db -> prepare('INSERT INTO run_data SET member_id=?, distance=?, total_run_time=?,
        run_time_hour=?, run_time_minute=?, run_time_second=?, velocity=?, VDOT=?, memo=?, month=?, day=?, hour=?, created=NOW()');
        $insert -> execute([$member['id'], $_POST['run_distance'], $total_run_time,
        $_POST['run_time_hour'],$_POST['run_time_minute'], $_POST['run_time_second'], $velocityKmVer, $VDOT, $_POST['memo'],
        $_POST['month'], $_POST['day'], $_POST['hour']]);
        

        header('Location: index.php');
        exit();
    }
}

//ランニングの全履歴を取得
$Allselect = $db -> prepare('SELECT * FROM run_data WHERE member_id=? ORDER BY created DESC');
$Allselect -> execute([$_SESSION['id']]);

//登録したばかりのランニング記録を取得、トップに表示、VDOTに基づいた各スコアも表示
$select = $db -> prepare('SELECT * FROM run_data, predicted_time, training_plan WHERE run_data.member_id=? AND run_data.VDOT=predicted_time.VDOT AND run_data.VDOT=training_plan.VDOT ORDER BY created DESC LIMIT 1');
$select -> execute([$_SESSION['id']]);

//月選択 

if(isset($_POST['select-month'])) {
    if($_POST['select-month'] == 'all' ) {
        $Allselect = $db -> prepare('SELECT * FROM run_data WHERE member_id=? ORDER BY created DESC');
        $Allselect -> execute([$_SESSION['id']]);
    } else {
        $Allselect = $db -> prepare('SELECT * FROM run_data WHERE member_id=? AND month=? ORDER BY created DESC');
        $Allselect -> execute([$_SESSION['id'], $_POST['select-month']]);
    }
}

/*
//VDOTがおかしい場合の最新ランニングデータ表示
if($VDOT > 85 || $VDOT < 30) {
    $select = $db -> prepare('SELECT * FROM run_data WHERE member_id=? ORDER BY created DESC LIMIT 1');
    $select -> execute([$_SESSION['id']]);
}*/


//htmlspecialcharsのショートカット関数定義
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES);
}

?>

<?php require('header.php'); ?>

<body>
<div class="wrap index">
    <header>
        <i class="fas fa-running"></i><h1>ランニング管理アプリ</h1><i class="fas fa-running"></i>
    </header>

    <?php echo $member['name'] . 'さんようこそ'; ?><br>
    <div><a href="logout.php" id="logout"><i class="fas fa-sign-out-alt"></i>ログアウト</a></div><br>
    <form action="" method="post">
        <ul>
            <?php date_default_timezone_set('Asia/Tokyo'); ?>
            <?php if($error['month'] == 'blank' || $error['day'] == 'blank' || $error['hour'] == 'blank'): ?>
                <?php if(!isset($_POST['select-month'])): ?>
                    <li class="error">時間が未入力でした</li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($error['digit'] == 'not-digit'): ?>
                <?php if(!isset($_POST['select-month'])): ?>
                    <li class="error">メモ以外には数字のみを入力してください</li>
                <?php endif; ?>
            <?php endif; ?>
            <li><i class="far fa-hand-point-right"></i>走った日付を入力してください</li>
            <?php if($error['number'] == 'month_wrong'): ?>
                    <li class="error">12以下の数値を入力してください</li>
            <?php endif; ?>
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
                <?php if(!isset($_POST['select-month'])): ?>
                    <li class="error">距離が未入力でした</li>
                <?php endif; ?>
            <?php endif; ?>
            <li class="form-parts"><input type="text" name="run_distance" value="<?php echo h($_POST['run_distance']); ?>">km</li>
            <li><i class="far fa-hand-point-right"></i>走った時間を入力してください</li>
            <li>例）35分15秒の場合： 00時間 35分 15秒</li>
            <?php if($error['run_time_hour'] == 'blank' || $error['run_time_minute'] == 'blank' || $error['run_time_second'] == 'blank'): ?>
                <?php if(!isset($_POST['select-month'])): ?>
                    <li class="error">時間が未入力でした</li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if($error['number'] == 'minute_wrong' || $error['number'] == 'second_wrong'): ?>
                <li class="error">適切な数値を入力してください</li>
            <?php endif; ?>
            <li class="form-parts"><span><input type="text" name="run_time_hour" value="<?php echo h($_POST['run_time_hour']); ?>">時間</span>
                <span><input type="text" name="run_time_minute" value="<?php echo h($_POST['run_time_minute']); ?>">分</span>
                <span><input type="text" name="run_time_second" value="<?php echo h($_POST['run_time_second']); ?>">秒</span></li>
            
            <li><i class="far fa-comments"></i>メモ(100文字以下)<br>
            <textarea name="memo" rows="4" cols="40"><?php echo h($_POST['memo']); ?></textarea>
            </li>
            <?php if($error['memo'] == 'over'): ?>
                <li class="error">100文字以下で入力してください</li>
            <?php endif; ?>
        </ul>
        <div><input type="submit" value="登録&計算"></div>
    </form>

    <main>
        <section class="table-container">
            <div class="table">
                <p class="sub-title">〇 最新のランニング結果</p>
                <?php foreach($select as $data): ?>
                <table>
                    <tr><td>日付</td><td><?php echo h($data['month']) . '月' . h($data['day']) . '日' . h($data['hour']) . '時ごろ'; ?></td></tr>
                    <tr><td>距離</td><td><?php echo h($data['distance']); ?></td></tr>
                    <tr><td>時間</td><td><?php if($data['run_time_hour'] == 0) {
                            echo '';
                        } else {
                            echo h($data['run_time_hour']) . 'h';
                        }
                        ?>
                        <?php echo h($data['run_time_minute']) . 'min ' . h($data['run_time_second']) . 'sec'; ?></td></tr>
                    <tr><td>スピード</td><td><?php if( false !== strpos( $data['velocity'], '.' ) ): 
                                $float = substr($data['velocity'], 1, 3);
                                echo ($data['velocity'] - $float) . 'min ' . ($float * 60) . 'sec';?>
                            <?php endif; ?>
                            <?php if( false == strpos( $data['velocity'], '.' ) ): 
                                echo h($data['velocity']) . 'min'; ?>
                            <?php endif; ?>/km</td></tr>
                    <?php if($VDOTerror['VDOT'] == 'VDOT_wrong'): ?>
                        <tr><td>VDOT</td><td>未計測</td></tr>
                    <?php else: ?>
                        <tr><td>VDOT</td><td><?php echo h($data['VDOT']); ?></td></tr>
                    <?php endif; ?>
                    <tr><td>メモ</td><td><?php echo h($data['memo']); ?></td></tr>
                </table>
            </div>
        
            <div class="table">
                <p class="sub-title">〇 VDOTに基づく各距離の予想フィニッシュタイム</p>
                <?php if($VDOTerror['VDOT'] == 'VDOT_wrong'): ?>
                    <p>算出されたVDOTが29以下または86以上のため、フィニッシュタイムを予測することができません</p>
                <?php else: ?>
                    
                    <table>
                        <tr><td>1500m</td><td><?php echo h($data['1500m']); ?></td></tr>
                        <tr><td>1マイル</td><td><?php echo h($data['1mile']); ?></td></tr>
                        <tr><td>3000m</td><td><?php echo h($data['3000m']); ?></td></tr>
                        <tr><td>2マイル</td><td><?php echo h($data['2mile']); ?></td></tr>
                        <tr><td>5000m</td><td><?php echo h($data['5000m']); ?></td></tr>
                        <tr><td>10000m</td><td><?php echo h($data['10000m']); ?></td></tr>
                        <tr><td>15km</td><td><?php echo h($data['15km']); ?></td></tr>
                        <tr><td>ハーフマラソン</td><td><?php echo h($data['half_marathon']); ?></td></tr>
                        <tr><td>フルマラソン</td><td><?php echo h($data['full_marathon']); ?></td></tr>
                    </table><br>
                <?php endif; ?>
            </div>

            <div class="table">
                <p class="sub-title">〇 VDOTに基づく適切なトレーニングメニュー</p>
                <?php if($VDOTerror['VDOT'] == 'VDOT_wrong'): ?>
                    <p>算出されたVDOTが29以下または86以上のため、適切なトレーニングメニューを提供することができません</p>
                <?php else: ?>
                    
                    <table>
                        <tr><td rowspan="2">Eぺース</td><td>1マイル</td><td><?php echo h($data['1mile_e']); ?></td></tr>
                        <tr><td>1km</td><td><?php echo h($data['1km_e']); ?></td></tr>
                        <tr><td rowspan="2">Mぺース</td><td>1マイル</td><td><?php echo h($data['1mile_m']); ?></td></tr>
                        <tr><td>1km</td><td><?php echo h($data['1km_m']); ?></td></tr>
                        <tr><td rowspan="3">Tぺース</td><td>400m</td><td><?php echo h($data['400m_t']); ?></td></tr>
                        <tr><td>1000m</td><td><?php echo h($data['1000m_t']); ?></td></tr>
                        <tr><td>1マイル</td><td><?php echo h($data['1mile_t']); ?></td></tr>
                        <tr><td rowspan="4">Iぺース</td><td>400m</td><td><?php echo h($data['400m_i']); ?></td></tr>
                        <tr><td>1000m</td><td><?php echo h($data['1000m_i']); ?></td></tr>
                        <tr><td>1200m</td><td><?php echo h($data['1200m_i']); ?></td></tr>
                        <tr><td>1マイル</td><td><?php echo h($data['1mile_i']); ?></td></tr>
                        <tr><td rowspan="5">Rぺース</td><td>200m</td><td><?php echo h($data['200m_r']); ?></td></tr>
                        <tr><td>300m</td><td><?php echo h($data['300m_r']); ?></td></tr>
                        <tr><td>400m</td><td><?php echo h($data['400m_r']); ?></td></tr>
                        <tr><td>600m</td><td><?php echo h($data['600m_r']); ?></td></tr>
                        <tr><td>800m</td><td><?php echo h($data['800m_r']); ?></td></tr>
                    </table>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="run-record-container">

            <p>月を選択</p>
            <form action="" method="post" id="select-month">
                <select name="select-month" id="select-month">
                    <option value="all">全表示</option>
                    <?php for($i=1; $i <= 12; $i++): ?>
                        <?php echo '<option value="' . $i . '">' . $i . '月</option>'; ?>
                    <?php endfor; ?>
                </select>
                <input type="submit" value="選択">
            </form>

            <p class="sub-title">〇 <?php
            if(isset($_POST['select-month'])) {
                if($_POST['select-month'] == 'all') {
                    echo '';
                } else {
                    echo $_POST['select-month'] . '月の';
                }
            } 
            ?>
            ランニング履歴</p>

            
            


            <div class="run-record">

                
                <?php foreach( $Allselect as $data_a): ?>
                    <div class="run-record-part">
                        <ul>
                            <li class="run-record-date"><?php echo h($data_a['month']) . '月' . h($data_a['day']) . '日' . h($data_a['hour']) . '時ごろ'; ?></li>
                            <li>距離：<?php echo h($data_a['distance']); ?>km</li>
                            <li>時間：
                            <?php if($data_a['run_time_hour'] == 0) {
                                echo '';
                            } else {
                                echo h($data_a['run_time_hour']) . 'h';
                            }
                            ?>
                            <?php echo h($data_a['run_time_minute']) . 'min ' . h($data_a['run_time_second']) . 'sec'; ?>
                            </li>
                            <li>
                                <?php if( false !== strpos( $data_a['velocity'], '.' ) ): 
                                    $float = substr($data_a['velocity'], 1, 3);
                                    echo 'スピード：' . ($data_a['velocity'] - $float) . 'min ' . ($float * 60) . 'sec';?>
                                <?php endif; ?>
                                <?php if( false == strpos( $data_a['velocity'], '.' ) ): 
                                    echo 'スピード：' . h($data_a['velocity']) . 'min'; ?>
                                <?php endif; ?>
                                /km
                            </li>
                            <?php if(mb_strlen($data_a['memo']) > 10): ?>
                                <li>
                                <?php echo 'メモ：' . substr(h($data_a['memo']), 0, 10) . '…'; ?>
                                </li>
                            <?php else: ?>
                                <li>
                                    <?php echo 'メモ：' . h($data_a['memo']); ?>
                                </li>
                            <?php endif; ?>
                            <li class="run-record-anchor-container">
                            <a class="run-record-anchor" href="detail.php?id=<?php echo h($data_a['id']); ?>">詳細</a>
                            <a class="run-record-anchor" href="modify.php?id=<?php echo h($data_a['id']); ?>">編集</a>
                            <a class="run-record-anchor" id="delete" href="delete.php?id=<?php echo h($data_a['id']); ?>">削除</a>
                            </li>

                        </ul>
                    </div>
                <?php endforeach; ?>
                <?php if($data_a == null){
                    echo '<p class="error no-record">履歴がありませんでした</p>';
                    exit();
                }
                ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>