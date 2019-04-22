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
	if ($pass==$_SESSION['pass'] && $_SESSION['user_status']=='a') { $access=1; } 
	else { $access=0; }
}
else { $access=0;}
if (!$access) {
	$_SESSION['unauth']=1;
	header("Location: index.php"); 
}
$welcome_message='Witaj w systemie EGZAMIN ZAWODOWY.<br>
					Wszystkie zasady korzystania z systemu wysłumaczy Ci prowadzący zajęcia.<br>
					Powodzenia! ~ Administratorzy';
//przekierowanie do message.php
if($_GET['direction']=='Wyślij wiadomość') { 
	$i=1;
	$ciag='';
	$_GET['direction']=false;
		foreach($_GET as $klucz) {
		$ciag.=($i.'='.$klucz.'&');
		$i++;
	}
	$ciag.='new=from-adm';
	header('Location:messages.php?'.$ciag); 
}
if($_GET['direction']=='Wyświetl dane do druku') {
	echo"
		<table align='center' border='2' cellpadding='5'>
			<tr>
				<td>Imię</td>
				<td>Nazwisko</td>
				<td>Login</td>
				<td>Hasło</td>
			</tr>
	";
	foreach($_GET as $key) {
		if($key!='0' && $key!='Wyświetl dane do druku') {
			$dane_t=mysql_fetch_assoc(mysql_query("SELECT * FROM users WHERE login='$key'"));
			$imie=$dane_t['imie'];
			$nazwisko=$dane_t['nazwisko'];
			$login=$dane_t['login'];
			$haslo=$dane_t['pass'];
			echo("<tr>
						<td>$imie</td>
						<td>$nazwisko</td>
						<td>Login: $login</td>
						<td>Hasło: $haslo</td>
					</tr>
			");
		}
	}
	echo("<tr>
				<td colspan=2>
					<a href='javascript:history.back()'><input type='button' value='Powrót'></a>
				</td>
				<td colspan=2>
					<a href='javascript:self.print()'><input type=button value='Drukuj'></a>
				</td>
			</tr>
		</table>");
}
//	ilość userów
$ilosc_osob=mysql_num_rows(mysql_query('select login from users;'));

$_SESSION['opt_zk']=$_GET['opt_zk'];

//usunięcie (po potwierdzeniu)
if ($_GET['del_check']=='OK') {
	foreach($_GET as $login) {
			mysql_query("DELETE from users where login='$login';");	
			mysql_query("DELETE from trening_result where login='$login';");
			mysql_query("DELETE from result where login='$login';");
			mysql_query("DELETE from messages where login='$login';");
	}
	$_SESSION['skasowano']=1;
	header('Location:index.php');
	
}	

//formularz potwierdzenia usunięcia
elseif ($_GET['del']) {	
	echo'<form><table border=1 align=center><tr><td>login</td><td>Imię</td><td>Nazwisko</td><td>klasa</td></tr>';
	foreach($_GET as $login) {
			$os_tab_ic=mysql_fetch_assoc(mysql_query("select * from users where login='$login';"));
				$login_ic=$os_tab_ic['login'];
				$klasa_ic=$os_tab_ic['klasa'];
				$imie_ic=$os_tab_ic['imie'];
				$nazwisko_ic=$os_tab_ic['nazwisko'];
				echo"<tr><td>$login_ic</td><td>$imie_ic</td><td>$nazwisko_ic</td><td>$klasa_ic</td></tr>";
				echo"<input type=hidden name=$login value=$login checked=on>";
			
	}
	echo"<tr><td colspan=5><font color=red>Czy na pewno chcesz usunąć tych użytkowników?<br>
		<b>UWAGA! Zostaną też usunięte wyniki testów próbnych i właściwych tych użytkowników.</b></font></td></tr>";
	echo'<tr><td colspan=5><input type=submit name=del_check value=OK><a href=index.php><input type=button value=Wróć></a></td><tr>';
	echo'</table></form>';
}

//Zezwolenie na test
elseif ($_GET['test_allow']) {
	$time_exp_t=mysql_fetch_assoc(mysql_query('select value1 from settings where id_ustawienia="7";')); //pobranie czasu ważności testu
	$time_exp=($time_exp_t['value1'])*60; //przełożenie na sekundy
	$date_now=(mktime($h=date('H'), $i=date('i'), $s=date('s'), $m=date('m'), $d=date('d'), $y=date('Y'))*1); //format uniksowy
	$date_exp=$date_now+$time_exp;	//czas exp
	$nde=date('Y-m-d H:i:s', $date_exp);	//czas exp w normalnej formie
	foreach($_GET as $login) {
		mysql_query("update users set test_available='y', test_exp='$nde' where login='$login'");
	}
	$_SESSION['zezwolono']=1;
	header('Location: index.php');
}


//Zabronienie testu
elseif ($_GET['test_deny']) {
	foreach($_GET as $login) {
		mysql_query("update users set test_available='n', test_exp='0000-00-00 00:00:00' where login='$login'");
	}
	$_SESSION['zabroniono']=1;
	header('Location: index.php');
}
//Wprowadzenie pojedyńczego użytkownika
elseif ($_POST['new_user']) { 
	if ($_POST['login'] && $_POST['pass'] && $_POST['ch_pass'] && $_POST['imie'] && $_POST['nazwisko'] && $_POST['klasa']!='def') {	//sprawdza czy podano wszystkie dane
		$loginy_all=mysql_fetch_assoc(mysql_query("select login from users where login='".$_POST['login']."'"));	//pobiera tabelę userów
		$jest=$loginy_all['login'];
		if ($jest==FALSE) {
			if ($_POST['pass']==$_POST['ch_pass']) {	//sprawdza czy oba hasła zostały podane prawidłowo
				if($_POST['klasa']!='Wybierz klasę...') {
					$login=$_POST['login'];	//ustala zmienne lokalne
					$pass=$_POST['pass'];
					$uprawnienia=$_POST['uprawnienia'];
					$test=$_POST['test'];
					$imie=$_POST['imie'];
					$nazwisko=$_POST['nazwisko'];
					$klasa=$_POST['klasa'];
					if($test=='y') { 
						$time_exp_t=mysql_fetch_assoc(mysql_query('select value1 from settings where id_ustawienia="7";')); //pobranie czasu ważności testu
						$time_exp=($time_exp_t['value1'])*60; //przełożenie na sekundy
						$date_now=(mktime($h=date('H'), $i=date('i'), $s=date('s'), $m=date('m'), $d=date('d'), $y=date('Y'))*1); //format uniksowy
						$date_exp=$date_now+$time_exp;	//czas exp
						$exp_time=date('Y-m-d H:i:s', $date_exp);	//czas exp w normalnej formie		
					}						
					if($uprawnienia=='a') {
						$test='y';
						$exp_time='3000-12-31 00:00';
					}
					
					mysql_query("INSERT INTO users (login,pass,uprawnienia,test_available,imie,nazwisko,klasa,test_exp) VALUES ('$login','$pass','$uprawnienia','$test','$imie','$nazwisko','$klasa','$exp_time');");	//wprowadza
					mysql_query("INSERT INTO messages VALUES('$login','0','Administratorzy','Witaj!','$welcome_message','','1');");
					echo'wprowadzono';
					//header('Location: administration.php?wprowadzono=ok');
				}
				else { echo'Należy wybrać klasę!'; }
			}
			else { echo'Hasła nie zgadzają się'; }	//gdy porównanie haseł jest niepoprawne
		}
		else { echo'Login zajęty!'; }	//gdy login zajęty
	}
	else { echo'<font color=red><center>Uzupełnij formularz</center></font>'; }	//gdy niepełne dane
}
//Wprowadzenie klasy do bazy
elseif ($_POST['new_class']) {
	$ilosc=$_GET['io'];	//ilość osób
	$klasa=$_GET['class_name'];	//nazwa klasy
	$_GET=Array();	//zerowanie tablicy
	echo'<table align=center border=1 cellpadding=4 style="font-size:20px; text-decoration:bold;"><font style="font-size:20px"><tr><td colspan=3>Klasa '.$klasa.'</td></tr>';
	$loginy_all_t=mysql_query('select login from users');
	$haslo=false;
	for ($i=1;$i<=$ilosc;$i++) {
		if ($i<10) { $i='0'.$i; }
		$loginy_all=mysql_fetch_assoc($loginy_all_t);
		$login=$_POST["l$i"];	//przypisywanie loginów dla kolejnych osób
		$imie=$_POST["i$i"];
		$nazwisko=$_POST["n$i"];
		$uprawnienia=$_POST["u$i"];
		$jest=array_search($login,$loginy_all);	//sprawdzanie czy takich loginów już nie ma
		if ($jest==FALSE) {	//gdy jest unikalny
			$chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
			for ($j=0;$j<6;$j++) { 
			$haslo[$i].=substr($chars, rand(0, strlen($chars)-1), 1); 
			}	//losowanie hasła dla użytkownika
			echo"<tr><td>$imie $nazwisko</td><td>Login: $login</td><td>Hasło: $haslo[$i]</td></tr>";	//Wyświetlenie danych
			mysql_query("INSERT INTO users (login,pass,uprawnienia,test_available,imie,nazwisko,klasa) VALUES ('$login','$haslo[$i]','$uprawnienia','n','$imie','$nazwisko','$klasa');");	//Wprowadzenie do bazy
		}
		else { echo'<tr><td colspan=3><font color=red>Nie utworzono konta dla loginu '.$_POST["l$i"].'. Login zajęty</td></tr>'; }	//gdy coś nie w porządku dla danego loginu
	}
	echo'</font></table>';
	echo'<br><br>';
	echo('<center><a href="javascript:self.print()"><input type=button value=Drukuj></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=administration.php><input type=button value="Powrót"></a>');	//Drukowanie tabeli z hasłami
}
//Poprawienie wyedytowanych danych
elseif ($_POST['popraw']) {
	for($i=1;$i<=$_POST['ilosc'];$i++) {
			$l=$_POST["id$i"];
			$p=$_POST["p$i"];
			$u=$_POST["upr$i"];
			$t=$_POST["test$i"];
			$im=$_POST["i$i"];
			$n=$_POST["n$i"];
			$k=$_POST["klasa$i"];
			mysql_query("UPDATE users SET pass='$p', uprawnienia='$u', test_available='$t', imie='$im', nazwisko='$n', klasa='$k' where login='$l';");
		
	}
	header('Location:index.php');
}



?>

<html>
<head>
<meta http-equiv="content-type" content="html/php; charset=utf-8">
<meta name="author" content="Paweł Król">
<title>TZN Egzamin Zawodowy - Zarządzanie Użytkownikami</title>
<link rel="stylesheet" type="text/css" href="style.css">
<script type=text/javascript>
function zaznaczenie(i) {
	for(x=1;x<=i;x++) {
		element=document.getElementById(x).checked=true;
	}
}
function odznaczenie(i) {
	for(x=1;x<=i;x++) {
		element=document.getElementById(x).checked=false;
	}
}
</script>
</head>
<body>
<?php

//Widok edycji
if (isset($_GET['edit'])) {
	$klasy_all_q=mysql_query("select * from users;");
	$i=1;
	while($klasy_all_t=mysql_fetch_assoc($klasy_all_q)) {	//wyciągnięcie listy klas
		$klasa=$klasy_all_t['klasa'];
		if ($klasa!='nd') {		
			$jest=array_search($klasa, $klasy_once);
			if ($jest==FALSE) {
				$klasy_once[$i]=$klasa;
				$i++;
			}
		}
	}
	asort($klasy_once);	//sortowanie
	echo'<form name="edycja_usera" method=POST><table border=1 align=center>';
	echo'<tr><td>Login</td><td>Hasło</td><td>Imię</td><td>Nazwisko</td><td>Klasa</td><td>Uprawnienia</td><td>TEST</td></tr>';
	$id_usr=0;
	unset($_GET['test_nr']);
	foreach ($_GET as $login) {
		$osoba_t=mysql_fetch_assoc(mysql_query("select * from users where login='$login';"));
		$osoba_login=$osoba_t['login'];
		$osoba_haslo=$osoba_t['pass'];
		$osoba_imie=$osoba_t['imie'];
		$osoba_nazwisko=$osoba_t['nazwisko'];
		$osoba_klasa=$osoba_t['klasa'];
		$osoba_upr=$osoba_t['uprawnienia'];
		$osoba_test=$osoba_t['test_available'];
		if($login!='Edytuj') {
			++$id_usr;
			echo"<tr>
				<input type=hidden name=id$id_usr value=$osoba_login>
				<td><b>$osoba_login</b></td>
				<td><input type=text name=p$id_usr size=10 value=$osoba_haslo></td>
				<td><input type=text name=i$id_usr size=10 value=$osoba_imie></td>
				<td><input type=text name=n$id_usr size=10 value=$osoba_nazwisko></td>
				<td><select name=klasa$id_usr>";	//wyświetla dane osoby
						foreach($klasy_once as $klucz => $wartosc) {	//wyświetla listę klas z zaznaczoną tą do której jest przypisany aktualnie
						echo "<option name=klasa$id_usr value=$wartosc"; if ($osoba_klasa==$wartosc) { echo' selected'; } echo">$wartosc</option>";
						}
						echo "<option name=klasa;nd value=nd"; if ($osoba_klasa=='nd') { echo' selected'; } echo">nd</option>";
			echo"</select></td>
				<td><select name=upr$id_usr>
						<option name=u value=u"; if ($osoba_upr=='u') { echo' selected'; } echo">Użytkownik</option>
						<option name=m value=m"; if ($osoba_upr=='m') { echo' selected'; } echo">Moderator</option>
						<option name=a value=a"; if ($osoba_upr=='a') { echo' selected'; } echo">Administrator</option>
					</select>
				</td>
				<td><select name=test$id_usr>
						<option name=t value=t"; if ($osoba_test=='y') { echo' selected'; } echo">TAK</option>
						<option name=n value=n"; if ($osoba_test=='n') { echo' selected'; } echo">NIE</option>
					</select></td>
				</tr>
			"; //to samo co wyżej tylko z uprawnieniami i dostępem do testu
			
		}
	} 	
	echo"<input type=hidden name=ilosc value=$id_usr>";
	echo'</table><input type=submit name=popraw value="Popraw dane"><br>
			<a href="index.php"><input type=button value=Wróć></a></form>';
}

//Formularz wprowadzenia pojedyńczego użytkownika
elseif ($_GET['opt_new_user']) {
	echo'
	<form name=new_user method=POST><table align=center border=1>
	<tr><td colspan=2>Wprowadź dane:</td></tr>
	<tr><td>Login</td><td><input type=text name=login></td></tr>
	<tr><td>Hasło</td><td><input type=password name=pass></td></tr>
	<tr><td>Potwierdź hasło</td><td><input type=password name=ch_pass></td></tr>
	<tr><td>Imię</td><td><input type=text name=imie></td></tr>
	<tr><td>Nazwisko</td><td><input type=text name=nazwisko></td></tr>
	<tr><td>Klasa</td><td><select name=klasa>
			<option name=def selected=selected>Wybierz klasę...</option>';

	$klasy_all_q=mysql_query("select * from users;");
	$i=1;
	while($klasy_all_t=mysql_fetch_assoc($klasy_all_q)) {
		$klasa=$klasy_all_t['klasa'];
		$jest=array_search($klasa, $klasy_once);
		if ($jest==FALSE) {
			$klasy_once[$i]=$klasa;
			$i++;
		}
	}
	asort($klasy_once);
	foreach($klasy_once as $klucz => $wartosc) {
	echo"<option value=$wartosc>$wartosc</option>";
	}
	echo'	</select></td></tr>
	<tr><td>Uprawnienia</td><td><select name=uprawnienia>
			<option value=u selected=selected>Użytkownik</option>
			<option value=m>Moderator</option>
			<option value=a>Administrator</option>
		</select></td></tr>
	<tr><td>TEST:</td><td><select name=test>
			<option value=y selected=selected>TAK</option>
			<option value=n>NIE</option>
		</select></td></tr>
	<tr><td colspan=2><input type=submit name=new_user value=Wprowadź></td></tr>
	<tr><td colspan=2><a href=index.php><input type=button value=Powrót></a></td></tr>
	</table></form>';
}

//formularz wprowadzania klasy
elseif ($_GET['opt_new_class']) {
	if ($_GET['io'] && $_GET['class_name']) {	//jeśli wypełniono nazwę oraz ilość osób
		$ilosc=$_GET['io'];
		$nazwa_klasy=$_GET['class_name'];
		echo' <form name=new_class method=POST><table align=center border=1>
		<tr><td colspan=4>Nazwa klasy(grupy):<br><b>'.$nazwa_klasy.'</b></td></tr>
		<tr><td>Login</td><td>Imię</td><td>Nazwisko</td><td>Uprawnienia</td></tr>';
		for ($i=1;$i<=$ilosc;$i++) {
			if ($i<10) { $i='0'.$i; }
				echo"<tr>
				<td><input type=text name=l$i value=$nazwa_klasy-$i size=5></td>
				<td><input type=text name=i$i></td>
				<td><input type=text name=n$i></td>
				<td>
				<select name=u$i>
					<option value=u selected=selected>Użytkownik</option>
					<option value=m>Moderator</option>
					<option value=a>Administrator</option>
				</select>
				</td></tr>";
		}
		echo'<tr><td colspan=2><a href=index.php><input type=button value=Powrót></a></td>
			<td colspan=2><input type=submit name=new_class value=Wprowadź></td></tr>';
		echo'</table></form>';
	}
	else { echo"nie wypełniłeś potrzebnych danych"; }
}

//formularz zarządzania klasą
elseif ($_GET['opt_zk']) {
	$klasa=$_GET['opt_zk'];
	echo'<form name=users_editor method=GET><table align=center border=1>';
	echo"<tr><td colspan=7>Klasa $klasa</td></tr>";
	if ($_SESSION['zezwolono']) { echo'<tr><td colspan=7><font color=green><center>Zezwolono wybranym użytkownikom</center></font></td></tr>'; $_SESSION['zezwolono']=0; }
	if ($_SESSION['zabroniono']) { echo'<tr><td colspan=7><font color=red><center>Zabroniono wybranym użytkownikom</center></font></td></tr>'; $_SESSION['zabroniono']=0; }
	if ($klasa=='Wszystkie') { 
		echo'<tr><td>login</td><td>Imię</td><td>Nazwisko</td><td>Uprawnienia</td><td>TEST</td><td>Klasa</td></tr>';
		$klasa_all_info_q=mysql_query("select * from users order by login;");
	}
	elseif($klasa=='Niesklasyfikowani') {
		echo'<tr><td>login</td><td>Imię</td><td>Nazwisko</td><td>Uprawnienia</td><td>TEST</td></tr>';
		$klasa_all_info_q=mysql_query("select * from users where klasa='nd' order by login;");
	}
	else { 
		$ostatni_test_tab=mysql_fetch_assoc(mysql_query("select test_nr from result where login like '$klasa%' order by test_nr desc limit 1"));
		$ostatni_test=$ostatni_test_tab['test_nr'];
		echo"<tr><td colspan=7>Wyniki klasy w teście prawidłowym numer
				<input type=hidden value=$klasa>";
				echo'<select name=test_nr onchange="javascript:window.location=\'users_adm.php?klasa='.$klasa.'&test_nr=\' + this.options[this.selectedIndex].value;">';
				echo"<option value=0>Wybierz numer testu</option>";
		for($i=1;$i<=$ostatni_test;$i++) {
			echo"<option value=$i>$i</option>";
		}
		echo"</select></td></tr>";
		$klasa_all_info_q=mysql_query("select * from users where klasa='$klasa' order by login;");
		echo'<tr><td>login</td><td>Imię</td><td>Nazwisko</td><td>Uprawnienia</td><td>TEST</td></tr>';
	}
	$i=0;
	while ($klasa_r=mysql_fetch_assoc($klasa_all_info_q)) {	//wyświetlenie wszystkich pobranych osób
		$login=$klasa_r['login'];
		$uprawnienia=$klasa_r['uprawnienia'];
		$test=$klasa_r['test_available'];
		$imie=$klasa_r['imie'];
		$nazwisko=$klasa_r['nazwisko'];
		$klasa_usera=$klasa_r['klasa'];
		++$i; 
		echo"<tr>
				<td>$login</td>
				<td>$imie</td>
				<td>$nazwisko</td>
				<td>$uprawnienia</td>
				<td>$test</td>";
				if ($klasa=='Wszystkie') { echo"<td>$klasa_usera</td>"; }
		   echo"<td><input type=checkbox name=$i id=$i value=$login></td>
				<td><a href=wyniki.php?user=$login&imie=$imie&nazwisko=$nazwisko><input type=button value=wyniki></a></td>
		</tr>"; 
	}
	echo"<tr>
			<td></td>
			<td colspan=4>
				<input type=button value='Zaznacz wszystkie' onclick='zaznaczenie($i)'>
				<input type=button value='Odznacz wszystkie' onclick='odznaczenie($i)'>
			</td>
			<td colspan=2></td>
		</tr>";
	echo'<tr>
			<td colspan=2><input type=submit name=edit value=Edytuj></td>
			<td colspan=3><input type=submit name=test_allow value="Zezwól na test">/
			<input type=submit name=test_deny value="Zabroń testu"></td>
			<td colspan=2><input type=submit name=del value=Usuń></td>
		</tr>';
	echo'<tr><td colspan=4><input type=submit name=direction value="Wyślij wiadomość"></td>';
	echo'<td colspan=4><input type=submit name=direction value="Wyświetl dane do druku"></td></tr>';
	echo'<tr><td colspan=7><a href=index.php><input type=button value=Powrót></a></td></tr>';
	echo'</table></form>';
}

//wyświetlanie wyników ostatniego testu prawidłowego całej klasy 
elseif (isset($_GET['klasa'])&&isset($_GET['test_nr'])) {
	$klasa=$_GET['klasa'];
	$test_nr=$_GET['test_nr'];
	$wyniki_klasy_tab=mysql_query("select * from result where login like '$klasa%' and test_nr='$test_nr'");
	echo"<table align=center cellpadding=5 border=1><tr><td colspan=4>Klasa $klasa, test nr $test_nr</td></tr>";
	echo'<tr><td>login</td><td>Nazwisko</td><td>Imię</td><td>wynik</td></tr>';
	while ($rekord1=mysql_fetch_assoc($wyniki_klasy_tab)) {
		$login=$rekord1['login'];
		$dane_usera=mysql_fetch_assoc(mysql_query("select imie, nazwisko from users where login='$login'"));
		echo('<tr><td>'.$rekord1['login'].'</td><td>'.$dane_usera['nazwisko'].'</td><td>'.$dane_usera['imie'].'</td><td>'.$rekord1['result'].'%</td></tr>');
	}
	echo"<tr><td colspan=4><a href=users_adm.php?opt_zk=$klasa><input type=button value='Powrót'></a></td></tr>";
	echo'</table>';
}
?>
<div class=stopka>
<?php include("stopka.html"); ?>
</div>
</body>
</html>
