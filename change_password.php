<?php session_start(); 
include("access.php");
mysql_connect ($host,$login,$haslo) or 
die ("Nie można popołączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 
die ("Nie można połączyć się z bazą danych");
if (($_SESSION['login']) && ($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];	
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m' || $_SESSION['user_status']=='u')) { $access=1; }  
	else { $access=0; }
}
else { 
	$access=0;
}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
//Po wprowadzeniu i zatwierdzeniu chęci zmiany hasła
if ($_POST['old'] && $_POST['new'] && $_POST['newcheck']) {
	if ($_SESSION['pass']==$_POST['old']) {
		if ($_POST['new']==$_POST['newcheck']) {
			if (strlen($_POST['new'])>4) {
				$querypass="UPDATE users SET pass='".$_POST['new']."' WHERE login='".$_SESSION['login']."';";
				mysql_query($querypass);
				$change=1;
				$_SESSION['pass']=$_POST['new'];
			}
			else { $krotkie=1; }
		}
		else { $nts=1; }
	}
	else { $bad_old=1; }
}
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Zmiana hasła</title>
<link rel="stylesheet" type="text/css" href="style.css">
<style type="text/css">
<!--
td {
	text-align:center;
	font-weight:bold;
}
-->
</style>
</head>
<body>
<center>
<form action="change_password.php" method=post>
<table border=1>
<tr><td>Stare hasło:</td><td><input type="password" name="old"></td></tr>
<tr><td>Nowe hasło:</td><td><input type="password" name="new"></td></tr>
<tr><td>Potwierdź nowe hasło:</td><td><input type="password" name="newcheck"></td></tr>
<tr><td colspan=2><font color=red>Nowe hasło nie moż być krótsze niż 5 znaków</font></td></tr>
<tr><td><a href=index.php><input type=button value=Powrót></a></td><td><input type="submit" value="Zmień hasło"></td></tr>
<?
//Segment informowania użytkownika na podstawie wcześniej ustalonych zmiennych
if ($change) { 
	print ("<tr><td colspan=\"2\"><font color=\"green\">Hasło zostało zmienione</font></td></tr>");
	$change=0; }	
if ($nts) { 
	print ("<tr><td colspan=\"2\"><font color=\"red\">Nowe hasło różni się w polu hasła i potwierdzenia</font></td></tr>");		
	$nts=0; }																													
if ($bad_old) { 																											
	print ("<tr><td colspan=\"2\"><font color=\"red\">Stare hasło jest niepoprawne</font></td></tr>");							
	$bad_old=0; }																											
if ($krotkie) { 																												
	print ("<tr><td colspan=\"2\"><font color=\"red\">Hasło za krótkie</font></td></tr>");
	$krotkie=0; }
?>
</table>

</form>
</center>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
