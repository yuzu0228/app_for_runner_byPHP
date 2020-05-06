<!--個別投稿を表示-->
<?php
//エラーログnoticeは非表示
error_reporting(E_ALL & ~E_NOTICE);

session_start();

require('dbconnect.php');

if(empty($_REQUEST['id'])) {
    header('Location: index.php');
    exit();
}

//投稿を取得
$select = $db -> prepare('SELECT * FROM run_data, predicted_time, training_plan WHERE run_data.VDOT=predicted_time.VDOT AND run_data.VDOT=training_plan.VDOT AND run_data.id=? ORDER BY created DESC LIMIT 1');
$select -> execute([$_REQUEST['id']]);

//htmlspecialcharsのショートカット関数定義
function h($value) {
    return htmlspecialchars($value, ENT_QUOTES);
}
?>



<?php require('header.php'); ?>

<body>
<div class="wrap index detail">
    <header>
        <i class="fas fa-running"></i><h1>ランニング管理アプリ</h1><i class="fas fa-running"></i>
    </header>

    <main>
        <p><a href="index.php">メインメニューに戻る</a></p><br>

        <section class="table-container">
            <?php
            if($data = $select -> fetch()): ?>
                <div class="table">
                    <p class="sub-title">〇 ランニングの結果</p>
                    <table>
                    
                        <?php date_default_timezone_set('Asia/Tokyo'); ?>
                        <tr><td>日付</td><td><?php $timeStamp=time(); echo h(date('n', $timeStamp)) . '月' . h(date('j', $timeStamp)) . '日' . h(date('g', $timeStamp)) . '時ごろ'; ?></td>
                        </tr>
                        <tr><td>距離</td><td><?php echo h($data['distance']); ?></td></tr>
                        <tr><td>時間</td><td><?php if($data['run_time_hour'] == 0) {
                                echo '';
                            } else {
                                echo h($data['run_time_hour']) . 'h';
                            }
                            ?>
                            <?php echo h($data['run_time_minute']) . 'm' . h($data['run_time_second']) . 's'; ?></td></tr>
                        <tr><td>スピード</td><td><?php if( false !== strpos( $data['velocity'], '.' ) ): 
                                    $float = substr($data['velocity'], 1, 3);
                                    echo ($data['velocity'] - $float) . 'min' . ($float * 60) . 'sec';?>
                                <?php endif; ?>
                                <?php if( false == strpos( $data['velocity'], '.' ) ): 
                                    echo h($data['velocity']) . 'min'; ?>
                                <?php endif; ?>/km</td></tr>
                        <tr><td>VDOT</td><td><?php echo h($data['VDOT']); ?></td></tr>
                        <tr><td>メモ</td><td><?php echo h($data['memo']); ?></td></tr>
                    </table>
                </div>

                <div class="table">
                    <p class="sub-title">〇 VDOTに基づく各距離の予想フィニッシュタイム</p>
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
                    </table>
                </div>

                <div class="table">
                    <p class="sub-title">〇 VDOTに基づく適切なトレーニングメニュー</p>
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
                </div>
            <?php else: ?>
                <p>その記録は削除されたか、URLが正しくありません</p>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
