<?php 		
session_start();
error_reporting(E_ALL);
include("access.php");
mysql_connect ($host, $login, $haslo) or
die ("Nie można połączyć się z MySQL");
mysql_select_db ($select) or
die ("Nie można połączyć się z bazą");
if (isset($_SESSION['fail'])) {
	if ($_SESSION['fail']>=3) { $_SESSION['fail']=0; $_SESSION['report']=0; }
}
if (isset($_SESSION['unauth'])) {
	if($_SESSION['unauth']!=0) {
		if(!isset($_SESSION['fail'])) { $_SESSION['fail']=1; }
		$_SESSION['fail']++;
		$_SESSION['unauth']=0;
		if (isset($_SESSION['fail'])) {
			if ($_SESSION['fail']>=3) { $_SESSION['report']=1; $_SESSION['fail']=0;}
		}
	}
}
// jeśli zostało wysłane żądanie zalogowania
//if ((isset($_POST['login']) && isset($_POST['pass']))||(isset($_SESSION['login']) && isset($_SESSION['pass']))) {				
if ((isset($_POST['login']) && isset($_POST['pass']))) {				
	$query="select * from users where login='".$_POST['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));	
	$pass=$tab['pass'];
	$upr=$tab['uprawnienia'];
	$act=$tab['activation'];
	if ($pass==$_POST['pass']) {
		$_SESSION['fail']=0;			
		$_SESSION['report']=0;		
		$_SESSION['login']=$_POST['login'];
		$_SESSION['pass']=$_POST['pass'];
		$_SESSION['user_status']=$upr;	
		$_SESSION['activation']=$act;
	} 
	else { $blad=1; }	//niepoprawny login/hasło
}
//Wylogowanie
if (isset($_GET['logout'])) {
	$_SESSION=array();		
	$_POST=array();
	$_GET['logout']='';
	$_GET=array();
	$logout=1;	
	session_destroy();
}	
//sprawdzanie uprawnień i przekierowanie na main page lub do aktywacji (jeśli zalogowany)
if(isset($_SESSION['user_status'])) {
	$login=$_SESSION['login'];
	$act_chk_t=mysql_fetch_assoc(mysql_query("select activation from users where login='$login'"));
	$act=$act_chk_t['activation'];
	if($act=='ok') { $_SESSION['activation']='ok'; }
	$act=false;
	if($_SESSION['activation']=='ok') {
		if ($_SESSION['user_status']=='a') { header("Location: administration.php");}
		elseif ($_SESSION['user_status']=='u' || $_SESSION['user_status']=='m') { header("Location: main.php"); }
	}
	else { 
		$login=$_SESSION['login'];
		header("Location: activation.php?from=first_log&l=$login");
	}
}
//sprawdzenie czy osoba niezalogowana może kożystać z zasobów
$ann_acc=mysql_fetch_assoc(mysql_query("Select value1 from settings where id_ustawienia=2;"));
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="Shortcut icon" href="logo.gif" />
</head>
<body>
<center>
<div class='all'>
<div class='content'>
<form name="logowanie" action="index.php" method=POST>			<!-- STRONA -->
<table width="500" border=0>
<tr><td align="center" width="33%"><font class="textlogowanie">Egzamin Zawodowy</font></td>
<td align="center" width="34%"><a href="http://www.tzn.dabrowa.pl" target="_blank"><img src="logo.gif" width="175" height="219" border=0></a></td>  <!-- logo to link!! -->
<td align="center" width="33%"><font class="textlogowanie">Technik informatyk</font></td></tr>
<tr><td align="center" colspan="3"><font class="textlogowanie">Techniczne Zakłady Naukowe</font></td></tr></table>
<br><br><br>
<table align="center" border=0>
<tr>
<?php
if($ann_acc['value1']==2) { echo'<td><a href="trening.php"><img src="trening.gif" alt="trening" border=0></a></td>';}
?>
<td align="center" valign="middle">
<table border=0>
<tr><td colspan="2" align="center">Zaloguj się:</td></tr>
<tr><td>Login:</td><td><input type="text" name="login"></td></tr>
<tr><td>Hasło:</td><td><input type="password" name="pass"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" value="Zaloguj"></td></tr>
<?php
//Komórka w tabeli logowania odpowwiedzialna za komunikaty
if (isset($blad)) {
print "<tr><td colspan=\"2\" align=\"center\"><CENTER><font color=\"red\">Błąd logowania</font></CENTER></td></tr>"; }
if (isset($logout)) {
print "<tr><td colspan=\"2\" align=\"center\"><CENTER><font color=\"red\">Zostałeś wylogowany</font></CENTER></td></tr>"; }
if (isset($_SESSION['fail'])) { //Nieautoryzowane działanie
	if($_SESSION['fail']!=0){
		print "<tr><td colspan=\"2\" align=\"center\"><CENTER><font color=\"red\">Nieautoryzowane działanie!!!</font></CENTER></td></tr>"; 
	}
}
if (isset($_SESSION['report'])) {
	if ($_SESSION['report']==1) { //działanie po przekroczeniu dozwolonej ilości braku autoryzacji
		print "<tr><td colspan=\"2\" align=\"center\"><CENTER><font color=\"red\"><b>ADMINISTRATOR SYSTEMU<br>ZOSTANIE POINFORMOWANY<br>O PRÓBIE WŁAMANIA!!!</b></font></CENTER></td></tr>"; 
		$date=date("d-m-y");
		$czas=date("G:i:s");
		$ip=$_SERVER['REMOTE_ADDR'];	// pobieranie ip użytkownika
		mail("pawelk92@gmail.com","[PRACOWNIA] Próba włamania do systemu \"Egzamin Zawodowy\"","W dniu ".$date." o godzinie ".$czas." czasu serwera nastąpiło przekroczenie dozwolonej ilości nieautoryzowanych działań z komputera o adresie ".$ip.". \r\n Serwer wnioskuje próbę włamania."); //wysłanie wiadomości do administratora
	}
}
?>
</table>
</td>
</tr>
</table>
</form>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</div>
</div>
</center>
</body>
</html>
