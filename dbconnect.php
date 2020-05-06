<?php
try{
        $db = new PDO('mysql:dbname=heroku_f0d1fa31638915a;host=us-cdbr-iron-east-01.cleardb.net;charset=utf8', 'bd5c6345b8bf97', 'a7eb2418');
    } catch (PDOexception $e) {
        echo '接続エラー' . $e->getMassage();
    }
?>