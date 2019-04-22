<?php 
session_start(); 
error_reporting(E_ALL);
include("access.php");
mysql_connect ($host,$login,$haslo) or
die ("Nie można połączyć się z MySQL");
mysql_query("SET NAMES 'utf8'");
mysql_select_db ($select) or 					
die ("Nie można połączyć bazą danych");
if (isset($_SESSION['login']) && isset($_SESSION['pass'])) {
	$query="select * from users where login='".$_SESSION['login']."';";
	$tab=mysql_fetch_assoc(mysql_query($query));
	$pass=$tab['pass'];
	if ($pass==$_SESSION['pass'] && ($_SESSION['user_status']=='a' || $_SESSION['user_status']=='m' || $_SESSION['user_status']=='u')) { $access=1; }
	else { $access=0; }
}
else { $access=0;}
if (!$access) {$_SESSION['unauth']=1; header("Location: index.php"); }	

?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Twój panel</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="Shortcut icon" href="logo.gif" />
</head>
<body>
<div>
<?php

$queryimie="select * from users where login='".$_SESSION['login']."';";
$tabimie=mysql_fetch_assoc(mysql_query($queryimie));			
$imie=$tabimie['imie'];
$_SESSION['imie']=$imie;
$_SESSION['nazwisko']=$tabimie['nazwisko'];
echo"<br><br><br><br><br>";
print("<div class=\"main-up\"><font class=\"witaj\">Witaj $imie</font>");
?>

<table border=0 width=1000 align="center">

<!-- panel użytkownika -->
<tr><td width=14%>Panel użytkownika<br>
<?php
//czy jest nowa wiadomość
$new_message_tab=mysql_fetch_assoc(mysql_query("select * from messages where login='".$_SESSION['login']."' and new=1"));

//przyciski
print ("<a href=\"wyniki.php\"><input type=\"button\" value=\"WYNIKI\"></a><br>");
if($new_message_tab!='') {echo'<font color=red><b>Masz wiadomość!</b></font><br>'; }
echo("<a href='messages.php?inbox'><input type=button value='Skrzynka odbiorcza'></a><br>");
echo("<a href=messages.php?new=from-usr><input type=button value='Kontakt'></a><br>");
print ("<a href=\"change_password.php\"><input type=\"button\" value=\"Zmień hasło\"></a><br>");		//przeniesienie do zmiany hasła
print "<a href=index.php?logout=true><input type=button value=\"Wyloguj\"></a>";
?>
</td>
<!-- wynik ostatniego testu -->
<td width=67% align="center">
<?php
$wot_q=mysql_query("select result from result where login='".$_SESSION['login']."' order by test_nr DESC limit 1;");
while ($wot_t=mysql_fetch_assoc($wot_q)) {
$wot=$wot_t['result']; }
if(isset($wot)) {
	if ($wot==true) {
		echo ("<b><font size=\"4\">Twój wynik z ostatniego testu to: $wot%</font></b>");
	}
}
?>
</td>
<!-- przycisk wykonania testu -->
<td width=17% align="center">
<?php
$querytest="select * from users where login='".$_SESSION['login']."';";	
$tabtest=mysql_fetch_assoc(mysql_query($querytest));										
$test=$tabtest['test_available'];
if (!$test) { print ("Błąd połączenia z bazą danych!"); }
if ($test=='y') { print ("<a href=\"test.php\"><img src=\"green_button.png\"></a>"); }
elseif ($test=='n') { print ("<img src=\"red_button.gif\">"); }
else { print ("Nieokreślony błąd skryptu"); }
?>
</td></tr>
<tr><td valign="top">
<?php
//Panel moderatora
if ($_SESSION['user_status']=='m') {
	echo('Panel Moderatora<br>');
	$kat_all_q=mysql_query("select * from kat;");
	echo'<form name=kat_select method=get><select name=dzialykat onchange="javascript:window.location=\'baza_pytan.php?dzial=\' + this.options[this.selectedIndex].value;">';
	echo"<option value='0' selected=selected>Wybierz dział pytań</option>";
	while($rekord=mysql_fetch_assoc($kat_all_q)) {
		$katid=$rekord['kat_id'];
		$nazwa=$rekord['kat_name'];
		echo"<option value='$katid'>$nazwa</option>";
	}
	echo"<option name=all>Wszystkie</option></select></form>";
	echo('<a href=nowe_pytanie.php><input type=button value="Dodaj pytanie"></a><br>');					//Dodawanie pytań
	}
if ($_SESSION['user_status']=='a') {
echo'<a href=administration.php><input type=button value=administration.php></a>';
}
?>
</td><td>
<!-- trening -->
<table border=0 align="center">
<tr><td colspan=2><font color="red" size="7">TRENING</font><br><br></td></tr>

<tr>
<td><table>
<?php
$kategorie_q=mysql_query("SELECT * from kat;");
$i=1;
$b=0;
while($rekord=mysql_fetch_assoc($kategorie_q)) {
	if(isset($rekord['pict'])) { $pict=$rekord['pict']; }
	else { $pict='ann.gif'; }
	$kat_name=$rekord['kat_name'];
	$kat_name=str_replace('_',' ',$kat_name);
	$kat_name=ucwords($kat_name);
	if($i) { $i=0; echo'</tr><tr>'; } else { $i=1; }
	++$b;
	echo"<td><a href=trening_test.php?o=$b><img src=kat_img/$pict height=150><br>$kat_name</a></td>";
}
if(isset($new_message_tab) && isset($_SESSION['message_alert'])) {
	if($new_message_tab!='' && $_SESSION['message_alert']!=1) {
		echo'<script type=text/javascript>alert("Masz nową wiadomość!");</script>'; 
		$_SESSION['message_alert']=1;
	}
}
?>
</table></td>
<td></td>
</tr>
</table>
</div> 
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
