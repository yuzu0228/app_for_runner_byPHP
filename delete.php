<?php
session_start();
require('dbconnect.php');

if(isset($_SESSION['id'])) {
    $id = $_REQUEST['id'];

    $select = $db -> prepare('SELECT * FROM run_data WHERE id=?');
    $select -> execute([$id]);
    $data = $select -> fetch();

    if($data['member_id'] == $_SESSION['id']) {
        $del = $db -> prepare('DELETE FROM run_data WHERE id=?');
        $del -> execute([$id]);
    }
}

header('Location: index.php');
exit();