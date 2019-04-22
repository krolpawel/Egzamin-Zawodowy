<?php 
error_reporting(E_ALL);
session_start(); 
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
	if ($pass==$_SESSION['pass'] && $_SESSION['user_status']=='a') {
		$access=1; }	
	else { 	$access=0; }
} 
else { $access=0; }
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
//czy jest nowa wiadomość
$new_message_tab=mysql_fetch_assoc(mysql_query("select * from messages where login='".$_SESSION['login']."' and new=1"));
?>
<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Administracja Systemem</title>
<link rel="stylesheet" type="text/css" href="style.css">
<link rel="Shortcut icon" href="logo.gif" />
</head>
<body>
<?php

//Nagłówek
$queryimie="select imie from users where login='".$_SESSION['login']."';";
$tabimie=mysql_fetch_assoc(mysql_query($queryimie));			
$imie=$tabimie['imie'];
print("<br><div class=\"main-up\"><font class=\"witaj\">Witaj administratorze $imie</font>");
echo'<br>';
echo'<table name=kontener border=1 align=center>';	//główna tabela panelu admina
echo'<tr><td><b>Zarządzanie Testem</b></td><td colspan=2><b>Zarządzanie Użytkownikami</b></td><td><b>Zarządzanie kontem</b></td></tr>';
echo'<tr><td>';

//Zmiana czasu trwania testu prawidłowego
$time_t=mysql_fetch_assoc(mysql_query("select * from settings where id_ustawienia='1';")); //Pobranie czasu z bazy
$time=$time_t['value1'];
if (isset($_GET['time'])) { $time=$_GET['time']; }
echo"<form name=\"time\" method=\"get\">Zmień czas testu<br>prawidłowego (w minutach):<br>
		<input type=\"text\" name=\"time\" size=2 value=\"".$time."\">
		<input type=\"submit\" value=\"zatwierdź\"></form>";
if (isset($_GET['time'])) {  
	mysql_query("update settings SET value1='".$_GET['time']."' where id_ustawienia='1';");
	echo('<font color=green>czas został zmieniony na: '.$_GET['time'].'</font>');
}

//zmiana ilości pytań w teście prawidłowym
$nr_of_q_true_test_t=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=4"));
$nr_of_q_test=$nr_of_q_true_test_t['value1'];
if (isset($_GET['nr_of_q_test'])) { $nr_of_q_test=$_GET['nr_of_q_test']; }
echo"<form name=\"nr_of_q_test\" method=\"get\">Zmień liczbę pytań<br>w teście prawidłowym:<br>
		<input type=\"text\" name=\"nr_of_q_test\" size=2 value=\"".$nr_of_q_test."\">
		<input type=\"submit\" value=\"zatwierdź\"></form>";
if (isset($_GET['nr_of_q_test'])) {  
	mysql_query("update settings SET value1='".$_GET['nr_of_q_test']."' where id_ustawienia='4';");
	echo('<font color=green>Liczba pytań została<br>zmieniona na: '.$_GET['nr_of_q_test'].'</font>');
}	
echo'<hr width=100% size=1 color=black>';

//Zmiana czasu trwania testu treningowego
$timet_t=mysql_fetch_assoc(mysql_query("select * from settings where id_ustawienia='6';"));
$timet=$timet_t['value1'];
if (isset($_GET['timet'])) { $timet=$_GET['timet']; }
echo"<form name=\"timet\" method=\"get\">Zmień czas testu<br>treningowego (w minutach):<br>
		<input type=\"text\" name=\"timet\" size=2 value=\"".$timet."\">
		<input type=\"submit\" value=\"zatwierdź\"></form>";
if (isset($_GET['timet'])) {  
	mysql_query("update settings SET value1='".$_GET['timet']."' where id_ustawienia='6';");
	echo('<font color=green>czas został zmieniony na: '.$_GET['timet'].'</font>');
}

//zmiana ilości pytań w teście treningowym
$nr_of_q_trening_test_t=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=5"));
$nr_of_q_testt=$nr_of_q_trening_test_t['value1'];
if (isset($_GET['nr_of_q_testt'])) { $nr_of_q_testt=$_GET['nr_of_q_testt']; }
echo"<form name=\"nr_of_q_testt\" method=\"get\">Zmień liczbę pytań<br>w teście treningowym:<br>
		<input type=\"text\" name=\"nr_of_q_testt\" size=2 value=\"".$nr_of_q_testt."\">
		<input type=\"submit\" value=\"zatwierdź\"></form>";
if (isset($_GET['nr_of_q_testt'])) {  
	mysql_query("update settings SET value1='".$_GET['nr_of_q_testt']."' where id_ustawienia='5';");
	echo('<font color=green>Liczba pytań została<br>zmieniona na: '.$_GET['nr_of_q_testt'].'</font>');
}	
echo'<hr width=100% size=1 color=black>';

//exp time
$exp_time_t=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=7"));
$exp_time=$exp_time_t['value1'];
if (isset($_GET['exp_time'])) { $exp_time=$_GET['exp_time']; }
echo"<form name=\"exp_time\" method=\"get\">Ważność pozwolenia<br>na test (w minutach)<br>
		<input type=\"text\" name=\"exp_time\" size=2 value=\"".$exp_time."\">
		<input type=\"submit\" value=\"zatwierdź\"></form>";
if (isset($_GET['exp_time'])) {  
	mysql_query("update settings SET value1='".$_GET['exp_time']."' where id_ustawienia='7';");
	echo('<font color=green>Czas ważności pozwolenia<br>został zmieniony na: '.$_GET['exp_time'].'</font>');
}	
echo'</td><td colspan=2>';
//Zarządzanie użytkownikiem
echo'<form name=users_adm method=GET action=users_adm.php><table border=1 align=center height=100%>';

//wyświetlanie komunikatów dot operacji wykonywanych na użytkownikach
if(isset($_SESSION['skasowano'])) {
	if ($_SESSION['skasowano']==1) {
		echo'<tr><td colspan=2><font color=red>Skasowano wybranych użytkowników</font></td></tr>'; $_SESSION['skasowano']=0;
	}
}
elseif(isset($_SESSION['zezwolono'])) {
	if($_SESSION['zezwolono']==1) {
		echo'<tr><td colspan=2><font color=red>Zezwolono wybranym użytkownikom na wykonanie testu</font></td></tr>'; $_SESSION['zezwolono']=0;
	}
}
elseif(isset($_SESSION['zabroniono'])) {
	if($_SESSION['zabroniono']==1) {
		echo'<tr><td colspan=2><font color=red>Zabroniono wybranym użytkownikom wykonania testu</font></td></tr>'; $_SESSION['zabroniono']=0;
	}
}
elseif (isset($_GET['wprowadzono'])) {
	if($_GET['wprowadzono']=='ok') { echo'<tr><td colspan=2><font color=green><b>Wprowadzono poprawnie</b></font></td></tr>'; }
}
//wyświetlenie tabeli z opcjami dot użytkowników
echo'<tr><td><input type=submit name=opt_new_user value="Nowy użytkownik"></td><td rowspan=2>Zarządzaj klasą:<br>';
$klasy_all_q=mysql_query("select klasa from users;");
$i=1;
while($klasy_all_t=mysql_fetch_assoc($klasy_all_q)) {
	$klasa=$klasy_all_t['klasa'];
	if ($klasa!='nd') {
		$jest=false;
		if(isset($klasy_once)) {$jest=array_search($klasa, $klasy_once); }
		if ($jest==FALSE) {
			$klasy_once[$i]=$klasa;
			$i++;
		}
	}
}
asort($klasy_once);
foreach($klasy_once as $klucz => $wartosc) {	
	echo "<input type=submit name=opt_zk value=$wartosc><br>";
}
echo"<input type=submit name=opt_zk value=Niesklasyfikowani><br>";
echo"<input type=submit name=opt_zk value=Wszystkie>";
echo'</td></tr>';
echo'<tr><td>Liczba osób w klasie: <input type=text name=io size=1><br>Nazwa klasy(grupy):<input type=text name=class_name size=3><br><input type=submit name=opt_new_class value="Stwórz klasę"></td></tr>';
echo'</td></tr></table></form>';

echo'</td><td rowspan=2>';

//Przyciski zarządzania kontem
if($new_message_tab!='') {echo'<font color=red><b>Masz wiadomość!</b></font><br>'; }
echo"<a href=messages.php?inbox><input type=button value='Skrzynka Odbiorcza'></a><br>";
echo"<a href=change_password.php><input type=button value='Zmień hasło'></a><br>";
echo"<a href=index.php?logout=true><input type=button value=wyloguj></a><br>";
echo'</td></tr><tr><td>';

//Baza pytań, nowe pytanie, trening, test trning - przyciski
$kat_all_q=mysql_query("select * from kat;");
echo'<form name=kat_select method=get><select name=dzialykat onchange="javascript:window.location=\'baza_pytan.php?dzial=\' + this.options[this.selectedIndex].value;">';
echo"<option value='0' selected=selected>Wybierz dział pytań</option>";
while($rekord=mysql_fetch_assoc($kat_all_q)) {
	$katid=$rekord['kat_id'];
	$nazwa=$rekord['kat_name'];
	echo"<option value='$katid'>$nazwa</option>";
}
echo"<option name=all>Wszystkie</option></select></form>";		
echo'<a href=nowe_pytanie.php><input type=button value="Dodaj pytanie"></a><br>';
echo'<a href=trening.php><input type=button value=trening.php></a><br>';
echo'<a href=main.php><input type=button value=main.php></a><br>';
echo'</td>';
echo'<td>Dostęp osób niezalogowanych do testu:<br>';

//Zezwolenie na przeglądanie wykonywanie testu dla niezalogowanych.
if(isset($_GET['ann_acc'])) {
	if ($_GET['ann_acc']=='1') { mysql_query("update settings set value1=1 where id_ustawienia='2'"); $_GET['ann_acc']=0;}
	elseif ($_GET['ann_acc']=='2') { mysql_query("update settings set value1=2 where id_ustawienia='2'"); $_GET['ann_acc']=0; }		
}
$ann_access=mysql_fetch_assoc(mysql_query("select value1 from settings where id_ustawienia=2;"));
if ($ann_access['value1']==1) { echo"<img src=ann_deny.gif></td><td><a href=administration.php?ann_acc=2><input type=button value=Zezwól></a>";}
elseif($ann_access['value1']==2) { echo"<img src=ann_allow.gif></td><td><a href=administration.php?ann_acc=1><input type=button value=Zabroń></a>";}
else { echo'Błąd komunikacji z bazą'; }
echo'</td></tr>';
echo'</table>';
if($new_message_tab!='' && $_SESSION['message_alert']!=1) {
	echo'<script type=text/javascript>alert("Masz nową wiadomość!");</script>'; 
	$_SESSION['message_alert']=1;
}
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
