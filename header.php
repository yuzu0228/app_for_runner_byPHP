<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ランニング記録アプリ</title>

	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css">
	<script src="js/jquery-3.4.1.min.js"></script>
	<script src="js/jquery.js"></script>
	
	<script>
		$(function(){
			$('.index #logout').on('click', function(){
				if(confirm('ログアウトしますか？')) {
					location.href = 'logout.php';
				}else {
					return false;
				}
			});

			$('#delete').on('click', function(){
				if(confirm('本当に削除しますか？')) {
					location.href = 'index.php';
				}else {
					return false;
				}
			});
			
		})
	</script>
	
</head>