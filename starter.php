<? session_start(); 
include('access.php');
mysql_connect ($host, $login, $haslo) or 
die ("Nie można popołączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");




	$time_exp_t=mysql_fetch_assoc(mysql_query('select value1 from settings where id_ustawienia="7";')); //pobranie czasu ważności testu
	$time_exp=($time_exp_t['value1'])*60;
	echo('pobrany czas przełożony na sekundy: '.$time_exp.'<br>');
	$date_now=(mktime($h=date('H'), $i=date('i'), $s=date('s'), $m=date('m'), $d=date('d'), $y=date('Y'))*1);
	echo('obecnie: '.$date_now.', w przełożeniu na datę: '. $ndo=date('Y-m-d H:i:s', $date_now).'<br>');
	$date_exp=$date_now+$time_exp;
	echo('czas exp: '.$date_exp.', w przełożeniu na datę: '. $nde=date('Y-m-d H:i:s', $date_exp).'<br>');

	
	
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title></title>
<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
